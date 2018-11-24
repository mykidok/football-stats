<?php

namespace App\Handler;

use App\Entity\Team;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;

class TeamHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var TeamRepository
     */
    private $teamRepository;

    public function __construct(EntityManagerInterface $em, TeamRepository $teamRepository)
    {
        $this->em = $em;
        $this->teamRepository = $teamRepository;
    }

    public function handleTeamUpdate(array $data, array $context)
    {
        foreach ($data['table'] as $datum) {
            $nbGoalsPerMatch = ($datum['goalsFor'] + $datum['goalsAgainst']) / $datum['playedGames'];

            /** @var Team $team */
            $team = $this->teamRepository->findOneBy(['apiId' => $datum['team']['id']]);

            'HOME' === $context['type'] ? $team->setNbGoalsPerMatchHome($nbGoalsPerMatch)
                : $team->setNbGoalsPerMatchAway($nbGoalsPerMatch);

            $this->em->persist($team);
        }

    }

}