<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function findTeamsWithGamesToday(\DateTime $date)
    {
        $qb = $this->createQueryBuilder('t');

        $qb
            ->leftJoin(Game::class, 'ag', Join::WITH, 't = ag.awayTeam')
            ->leftJoin(Game::class, 'hg', Join::WITH, 't = hg.homeTeam')
            ->where(
                $qb->expr()->orX(
                    'ag.awayTeam = t', 'hg.homeTeam = t')
            )
            ->andWhere(
                $qb->expr()->orX(
                    'ag.date < :date_end AND ag.date > :date_start',
                    'hg.date < :date_end AND hg.date > :date_start')
            )
            ->groupBy('t.id')
            ->setParameters([
                'date_start' => $date->format('Y-m-d 00:00:00'),
                'date_end' => $date->format('Y-m-d 23:59:59'),
            ])
        ;

        return $qb->getQuery()->getResult();
    }
}