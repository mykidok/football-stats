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

    public function findTeamWithFormOfTheMoment(\DateTime $date)
    {
        $qb = $this->createQueryBuilder('t');

        $subquery = $this->createQueryBuilder('g');
        $subquery
            ->resetDQLPart('from')
            ->select($subquery->expr()->avg('g.realNbGoals'))
            ->from(Game::class, 'g')
            ->where($subquery
                        ->expr()
                        ->orX('g.homeTeam = t.id', 'g.awayTeam = t.id')
            )
            ->andWhere('g.goodResult IS NOT NULL')
            ->orderBy('g.id', 'DESC')
            ->setMaxResults(5)
        ;

        $qb
            ->select('t.apiId')
            ->addSelect(sprintf('(%s) as momentForm', $subquery->getDQL()))
            ->leftJoin(Game::class, 'ag', Join::WITH, 't = ag.awayTeam')
            ->leftJoin(Game::class, 'hg', Join::WITH, 't = hg.homeTeam')
            ->where(
                $qb->expr()->orX(
                    'ag.awayTeam = t AND ag.goodResult IS NOT NULL',
                    'hg.homeTeam = t AND hg.goodResult IS NOT NULL')
            )
            ->andWhere(
                $qb->expr()->orX(
                    'ag.date < :date_end AND ag.date > :date_start',
                    'hg.date < :date_end AND hg.date > :date_start')
            )
            ->setParameters([
                'date_start' => $date->format('Y-m-d 00:00:00'),
                'date_end' => $date->format('Y-m-d 23:59:59'),
            ])
        ;

        return $qb->getQuery()->getScalarResult();
    }
}