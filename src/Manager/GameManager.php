<?php

namespace App\Manager;


use App\Entity\Game;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;

class GameManager
{
    /**
     * @var GameRepository
     */
    private $gameRepository;

    /**
     * @var EntityManagerInterface
     */
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
}