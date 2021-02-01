<?php


namespace App\Manager;


use App\Entity\Bet;
use App\Entity\Game;
use App\Entity\UnderOverBet;
use App\Entity\WinnerBet;

class BetManager
{
    public function getFormOfTheMomentForBet(Game $game, Bet $bet, float $formForMatch): bool
    {
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

        return $form;
    }

}