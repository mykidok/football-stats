<?php

namespace App\Manager;

use App\Entity\Game;
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
            $percentageAway = null;
            $percentageHome = null;
            $nbMatchHome = null;
            $nbMatchAway = null;
            foreach ($teams as $team) {
                if ($team['teamName'] === $game->getHomeTeam()->getName()) {
                    $nbMatchHome = $team['teamNbMatch'];
                    $percentageHome = $team['teamPercentage'];
                    $i++;
                }
                if ($team['teamName'] === $game->getAwayTeam()->getName()) {
                    $nbMatchAway = $team['teamNbMatch'];
                    $percentageAway = $team['teamPercentage'];
                    $i++;
                }
                if ($i === 2) {
                    $game->setNbMatchForTeams($nbMatchAway+$nbMatchHome);
                    if ($nbMatchAway+$nbMatchHome !== 0) {
                        $game->setPercentage(((($nbMatchHome*$percentageHome)+($nbMatchAway*$percentageAway))/($nbMatchAway+$nbMatchHome)));
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
        foreach ($clientOdds as $clientOdd) {
            $homeTeamName = explode('-', $clientOdd['label'])[0];

            /** @var Game $game */
            $game = $this->gameRepository->findOneByHomeTeamShortName(new \DateTime('now'), $homeTeamName);

            if (null === $game) {
                continue;
            }

            if ($game->getAverageExpectedNbGoals() <= Game::LIMIT) {
                $odd = str_replace(',', '.', $clientOdd['outcomes'][1]['cote']);
                $game->setOdd($odd);
            } elseif ($game->getAverageExpectedNbGoals() > Game::LIMIT) {
                $odd = str_replace(',', '.', $clientOdd['outcomes'][0]['cote']);
                $game->setOdd($odd);
            }

            if ($game->getPrevisionalWinner() === $game->getHomeTeam()) {
                $winnerOdd = $clientOdd['winnerOdds'][0]['cote'];
            } elseif ($game->getPrevisionalWinner() === $game->getAwayTeam()) {
                $winnerOdd = $clientOdd['winnerOdds'][2]['cote'];
            } else {
                $winnerOdd = $clientOdd['winnerOdds'][1]['cote'];
            }

            $game->setWinnerOdd((float) str_replace(',', '.', $winnerOdd));

            $games[] = $game;
            $this->em->persist($game);
        }

        $this->em->flush();

        return $games;
    }
}