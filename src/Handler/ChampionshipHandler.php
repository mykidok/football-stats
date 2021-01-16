<?php

namespace App\Handler;

class ChampionshipHandler
{
    public function handleChampionshipGoals(array $data): array
    {
        $championshipGoals = [
            'totalAwayGoalsFor' => 0,
            'totalAwayGoalsAgainst' => 0,
            'totalAwayPlayedGames' => 0,
            'totalHomeGoalsFor' => 0,
            'totalHomeGoalsAgainst' => 0,
            'totalHomePlayedGames' => 0,
        ];

        foreach ($data['league']['standings'][0] as $teamResult) {
            $championshipGoals['totalHomeGoalsFor'] += $teamResult['home']['goals']['for'];
            $championshipGoals['totalHomeGoalsAgainst'] += $teamResult['home']['goals']['against'];
            $championshipGoals['totalHomePlayedGames'] += $teamResult['home']['played'];
            $championshipGoals['totalAwayGoalsFor'] += $teamResult['away']['goals']['for'];
            $championshipGoals['totalAwayGoalsAgainst'] += $teamResult['away']['goals']['against'];
            $championshipGoals['totalAwayPlayedGames'] += $teamResult['away']['played'];
        }

        return $championshipGoals;
    }
}