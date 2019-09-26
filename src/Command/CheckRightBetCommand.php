<?php

namespace App\Command;

use App\Entity\Championship;
use App\Entity\Client;
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

    public function __construct(Client $client, EntityManagerInterface $em, GameRepository $gameRepository)
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
            $gameDay = $this->client->get('matches', [
                    'query' => [
                        'competitions' => $championship->getApiId(),
                        'dateTo' => (new \DateTime('yesterday'))->format('Y-m-d'),
                        'dateFrom' => (new \DateTime('yesterday'))->format('Y-m-d'),
                    ]
                ]
            );

            if (empty($gameDay['matches'])) {
                $output->writeln(sprintf('------ No match today for %s ------', $championship->getName()));
                continue;
            }

            $i = 0;
            foreach ($gameDay['matches'] as $item) {
                if ('FINISHED' === $item['status']) {
                    /** @var Game|null $game */
                    $game = $this->gameRepository->findOneBy(['apiId' => $item['id']]);

                    if (null !== $game) {
                        $realNbGoals = $item['score']['fullTime']['homeTeam'] + $item['score']['fullTime']['awayTeam'];
                        if (($game->getAverageExpectedNbGoals() > Game::LIMIT && $realNbGoals > Game::LIMIT)
                            || $game->getAverageExpectedNbGoals() <= Game::LIMIT && $realNbGoals <= Game::LIMIT ) {
                            $game->setGoodResult(true);
                        } else {
                            $game->setGoodResult(false);
                        }

                        $game->setRealNbGoals($realNbGoals);

                        if (
                            ($item['score']['winner'] === 'HOME_TEAM' && $game->getPrevisionalWinner() === $game->getHomeTeam())
                             || ($item['score']['winner'] === 'AWAY_TEAM' && $game->getPrevisionalWinner() === $game->getAwayTeam())
                             || ($item['score']['winner'] === 'DRAW' && null === $game->getPrevisionalWinner())
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