<?php

namespace App\Handler;

use App\Entity\Team;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;

class TeamHandler
{
    private $em;
    private $teamRepository;

    public function __construct(EntityManagerInterface $em, TeamRepository $teamRepository)
    {
        $this->em = $em;
        $this->teamRepository = $teamRepository;
    }

    public function handleTeamUpdate(array $data, array $context, array $championshipGoals)
    {
        foreach ($data['table'] as $datum) {
            if ($datum['playedGames'] === 0) {
                continue;
            }

            if ($datum['goalsFor'] === 0 && $datum['goalsAgainst'] === 0) {
                $nbGoalsPerMatch = 0;
            } else {
                $nbGoalsPerMatch = ($datum['goalsFor'] + $datum['goalsAgainst']) / $datum['playedGames'];
            }

            /** @var Team $team */
            $team = $this->teamRepository->findOneBy(['apiId' => $datum['team']['id']]);

            if ('HOME' === $context['type']) {
                if ($datum['goalsFor'] !== 0 && $datum['goalsAgainst'] !== 0) {
                    $forceAttack = ($datum['goalsFor']/$datum['playedGames'])/($championshipGoals['totalHomeGoalsFor']/$championshipGoals['totalHomePlayedGames']);
                    $forceDefense = ($datum['goalsAgainst']/$datum['playedGames'])/($championshipGoals['totalHomeGoalsAgainst']/$championshipGoals['totalHomePlayedGames']);
                } else {
                    $forceAttack = 0;
                    $forceDefense = 0;
                }
                $team
                    ->setNbGoalsPerMatchHome($nbGoalsPerMatch)
                    ->setHomePlayedGames($datum['playedGames'])
                    ->setHomeForceAttack($forceAttack)
                    ->setHomeForceDefense($forceDefense)
                ;
            } else {
                if ($datum['goalsFor'] !== 0 && $datum['goalsAgainst'] !== 0) {
                    $forceAttack = ($datum['goalsFor']/$datum['playedGames'])/($championshipGoals['totalAwayGoalsFor']/$championshipGoals['totalAwayPlayedGames']);
                    $forceDefense = ($datum['goalsAgainst']/$datum['playedGames'])/($championshipGoals['totalAwayGoalsAgainst']/$championshipGoals['totalAwayPlayedGames']);
                } else {
                    $forceAttack = 0;
                    $forceDefense = 0;
                }
                $team
                    ->setNbGoalsPerMatchAway($nbGoalsPerMatch)
                    ->setAwayPlayedGames($datum['playedGames'])
                    ->setAwayForceAttack($forceAttack)
                    ->setAwayForceDefense($forceDefense)
                ;
            }

            $this->em->persist($team);
        }
    }
}