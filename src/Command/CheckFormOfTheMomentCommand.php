<?php

namespace App\Command;


use App\Entity\Game;
use App\Entity\Team;
use App\Entity\UnderOverBet;
use App\Entity\WinnerBet;
use App\Repository\GameRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckFormOfTheMomentCommand extends Command
{
    private $em;
    private $gameRepository;
    private $teamRepository;

    public function __construct(EntityManagerInterface $em, GameRepository $gameRepository, TeamRepository $teamRepository)
    {
        parent::__construct('api:check:form');
        $this->setDescription('Check form of the moment for each match of the day');

        $this->em = $em;
        $this->gameRepository = $gameRepository;
        $this->teamRepository = $teamRepository;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $games = $this->gameRepository->findGamesOfTheDay(new \DateTime());

        if (empty($games)) {
            $output->writeln('No games played today');
        }

        $teamsOfTheDay = $this->teamRepository->findTeamsWithGamesToday();

        /** @var Team $team */
        foreach ($teamsOfTheDay as $team) {
            /** @var Game[] $lastGames */
            $lastGames = $this->gameRepository->findLastFourGamesForTeam($team);

            if (count($lastGames) === 0) {
                continue;
            }

            $goals = 0;
            $points = 0;
            foreach ($lastGames as $lastGame) {
                $goals += $lastGame->getRealNbGoals();
                if (null === $lastGame->getWinner()) {
                    $points += 1;
                } elseif ($lastGame->getWinner()->getId() === $team->getId()) {
                    $points += 3;
                }
            }

            $team->setMomentForm($goals/count($lastGames));
            $team->setPointsMomentForm($points);
            $this->em->persist($team);
        }

        $this->em->flush();

        /** @var Game $game */
        foreach ($games as $game) {
            $homeTeamForm =  $game->getHomeTeam()->getMomentForm();
            $awayTeamForm = $game->getAwayTeam()->getMomentForm();

            if (null !== $awayTeamForm && null !== $homeTeamForm) {
                $formForMatch = ($game->getHomeTeam()->getMomentForm() + $game->getAwayTeam()->getMomentForm())/2;
            } elseif (null === $homeTeamForm && null !== $awayTeamForm) {
                $formForMatch = $game->getAwayTeam()->getMomentForm();
            } elseif (null === $awayTeamForm && null !== $homeTeamForm) {
                $formForMatch = $game->getHomeTeam()->getMomentForm();
            } else {
                continue;
            }

            foreach ($game->getBets() as $bet) {
                $form = false;
                $homePointMomentForm = $game->getHomeTeam()->getPointsMomentForm();
                $awayPointMomentForm = $game->getAwayTeam()->getPointsMomentForm();
                if ($bet instanceof WinnerBet && $bet->isWinOrDraw()) {
                    if (
                        (($homePointMomentForm > $awayPointMomentForm || $homePointMomentForm === $awayPointMomentForm) && null!== $bet->getWinner() && $bet->getWinner()->getId() === $game->getHomeTeam()->getId())
                        || (($awayPointMomentForm > $homePointMomentForm || $homePointMomentForm === $awayPointMomentForm) && null!== $bet->getWinner() && $bet->getWinner()->getId() === $game->getAwayTeam()->getId())
                    ) {
                        $form = true;
                    }
                }

                if ($bet instanceof WinnerBet && !$bet->isWinOrDraw()) {
                    if (
                        ($homePointMomentForm > $awayPointMomentForm && null!== $bet->getWinner() && $bet->getWinner()->getId() === $game->getHomeTeam()->getId())
                        || ($awayPointMomentForm > $homePointMomentForm && null!== $bet->getWinner() && $bet->getWinner()->getId() === $game->getAwayTeam()->getId())
                        || ($homePointMomentForm === $awayPointMomentForm && null === $bet->getWinner())
                    ) {
                        $form = true;
                    }
                }

                if ($bet instanceof UnderOverBet) {
                    if (UnderOverBet::LESS_TWO_AND_A_HALF === $bet->getType() || UnderOverBet::PLUS_TWO_AND_A_HALF === $bet->getType()) {
                        if (($formForMatch > UnderOverBet::LIMIT_2_5 && $game->getAverageExpectedNbGoals() > UnderOverBet::LIMIT_2_5)
                            || ($formForMatch < UnderOverBet::LIMIT_2_5 && $game->getAverageExpectedNbGoals() < UnderOverBet::LIMIT_2_5)) {
                            $form = true;
                        }
                    }
                    if (UnderOverBet::LESS_THREE_AND_A_HALF === $bet->getType() || UnderOverBet::PLUS_THREE_AND_A_HALF === $bet->getType()) {
                        if (($formForMatch > UnderOverBet::LIMIT_3_5 && $game->getAverageExpectedNbGoals() > UnderOverBet::LIMIT_3_5)
                            || ($formForMatch < UnderOverBet::LIMIT_3_5 && $game->getAverageExpectedNbGoals() < UnderOverBet::LIMIT_3_5)) {
                            $form = true;
                        }
                    }
                }

                $bet->setForm($form);
                $this->em->persist($bet);
            }

            $this->em->persist($game);
            $output->writeln(sprintf('%s - %s updated',
                    $game->getHomeTeam()->getName(),
                    $game->getAwayTeam()->getName()));
        }

        $this->em->flush();
    }


}