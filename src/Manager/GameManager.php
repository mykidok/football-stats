<?php

namespace App\Manager;

use App\Entity\Game;
use App\Entity\UnderOverBet;
use App\Entity\WinnerBet;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;

class GameManager
{
    private $gameRepository;
    private $em;

    public function __construct(GameRepository $gameRepository, EntityManagerInterface $em)
    {
        $this->gameRepository = $gameRepository;
        $this->em = $em;
    }

    public function setPercentageForGamesOfTheDay(array $teams)
    {
        $games = $this->gameRepository->findGamesOfTheDay(new \DateTime('now'));

        /** @var Game $game */
        foreach ($games as $game) {
            $i = 0;
            $percentageThreeHalfHome = null;
            $percentageThreeHalfAway = null;
            $percentageTwoHalfHome = null;
            $percentageTwoHalfAway = null;
            $percentageWinnerAway = null;
            $percentageWinnerHome = null;
            $nbMatchHome = null;
            $nbMatchAway = null;
            foreach ($teams as $team) {
                if ($team['teamName'] === $game->getHomeTeam()->getName()) {
                    $nbMatchHome = $team['teamNbMatch'];
                    $percentageTwoHalfHome = $team['teamPercentageTwoHalf'];
                    $percentageThreeHalfHome = $team['teamPercentageThreeHalf'];
                    $percentageWinnerHome = $team['teamWinnerPercentage'];
                    $i++;
                }
                if ($team['teamName'] === $game->getAwayTeam()->getName()) {
                    $nbMatchAway = $team['teamNbMatch'];
                    $percentageTwoHalfAway = $team['teamPercentageTwoHalf'];
                    $percentageThreeHalfAway = $team['teamPercentageThreeHalf'];
                    $percentageWinnerAway = $team['teamWinnerPercentage'];
                    $i++;
                }
                if ($i === 2) {
                    $game->setNbMatchForTeams($nbmatches = $nbMatchAway+$nbMatchHome);
                    if ($nbmatches !== 0) {
                        foreach ($game->getBets() as $bet) {
                            if ($bet instanceof WinnerBet) {
                                $bet->setPercentage(((($nbMatchHome*$percentageWinnerHome)+($nbMatchAway*$percentageWinnerAway))/($nbMatchAway+$nbMatchHome)));
                            }

                            if ($bet instanceof UnderOverBet && ($bet->getType() === UnderOverBet::LESS_TWO_AND_A_HALF || $bet->getType() === UnderOverBet::PLUS_TWO_AND_A_HALF)) {
                                $bet->setPercentage(((($nbMatchHome*$percentageTwoHalfHome)+($nbMatchAway*$percentageTwoHalfAway))/($nbMatchAway+$nbMatchHome)));

                            }

                            if ($bet instanceof UnderOverBet && ($bet->getType() === UnderOverBet::LESS_THREE_AND_A_HALF || $bet->getType() === UnderOverBet::PLUS_THREE_AND_A_HALF)) {
                                $bet->setPercentage(((($nbMatchHome*$percentageThreeHalfHome)+($nbMatchAway*$percentageThreeHalfAway))/($nbMatchAway+$nbMatchHome)));
                            }
                        }
                    }

                    $this->em->persist($game);
                }
            }
        }

        $this->em->flush();
    }

    public function setOddsForGamesOfTheDay(array $clientOdds): array
    {
        $games = [];
        foreach ($clientOdds as $key => $clientOdd) {
            $homeTeamName = explode('-', $key)[0];

            /** @var Game $game */
            $game = $this->gameRepository->findOneByHomeTeamShortName(new \DateTime('now'), $homeTeamName);

            if (null === $game) {
                continue;
            }

            foreach ($game->getBets() as $bet) {
                if ($bet instanceof UnderOverBet) {
                    switch ($bet->getType()) {
                        case UnderOverBet::LESS_TWO_AND_A_HALF:
                            $odd = $clientOdd['underOverTwo'][1]['cote'];
                            break;
                        case UnderOverBet::PLUS_TWO_AND_A_HALF:
                            $odd = $clientOdd['underOverTwo'][0]['cote'];
                            break;
                        case UnderOverBet::LESS_THREE_AND_A_HALF:
                            $odd = $clientOdd['underOverThree'][1]['cote'];
                            break;
                        case UnderOverBet::PLUS_THREE_AND_A_HALF:
                            $odd = $clientOdd['underOverThree'][0]['cote'];
                            break;
                        default:
                            $odd = null;
                    }

                    $bet->setOdd((float) str_replace(',', '.', $odd));
                }

                if ($bet instanceof WinnerBet && !$bet->isWinOrDraw()) {
                    switch ($bet->getWinner()) {
                        case $game->getHomeTeam():
                            $winnerOdd = $clientOdd['winner'][0]['cote'];
                            break;
                        case $game->getAwayTeam():
                            $winnerOdd = $clientOdd['winner'][2]['cote'];
                            break;
                        default:
                            $winnerOdd = $clientOdd['winner'][1]['cote'];
                    }

                    $bet->setOdd((float) str_replace(',', '.', $winnerOdd));
                }

                if ($bet instanceof WinnerBet && $bet->isWinOrDraw()) {
                    switch ($bet->getWinner()) {
                        case $game->getHomeTeam():
                            $doubleChanceOdd = $clientOdd['doubleChance'][0]['cote'];
                            break;
                        case $game->getAwayTeam():
                            $doubleChanceOdd = $clientOdd['doubleChance'][1]['cote'];
                            break;
                        default:
                            $doubleChanceOdd = null;
                    }

                    $bet->setOdd((float) str_replace(',', '.', $doubleChanceOdd));
                }
            }

            $games[] = $game;
            $this->em->persist($game);
        }

        $this->em->flush();

        return $games;
    }
}