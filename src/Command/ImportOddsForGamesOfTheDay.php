<?php

namespace App\Command;

use App\Entity\DataClient;
use App\Entity\Game;
use App\Entity\OddsClient;
use App\Manager\GameManager;
use App\Repository\ChampionshipRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportOddsForGamesOfTheDay extends Command
{
    private $client;
    private $gameManager;
    private $dataClient;
    private $championshipRepository;
    private $gameRepository;
    private $entityManager;

    public function __construct(GameManager $gameManager, OddsClient $client, DataClient $dataClient, ChampionshipRepository $championshipRepository, GameRepository $gameRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct('api:import:odds');
        $this->setDescription('Import all odds for games of the day');

        $this->client = $client;
        $this->gameManager = $gameManager;
        $this->dataClient = $dataClient;
        $this->championshipRepository = $championshipRepository;
        $this->gameRepository = $gameRepository;
        $this->entityManager = $entityManager;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $odds = $this->client->get(sprintf('1n2/offre/%s', (new \DateTime('now'))->format('Ymd')), [
            'query' => [
                'sport' => '100',
            ]
        ]);

        $clientOdds = [];
        foreach ($odds as $data) {
            foreach ((array) $data['formules'] as $formule) {
                if ($formule['marketType'] === "Plus/Moins 2,5 buts (Temps Réglementaire)") {
                    $clientOdds[] = array_merge($formule, ['winnerOdds' => $data['outcomes']]);
                }
            }
        }

        $games = $this->gameManager->setOddsForGamesOfTheDay($clientOdds);

        $championships = $this->championshipRepository->championshipsWithGamesWithoutOdds();

        foreach ($championships as $championship) {
            $odds = $this->dataClient->get('odds', [
                    'query' => [
                        'league' => $championship['api_id'],
                        'season' => 2020,
                        'bookmaker' => 6,
                        'date' => (new \DateTime())->format('Y-m-d'),
                    ]
                ]
            );

            foreach ($odds['response'] as $data) {
                /** @var Game|null $game */
                $gameToUpdate = $this->gameRepository->findOneBy(['apiId' => $data['fixture']['id']]);

                if (null === $gameToUpdate) {
                    continue;
                }

                foreach ($data['bookmakers'] as $bookmakerOdd) {
                    foreach ($bookmakerOdd['bets'] as $bet) {
                        if ('Match Winner' === $bet['name']) {
                            switch (true) {
                                case $gameToUpdate->getPrevisionalWinner() === $gameToUpdate->getHomeTeam():
                                    $odd = $this->getOdd($bet['values'], 'Home');
                                    break;
                                case $gameToUpdate->getPrevisionalWinner() === $gameToUpdate->getAwayTeam();
                                    $odd = $this->getOdd($bet['values'], 'Away');
                                    break;
                                case null === $gameToUpdate->getPrevisionalWinner();
                                    $odd = $this->getOdd($bet['values'], 'Draw');
                            }

                            $gameToUpdate->setWinnerOdd($odd);
                        }

                        if ('Goals Over/Under' === $bet['name']) {
                            $odd = null;
                            if ($gameToUpdate->getAverageExpectedNbGoals() > Game::LIMIT) {
                                $odd = $this->getOdd($bet['values'], 'Over 2.5');
                            } elseif ($gameToUpdate->getAverageExpectedNbGoals() <= Game::LIMIT) {
                                $odd = $this->getOdd($bet['values'], 'Under 2.5');
                            }

                            $gameToUpdate->setOdd($odd);
                        }
                    }
                }

                $this->entityManager->persist($gameToUpdate);
                $this->entityManager->flush();
                $games[] = $gameToUpdate;
            }
        }

        foreach ($games as $game) {
            $output->writeln(
                sprintf('%s - %s updated', $game->getHomeTeam()->getName(), $game->getAwayTeam()->getName())
            );
        }
    }

    private function getOdd($odds, string $key): ?float
    {
        foreach ($odds as $odd) {
            if ($key === $odd['value']) {
                return $odd['odd'];
            }
        }

        return null;
    }
}