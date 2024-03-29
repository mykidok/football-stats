<?php

namespace App\Command;

use App\Entity\BothTeamsScoreBet;
use App\Entity\Championship;
use App\Entity\Client;
use App\Entity\Game;
use App\Entity\UnderOverBet;
use App\Entity\WinnerBet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckRightBetCommand extends Command
{
    private $client;
    private $em;

    public function __construct(Client $client, EntityManagerInterface $em)
    {
        parent::__construct('api:check:bet');
        $this->setDescription('Check results of the day to check if bets were right');

        $this->client = $client;
        $this->em = $em;
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
                        'season' => (int) $championship->getStartDate()->format('Y'),
                        'date' => (new \DateTime('yesterday'))->format('Y-m-d'),
                    ]
                ]
            );

            if (!empty($gameDay['errors'])) {
                foreach ($gameDay['errors'] as $key => $error) {
                    $output->writeln(sprintf('%s : %s', $key, $error));
                }
                sleep(6);
                continue;
            }


            if (0 === $gameDay['results']) {
                $output->writeln(sprintf('------ No match today for %s ------', $championship->getName()));
                sleep(6);
                continue;
            }

            $i = 0;
            $gameRepository = $this->em->getRepository(Game::class);
            foreach ($gameDay['response'] as $item) {
                if ('Match Finished' === $item['fixture']['status']['long']) {
                    /** @var Game|null $game */
                    $game = $gameRepository->findOneBy(['apiId' => $item['fixture']['id']]);

                    if (null !== $game) {
                        $realNbGoals = $item['goals']['home'] + $item['goals']['away'];
                        foreach ($game->getBets() as $bet) {
                            if (
                                (UnderOverBet::LESS_TWO_AND_A_HALF ===  $bet->getType() && $realNbGoals < UnderOverBet::LIMIT_2_5)
                                || (UnderOverBet::LESS_THREE_AND_A_HALF ===  $bet->getType() && $realNbGoals < UnderOverBet::LIMIT_3_5)
                                || (UnderOverBet::PLUS_TWO_AND_A_HALF ===  $bet->getType() && $realNbGoals > UnderOverBet::LIMIT_2_5)
                                || (UnderOverBet::PLUS_THREE_AND_A_HALF ===  $bet->getType() && $realNbGoals > UnderOverBet::LIMIT_3_5)
                                || ($bet instanceof WinnerBet && $bet->getWinner() === $game->getHomeTeam() && $item['teams']['home']['winner'])
                                || ($bet instanceof WinnerBet && $bet->isWinOrDraw() && $bet->getWinner() === $game->getHomeTeam() && ($item['teams']['home']['winner'] || (!$item['teams']['home']['winner'] && !$item['teams']['away']['winner'])))
                                || ($bet instanceof WinnerBet && $bet->isWinOrDraw() && $bet->getWinner() === $game->getAwayTeam() && ($item['teams']['away']['winner'] || (!$item['teams']['home']['winner'] && !$item['teams']['away']['winner'])))
                                || ($bet instanceof WinnerBet && $bet->getWinner() === $game->getAwayTeam() && $item['teams']['away']['winner'])
                                || ($bet instanceof WinnerBet && null === $bet->getWinner() && !$item['teams']['home']['winner'] && !$item['teams']['away']['winner'])
                                || ($bet instanceof BothTeamsScoreBet && $bet->isBothTeamsScore() && $item['goals']['home'] > 0 && $item['goals']['away'] > 0)
                                || ($bet instanceof BothTeamsScoreBet && !$bet->isBothTeamsScore() && ($item['goals']['home'] === 0 || $item['goals']['away'] === 0))
                            ) {
                                $bet->setGoodResult(true);
                            } else {
                                $bet->setGoodResult(false);
                            }
                        }

                        $game
                            ->setRealNbGoals($realNbGoals)
                            ->setFinished(true)
                            ->setHomeTeamGoals($item['goals']['home'])
                            ->setAwayTeamGoals($item['goals']['away'])
                        ;

                        if ($item['teams']['home']['winner']) {
                            $game->setWinner($game->getHomeTeam());
                        } elseif ($item['teams']['away']['winner']) {
                            $game->setWinner($game->getAwayTeam());
                        }

                        $this->em->persist($game);
                        $this->em->flush();
                        $i++;
                    }
                }
            }
            $output->writeln(sprintf('------ %d matches updated for %s ------', $i, $championship->getName()));
            sleep(6);
        }
    }

}