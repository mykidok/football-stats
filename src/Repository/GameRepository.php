<?php

namespace App\Repository;

use App\Entity\Game;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function findGamesOfTheDay(\DateTime $date)
    {
        $qb = $this->createQueryBuilder('g');

        $qb
            ->select('g')
            ->where('g.date > :date_start')
            ->andWhere('g.date < :date_end')
            ->setParameters([
                'date_start' => $date->format('Y-m-d 00:00:00'),
                'date_end' => $date->format('Y-m-d 23:59:59'),
            ])
        ;

        return $qb->getQuery()->getResult();
    }

    public function findOneByHomeTeamShortName(\DateTime $date, string $shortName)
    {
        $qb = $this->createQueryBuilder('g');

        $qb
            ->select('g')
            ->where('g.date > :date_start')
            ->andWhere('g.date < :date_end')
            ->andWhere('t.shortName = :shortName')
            ->leftJoin(Team::class, 't', Join::WITH, 'g.homeTeam = t')
            ->setParameters([
                'date_start' => $date->format('Y-m-d 00:00:00'),
                'date_end' => $date->format('Y-m-d 23:59:59'),
                'shortName' => $shortName,
            ])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findLastFourGamesForTeam(Team $team)
    {
        $qb = $this->createQueryBuilder('g');

        $qb
            ->where($qb->expr()->orX('g.homeTeam = :team', 'g.awayTeam = :team'))
            ->andWhere('g.finished = :true')
            ->orderBy('g.id', 'DESC')
            ->setMaxResults(4)
            ->setParameters(['team' => $team, 'true' => true])
        ;

        return $qb->getQuery()->getResult();
    }
}