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
            ->orderBy('g.previsionalNbGoals', 'ASC')
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
}