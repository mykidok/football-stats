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

    public function handleTeamUpdate(array $data, array $championshipGoals)
    {
        foreach ($data['standings'][0] as $datum) {
            /** @var Team $team */
            $team = $this->teamRepository->findOneBy(['apiId' => $datum['team']['id']]);

            $homeForceAttack = 0;
            $homeForceDefense = 0;
            $awayForceAttack = 0;
            $awayForceDefense = 0;
            $nbGoalsPerMatchHome = 0;
            $nbGoalsPerMatchAway  =0;

            if (($homePlayedGames = $datum['home']['played']) !== 0) {
                $homeGoalsFor = $datum['home']['goals']['for'];
                $homeGoalsAgainst =  $datum['home']['goals']['against'];

                if ($homeGoalsFor > 0 || $homeGoalsAgainst > 0) {
                    $nbGoalsPerMatchHome = ($homeGoalsFor + $homeGoalsAgainst) / $homePlayedGames;
                }

                if ($homeGoalsFor !== 0 && $homeGoalsAgainst !== 0) {
                    $homeForceAttack = ($homeGoalsFor/$homePlayedGames)/($championshipGoals['totalHomeGoalsFor']/$championshipGoals['totalHomePlayedGames']);
                    $homeForceDefense = ($homeGoalsAgainst/$homePlayedGames)/($championshipGoals['totalHomeGoalsAgainst']/$championshipGoals['totalHomePlayedGames']);
                }


            }

            if (($awayPlayedGames = $datum['away']['played']) !== 0) {
                $awayGoalsFor = $datum['away']['goals']['for'];
                $awayGoalsAgainst =  $datum['away']['goals']['against'];

                if ($awayGoalsFor > 0 || $awayGoalsAgainst > 0) {
                    $nbGoalsPerMatchAway = ($awayGoalsFor + $awayGoalsAgainst) / $awayPlayedGames;
                }

                if ($awayGoalsFor !== 0 && $awayGoalsAgainst !== 0) {
                    $awayForceAttack = ($awayGoalsFor/$awayPlayedGames)/($championshipGoals['totalAwayGoalsFor']/$championshipGoals['totalAwayPlayedGames']);
                    $awayForceDefense = ($awayGoalsAgainst/$awayPlayedGames)/($championshipGoals['totalAwayGoalsAgainst']/$championshipGoals['totalAwayPlayedGames']);
                }
            }

            $team
                ->setNbGoalsPerMatchHome($nbGoalsPerMatchHome)
                ->setHomePlayedGames($homePlayedGames)
                ->setHomeForceAttack($homeForceAttack)
                ->setHomeForceDefense($homeForceDefense)
                ->setNbGoalsPerMatchAway($nbGoalsPerMatchAway)
                ->setAwayPlayedGames($awayPlayedGames)
                ->setAwayForceAttack($awayForceAttack)
                ->setAwayForceDefense($awayForceDefense)
            ;

            $this->em->persist($team);
        }
    }
}