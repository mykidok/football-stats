<?php

namespace App\Repository;

use App\Entity\Championship;
use App\Entity\Game;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

class ChampionshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Championship::class);
    }

    public function findChampionshipsWithStatistics()
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select('c.name, c.logo')
            ->addSelect('t.name as teamName')
            ->addSelect('COUNT(g.id) as nbMatch')
            ->addSelect(
                'SUM(
                            CASE WHEN (g.goodResult = 1 AND g.championship = c.id)
                            THEN 1
                            ELSE 0 END
                    ) * 100 /
                    SUM(
                            CASE WHEN (g.championship = c.id)
                            THEN 1
                            ELSE 0 END
                        ) as championshipPercentage'
            )
            ->addSelect(
                'SUM(
                            CASE WHEN (g.homeTeam = t.id OR g.awayTeam = t.id)
                            THEN 1
                            ELSE 0 END
                        ) as teamNbMatch'
            )
            ->addSelect(
                'SUM(
                            CASE WHEN (g.goodResult = 1 AND (g.homeTeam = t.id OR g.awayTeam = t.id))
                            THEN 1
                            ELSE 0 END
                    ) * 100 /
                    SUM(
                            CASE WHEN (g.homeTeam = t.id OR g.awayTeam = t.id)
                            THEN 1
                            ELSE 0 END
                        ) as teamPercentage'
            )
            ->leftJoin(Team::class, 't', Join::WITH, 'c.id = t.championship')
            ->leftJoin(Game::class, 'g', Join::WITH, 'c.id = g.championship')
            ->groupBy('c.id, teamName')
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('teamPercentage', 'DESC')
        ;

        return $qb->getQuery()->getScalarResult();
    }
}