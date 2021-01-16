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
        $dateStart = $date->format('Y-m-d 00:00:00');
        $dateEnd = $date->format('Y-m-d 23:59:59');
        $query = <<<SQL
SELECT * 
FROM game g
    WHERE g.date > '$dateStart'
    AND g.date < '$dateEnd'
    AND g.odd IS NOT NULL
    AND g.odd > 1.35
ORDER BY 
      g.moment_form DESC,
      g.prevision_is_same_as_expected DESC,
      CASE
      WHEN (g.my_odd - g.odd) > 0 THEN (g.my_odd - g.odd)
      WHEN (g.odd - g.my_odd) > 0 THEN (g.odd - g.my_odd)
      END ASC,
      g.percentage DESC,
      g.nb_match_for_teams DESC,
      g.odd DESC,
      g.my_odd DESC
SQL;


        $em = $this->getEntityManager();
        return $em->getConnection()->executeQuery($query)->fetchAll();
    }

    public function findGamesOfTheDayWinnerOdds(\DateTime $date)
    {
        $dateStart = $date->format('Y-m-d 00:00:00');
        $dateEnd = $date->format('Y-m-d 23:59:59');
        $query = <<<SQL
SELECT * 
FROM game g
    WHERE g.date > '$dateStart'
    AND g.date < '$dateEnd'
    AND g.winner_odd IS NOT NULL
    AND g.winner_odd > 1.40
ORDER BY 
      g.winner_moment_form DESC,
      g.percentage DESC,
      g.nb_match_for_teams DESC,
      g.winner_odd DESC
SQL;

        $em = $this->getEntityManager();
        return $em->getConnection()->executeQuery($query)->fetchAll();
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
            ->andWhere($qb->expr()->isNotNull('g.goodResult'))
            ->orderBy('g.id', 'DESC')
            ->setMaxResults(4)
            ->setParameters(['team' => $team])
        ;

        return $qb->getQuery()->getResult();
    }
}