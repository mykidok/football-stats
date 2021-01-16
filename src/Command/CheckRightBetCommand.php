<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\Client;
use App\Entity\DataClient;
use App\Entity\Game;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckRightBetCommand extends Command
{
    private $client;
    private $em;
    private $gameRepository;

    public function __construct(DataClient $client, EntityManagerInterface $em, GameRepository $gameRepository)
    {
        parent::__construct('api:check:bet');
        $this->setDescription('Check results of the day to check if bets were right');

        $this->client = $client;
        $this->em = $em;
        $this->gameRepository = $gameRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $championshipRepository = $this->em->getRepository(Championship::class);
        $championships = $championshipRepository->findAll();

        /** @var Championship $championship */
        foreach ($championships as $championship) {
            $gameDay = $this->client->get('fixtures', [
                    'query' => [
                        'league' => $championship->getApiId(),
                        'season' => 2020,
                        'date' => (new \DateTime('yesterday'))->format('Y-m-d'),
                    ]
                ]
            );

            if (0 === $gameDay['results']) {
                $output->writeln(sprintf('------ No match today for %s ------', $championship->getName()));
                continue;
            }

            $i = 0;
            foreach ($gameDay['response'] as $item) {
                if ('Match Finished' === $item['status']['long']) {
                    /** @var Game|null $game */
                    $game = $this->gameRepository->findOneBy(['apiId' => $item['fixture']['id']]);

                    if (null !== $game) {
                        $realNbGoals = $item['goals']['home'] + $item['goals']['away'];
                        if (($game->getAverageExpectedNbGoals() > Game::LIMIT && $realNbGoals > Game::LIMIT)
                            || $game->getAverageExpectedNbGoals() <= Game::LIMIT && $realNbGoals <= Game::LIMIT ) {
                            $game->setGoodResult(true);
                        } else {
                            $game->setGoodResult(false);
                        }

                        $game->setRealNbGoals($realNbGoals);

                        if ($item['teams']['home']['winner']) {
                            $game->setWinner($game->getHomeTeam());
                        } elseif ($item['teams']['away']['winner']) {
                            $game->setWinner($game->getAwayTeam());
                        }

                        if (
                            ($item['teams']['home']['winner'] && $game->getPrevisionalWinner() === $game->getHomeTeam())
                             || ($item['teams']['away']['winner'] && $game->getPrevisionalWinner() === $game->getAwayTeam())
                             || (!$item['teams']['home']['winner'] && !$item['teams']['away']['winner'] && null === $game->getPrevisionalWinner())
                        ) {
                            $game->setWinnerResult(true);
                        } else {
                            $game->setWinnerResult(false);
                        }

                        $this->em->persist($game);
                        $this->em->flush();
                        $i++;
                    }
                }
            }
            $output->writeln(sprintf('------ %d matches updated for %s ------', $i, $championship->getName()));
        }
    }

}