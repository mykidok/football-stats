<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\Client;
use App\Entity\Game;
use App\Entity\OddsClient;
use App\Entity\UnderOverBet;
use App\Entity\WinnerBet;
use App\Manager\GameManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportOddsForGamesOfTheDay extends Command
{
    private $client;
    private $gameManager;
    private $dataClient;
    private $entityManager;

    public function __construct(GameManager $gameManager, OddsClient $client, Client $dataClient, EntityManagerInterface $entityManager)
    {
        parent::__construct('api:import:odds');
        $this->setDescription('Import all odds for games of the day');

        $this->client = $client;
        $this->gameManager = $gameManager;
        $this->dataClient = $dataClient;
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
            $clientOdds[$data['label']]['winner'] = $data['outcomes'];
            foreach ((array) $data['formules'] as $formule) {
                if ($formule['marketType'] === "Plus/Moins 2,5 buts (Temps Réglementaire)") {
                    $clientOdds[$data['label']]['underOverTwo'] = $formule['outcomes'];
                }
                if ($formule['marketType'] === "Plus/Moins 3,5 buts (Temps Réglementaire)") {
                    $clientOdds[$data['label']]['underOverThree'] = $formule['outcomes'];
                }
                if ($formule['marketType'] === "Double chance (Temps Réglementaire)") {
                    $clientOdds[$data['label']]['doubleChance'] = $formule['outcomes'];
                }
            }
        }

        $games = $this->gameManager->setOddsForGamesOfTheDay($clientOdds);

        $championshipRepository = $this->entityManager->getRepository(Championship::class);
        foreach ($championshipRepository->championshipsWithGamesWithoutOdds() as $championship) {
            $odds = $this->dataClient->get('odds', [
                    'query' => [
                        'league' => $championship['api_id'],
                        'season' => 2020,
                        'bookmaker' => 6,
                        'date' => (new \DateTime())->format('Y-m-d'),
                    ]
                ]
            );

            $gameRepository = $this->entityManager->getRepository(Game::class);
            foreach ($odds['response'] as $data) {
                /** @var Game|null $game */
                $gameToUpdate = $gameRepository->findOneBy(['apiId' => $data['fixture']['id']]);

                if (null === $gameToUpdate || null !==  $gameToUpdate->getRealNbGoals()) {
                    continue;
                }

                foreach ($data['bookmakers'] as $bookmakerOdd) {
                    foreach ($bookmakerOdd['bets'] as $bet) {
                        if ('Match Winner' === $bet['name']) {
                            foreach ($gameToUpdate->getBets() as $gameBet) {
                                if ($gameBet instanceof WinnerBet && !$gameBet->isWinOrDraw()) {
                                    switch (true) {
                                        case $gameBet->getWinner() === $gameToUpdate->getHomeTeam():
                                            $winnerOdd = $this->getOdd($bet['values'], 'Home');
                                            break;
                                        case $gameBet->getWinner() === $gameToUpdate->getAwayTeam();
                                            $winnerOdd = $this->getOdd($bet['values'], 'Away');
                                            break;
                                        default:
                                            $winnerOdd = $this->getOdd($bet['values'], 'Draw');
                                    }
                                    $gameBet->setOdd($winnerOdd);
                                }
                            }
                        }

                        if ('Double Chance' === $bet['name']) {
                            foreach ($gameToUpdate->getBets() as $gameBet) {
                                if ($gameBet instanceof WinnerBet && $gameBet->isWinOrDraw()) {
                                    switch (true) {
                                        case $gameBet->getWinner() === $gameToUpdate->getHomeTeam():
                                            $doubleChanceOdd = $this->getOdd($bet['values'], 'Home/Draw');
                                            break;
                                        case $gameBet->getWinner() === $gameToUpdate->getAwayTeam();
                                            $doubleChanceOdd = $this->getOdd($bet['values'], 'Draw/Away');
                                            break;
                                        default:
                                            $doubleChanceOdd = null;
                                    }
                                    $gameBet->setOdd($doubleChanceOdd);
                                }
                            }
                        }

                        if ('Goals Over/Under' === $bet['name']) {
                            foreach ($gameToUpdate->getBets() as $gameBet) {
                                if ($gameBet instanceof UnderOverBet) {
                                    switch ($gameBet->getType()) {
                                        case UnderOverBet::LESS_TWO_AND_A_HALF:
                                            $underOverOdd = $this->getOdd($bet['values'], 'Under 2.5');
                                            break;
                                        case UnderOverBet::PLUS_TWO_AND_A_HALF:
                                            $underOverOdd = $this->getOdd($bet['values'], 'Over 2.5');
                                            break;
                                        case UnderOverBet::LESS_THREE_AND_A_HALF:
                                            $underOverOdd = $this->getOdd($bet['values'], 'Under 3.5');
                                            break;
                                        case UnderOverBet::PLUS_THREE_AND_A_HALF:
                                            $underOverOdd = $this->getOdd($bet['values'], 'Over 3.5');
                                            break;
                                        default:
                                            $underOverOdd = null;
                                    }

                                    $gameBet->setOdd($underOverOdd);
                                }
                            }
                        }
                    }
                }

                $this->entityManager->persist($gameToUpdate);
                $this->entityManager->flush();
                $games[] = $gameToUpdate;
                sleep(6);
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