<?php

namespace App\Handler;

use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;

class TeamHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function handleTeamUpdate(array $data, array $context)
    {
        foreach ($data['table'] as $datum) {
            $nbGoalsPerMatch = ($datum['goalsFor'] + $datum['goalsAgainst']) / $datum['playedGames'];

            $teamRepository = $this->em->getRepository(Team::class);

            /** @var Team $team */
            $team = $teamRepository->findOneBy(['apiId' => $datum['team']['id']]);

            'HOME' === $context['type'] ? $team->setNbGoalsPerMatchHome($nbGoalsPerMatch)
                : $team->setNbGoalsPerMatchAway($nbGoalsPerMatch);

            $this->em->persist($team);
        }

    }

}