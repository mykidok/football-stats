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
        foreach ($data['league']['standings'][0] as $teamResult) {
            $teamsHistorics[$teamResult['team']['id']]['homeGoalsFor'] = $teamResult['home']['goals']['for'];
            $teamsHistorics[$teamResult['team']['id']]['homeGoalsAgainst'] = $teamResult['home']['goals']['against'];
            $teamsHistorics[$teamResult['team']['id']]['homePlayedGames'] = $teamResult['home']['played'];
            $teamsHistorics[$teamResult['team']['id']]['awayGoalsFor'] = $teamResult['away']['goals']['for'];
            $teamsHistorics[$teamResult['team']['id']]['awayGoalsAgainst'] = $teamResult['away']['goals']['against'];
            $teamsHistorics[$teamResult['team']['id']]['awayPlayedGames'] = $teamResult['away']['played'];
        }

        return $teamsHistorics;
    }



}