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

        foreach ($data['standings'] as $standing) {
            if ('HOME' === $standing['type']) {
                foreach ($standing['table'] as $team) {
                    $championshipGoals['totalHomeGoalsFor'] += $team['goalsFor'];
                    $championshipGoals['totalHomeGoalsAgainst'] += $team['goalsAgainst'];
                    $championshipGoals['totalHomePlayedGames'] += $team['playedGames'];
                }
            }

            if ('AWAY' === $standing['type']) {
                foreach ($standing['table'] as $team) {
                    $championshipGoals['totalAwayGoalsFor'] += $team['goalsFor'];
                    $championshipGoals['totalAwayGoalsAgainst'] += $team['goalsAgainst'];
                    $championshipGoals['totalAwayPlayedGames'] += $team['playedGames'];
                }
            }
        }

        return $championshipGoals;
    }
}