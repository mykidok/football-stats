<?php

namespace App\Manager;

use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;

class TeamManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function handleTeamUpdate(array $data, array $championshipGoals)
    {
        $teamRepository = $this->em->getRepository(Team::class);
        foreach ($data['standings'][0] as $teamStanding) {
            /** @var Team $team */
            $team = $teamRepository->findOneBy(['apiId' => $teamStanding['team']['id']]);

            if (null === $team) {
                continue;
            }

            $homeForceAttack = null;
            $homeForceDefense = null;
            $awayForceAttack = null;
            $awayForceDefense = null;
            $nbGoalsPerMatchHome = null;
            $nbGoalsPerMatchAway = null;

            if (($homePlayedGames = $teamStanding['home']['played']) !== 0) {
                $homeGoalsFor = $teamStanding['home']['goals']['for'];
                $homeGoalsAgainst =  $teamStanding['home']['goals']['against'];

                if ($homeGoalsFor > 0 || $homeGoalsAgainst > 0) {
                    $nbGoalsPerMatchHome = ($homeGoalsFor + $homeGoalsAgainst) / $homePlayedGames;
                }

                if ($homeGoalsFor !== 0 && $homeGoalsAgainst !== 0) {
                    $homeForceAttack = ($homeGoalsFor/$homePlayedGames)/($championshipGoals['totalHomeGoalsFor']/$championshipGoals['totalHomePlayedGames']);
                    $homeForceDefense = ($homeGoalsAgainst/$homePlayedGames)/($championshipGoals['totalHomeGoalsAgainst']/$championshipGoals['totalHomePlayedGames']);
                }
            }

            if (($awayPlayedGames = $teamStanding['away']['played']) !== 0) {
                $awayGoalsFor = $teamStanding['away']['goals']['for'];
                $awayGoalsAgainst =  $teamStanding['away']['goals']['against'];

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