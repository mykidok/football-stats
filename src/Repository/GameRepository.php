<?php

namespace App\Repository;

use App\Entity\Championship;
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

    public function findGamesOfTheDayForChampionship(Championship $championship, \DateTime $date)
    {
        $qb = $this->createQueryBuilder('g');

        $qb
            ->select('g')
            ->leftJoin(Championship::class, 'ch', Join::WITH, 'g.championship = ch.id')
            ->where('ch.id = :championship')
            ->andWhere('g.date > :date_start')
            ->andWhere('g.date < :date_end')
            ->setParameters([
                'championship' => $championship->getId(),
                'date_start' => $date->format('Y-m-d 00:00:00'),
                'date_end' => $date->format('Y-m-d 23:59:59'),
            ])
            ->orderBy('g.averageExpectedNbGoals', 'ASC')
        ;

        return $qb->getQuery()->getResult();
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

    public function findGamesOfTheDayOrderByOddAndPercentage(\DateTime $date)
    {
        $qb = $this->createQueryBuilder('g');

        $qb
            ->select('g')
            ->where('g.date > :date_start')
            ->andWhere('g.date < :date_end')
            ->andWhere($qb->expr()->isNotNull('g.odd'))
            ->orderBy('g.previsionIsSameAsExpected', 'DESC')
            ->addOrderBy('g.momentForm', 'DESC')
            ->addOrderBy('g.percentage', 'DESC')
            ->addOrderBy('g.nbMatchForTeams', 'DESC')
            ->addOrderBy('g.odd', 'DESC')
            ->addOrderBy('g.myOdd', 'ASC')
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
            ->select('g.realNbGoals')
            ->where($qb->expr()->orX('g.homeTeam = :team', 'g.awayTeam = :team'))
            ->andWhere($qb->expr()->isNotNull('g.goodResult'))
            ->orderBy('g.id', 'DESC')
            ->setMaxResults(4)
            ->setParameters(['team' => $team])
        ;

        return $qb->getQuery()->getResult();
    }
}