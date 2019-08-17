<?php

namespace App\Handler;

use App\Entity\Team;
use App\Repository\TeamRepository;

class TeamHistoricHandler
{
    private $teamRepository;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function handleTeamsHistorics(array $data): array
    {
        $teamsHistorics = [];
        foreach ($data['standings'] as $standing) {
            if ('TOTAL' === $standing['type']) {
                continue;
            }

            if ('HOME' === $standing['type']) {
                foreach ($standing['table'] as $team) {
                    $teamsHistorics[$team['team']['id']]['homeGoalsFor'] = $team['goalsFor'];
                    $teamsHistorics[$team['team']['id']]['homeGoalsAgainst'] = $team['goalsAgainst'];
                    $teamsHistorics[$team['team']['id']]['homePlayedGames'] = $team['playedGames'];
                }
            }

            if ('AWAY' === $standing['type']) {
                foreach ($standing['table'] as $team) {
                    $teamsHistorics[$team['team']['id']]['awayGoalsFor'] = $team['goalsFor'];
                    $teamsHistorics[$team['team']['id']]['awayGoalsAgainst'] = $team['goalsAgainst'];
                    $teamsHistorics[$team['team']['id']]['awayPlayedGames'] = $team['playedGames'];
                }
            }
        }

        return $teamsHistorics;
    }



}