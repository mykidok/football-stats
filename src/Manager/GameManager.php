<?php

namespace App\Manager;

use App\Entity\Bet;
use App\Entity\BothTeamsScoreBet;
use App\Entity\Game;
use App\Entity\UnderOverBet;
use App\Entity\WinnerBet;
use Doctrine\ORM\EntityManagerInterface;

class GameManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function setPercentageForGamesOfTheDay()
    {
        $betRepository = $this->em->getRepository(Bet::class);
        $gameRepository = $this->em->getRepository(Game::class);
        $games = $gameRepository->findGamesOfTheDay(new \DateTime('now'));

        /** @var Game $game */
        foreach ($games as $game) {
            foreach ($game->getBets() as $bet) {
                $homeBets = $betRepository->findBetsForTeams($game->getHomeTeam(), $bet);
                $awayBets = $betRepository->findBetsForTeams($game->getAwayTeam(), $bet);

                if (($nbBetsHome = count($homeBets)) === 0 || ($nbBetsAway = count($awayBets)) === 0) {
                    continue;
                }

                $homeGoodBets = array_filter($homeBets, function(Bet $homeBet) {
                    return $homeBet->isGoodResult();
                });

                $awayGoodBets = array_filter($awayBets, function(Bet $awayBet) {
                    return $awayBet->isGoodResult();
                });

                $percentageHome = count($homeGoodBets) * 100 / $nbBetsHome;
                $percentageAway = count($awayGoodBets) * 100 / $nbBetsAway;

                $bet->setPercentage(((($nbBetsHome*$percentageHome)+($nbBetsAway*$percentageAway))/($nbBetsHome+$nbBetsAway)));
            }
        }

        $this->em->flush();
    }

    public function setOddsForGamesOfTheDay(array $clientOdds): array
    {
        $games = [];
        $alreadyImportedHomeTeamBets = [];
        foreach ($clientOdds as $key => $clientOdd) {
            $homeTeamName = explode('-', $key)[0];

            if (\in_array($homeTeamName, $alreadyImportedHomeTeamBets, true)) {
                continue;
            }
            $alreadyImportedHomeTeamBets[] = $homeTeamName;

            $gameRepository = $this->em->getRepository(Game::class);

            /** @var Game $game */
            $game = $gameRepository->findOneByHomeTeamShortName(new \DateTime('now'), $homeTeamName);

            if (null === $game) {
                continue;
            }

            foreach ($game->getBets() as $bet) {
                if ($bet instanceof UnderOverBet) {
                    switch ($bet->getType()) {
                        case UnderOverBet::LESS_TWO_AND_A_HALF:
                            $odd = isset($clientOdd['underOverTwo']) ? $clientOdd['underOverTwo'][1]['cote'] : null;
                            break;
                        case UnderOverBet::PLUS_TWO_AND_A_HALF:
                            $odd = isset($clientOdd['underOverTwo']) ? $clientOdd['underOverTwo'][0]['cote'] : null;
                            break;
                        case UnderOverBet::LESS_THREE_AND_A_HALF:
                            $odd = isset($clientOdd['underOverThree']) ? $clientOdd['underOverThree'][1]['cote'] : null;
                            break;
                        case UnderOverBet::PLUS_THREE_AND_A_HALF:
                            $odd = isset($clientOdd['underOverThree']) ? $clientOdd['underOverThree'][0]['cote'] : null;
                            break;
                        default:
                            $odd = null;
                    }

                    $bet->setOdd((float) str_replace(',', '.', $odd));

                    if ($odd !== null && $bet->getOdd() < Bet::MINIMUM_ODD) {
                        $game->removeBet($bet);
                    }
                }

                if ($bet instanceof WinnerBet && !$bet->isWinOrDraw() && isset($clientOdd['winner'])) {
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

                    if ($winnerOdd !== null && $bet->getOdd() < Bet::MINIMUM_ODD) {
                        $game->removeBet($bet);
                    }
                }

                if ($bet instanceof WinnerBet && $bet->isWinOrDraw() && isset($clientOdd['doubleChance'])) {
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

                    if ($doubleChanceOdd !== null && $bet->getOdd() < Bet::MINIMUM_ODD) {
                        $game->removeBet($bet);
                    }
                }

                if ($bet instanceof BothTeamsScoreBet && isset($clientOdd['bothTeamsScore'])) {
                    $bothTeamsScoreOdd = $bet->isBothTeamsScore() ? $clientOdd['bothTeamsScore'][0]['cote'] : $clientOdd['bothTeamsScore'][1]['cote'];

                    $bet->setOdd((float) str_replace(',', '.', $bothTeamsScoreOdd));

                    if ($bothTeamsScoreOdd !== null && $bet->getOdd() < Bet::MINIMUM_ODD) {
                        $game->removeBet($bet);
                    }
                }
            }

            $games[] = $game;
            $this->em->persist($game);
        }

        $this->em->flush();

        return $games;
    }

    public function getFormForMatch(Game $game): ?float
    {
        $homeTeamForm =  $game->getHomeTeam()->getMomentForm();
        $awayTeamForm = $game->getAwayTeam()->getMomentForm();

        switch (true) {
            case null !== $awayTeamForm && null !== $homeTeamForm:
                $formForMatch = ($game->getHomeTeam()->getMomentForm() + $game->getAwayTeam()->getMomentForm())/2;
                break;
            case null === $homeTeamForm && null !== $awayTeamForm:
                $formForMatch = $game->getAwayTeam()->getMomentForm();
                break;
            case null === $awayTeamForm && null !== $homeTeamForm:
                $formForMatch = $game->getHomeTeam()->getMomentForm();
                break;
            default:
                $formForMatch = null;
        }

        return $formForMatch;
    }
}