<?php

namespace App\Handler;

use App\Entity\Match;
use App\Entity\Standing;
use App\Entity\Team;

class MatchDayHandler
{
    public function handleTeam(Team $matchTeam, Standing $standing)
    {
        /** @var Team $team */
        foreach ($standing->getTable()->getTeams() as $team) {
            if ($matchTeam->getId() === $team->getId()) {
                $matchTeam->setNbGoalsPerMatch(round($team->getNbGoalsPerMatch(), 3));
            }
        }
    }
}