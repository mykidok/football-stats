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

    public function findChampionshipsWithStatistics(string $goodResult, string $momentForm): array
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select('c.name, c.logo')
            ->addSelect('t.name as teamName')
            ->addSelect(
                "SUM(
                            CASE WHEN (g.$goodResult IS NOT NULL AND g.championship = c.id)
                            THEN 1
                            ELSE 0 END
                    ) as nbMatch"
            )
            ->addSelect(
                "SUM(
                            CASE WHEN (g.$goodResult = 1 AND g.championship = c.id)
                            THEN 1
                            ELSE 0 END
                    ) * 100 /
                    SUM(
                            CASE WHEN (g.championship = c.id AND g.$goodResult IS NOT NULL)
                            THEN 1
                            ELSE 0 END
                        ) as championshipPercentage"
            )
            ->addSelect(
                "SUM(
                            CASE WHEN (g.$goodResult = 1 AND g.championship = c.id  AND g.$momentForm = 1)
                            THEN 1
                            ELSE 0 END
                    ) * 100 /
                    SUM(
                            CASE WHEN (g.championship = c.id AND g.$goodResult IS NOT NULL AND g.$momentForm = 1)
                            THEN 1
                            ELSE 0 END
                        ) as championshipPercentageWithForm"
            )
            ->addSelect(
                "SUM(
                            CASE WHEN ((g.homeTeam = t.id OR g.awayTeam = t.id) AND g.$goodResult IS NOT NULL)
                            THEN 1
                            ELSE 0 END
                        ) as teamNbMatch"
            )
            ->addSelect(
                "SUM(
                            CASE WHEN (g.$goodResult = 1 AND (g.homeTeam = t.id OR g.awayTeam = t.id))
                            THEN 1
                            ELSE 0 END
                    ) * 100 /
                    SUM(
                            CASE WHEN ((g.homeTeam = t.id OR g.awayTeam = t.id) AND g.$goodResult IS NOT NULL)
                            THEN 1
                            ELSE 0 END
                        ) as teamPercentage"
            )
            ->leftJoin(Team::class, 't', Join::WITH, 'c.id = t.championship')
            ->leftJoin(Game::class, 'g', Join::WITH, 'c.id = g.championship')
            ->groupBy('c.id, teamName')
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('teamPercentage', 'DESC')
            ->addOrderBy('teamNbMatch', 'DESC')
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function findTeamsWithStatistics(): array
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select('t.name as teamName')
            ->addSelect(
                'SUM(
                            CASE WHEN ((g.homeTeam = t.id OR g.awayTeam = t.id) AND g.goodResult IS NOT NULL)
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
                            CASE WHEN ((g.homeTeam = t.id OR g.awayTeam = t.id) AND g.goodResult IS NOT NULL)
                            THEN 1
                            ELSE 0 END
                        ) as teamPercentage'
            )
            ->addSelect(
                'SUM(
                            CASE WHEN (g.winnerResult = 1 AND (g.homeTeam = t.id OR g.awayTeam = t.id))
                            THEN 1
                            ELSE 0 END
                    ) * 100 /
                    SUM(
                            CASE WHEN ((g.homeTeam = t.id OR g.awayTeam = t.id) AND g.winnerResult IS NOT NULL)
                            THEN 1
                            ELSE 0 END
                        ) as teamWinnerPercentage'
            )
            ->leftJoin(Team::class, 't', Join::WITH, 'c.id = t.championship')
            ->leftJoin(Game::class, 'g', Join::WITH, 'c.id = g.championship')
            ->groupBy('c.id, teamName')
            ->addOrderBy('teamPercentage', 'DESC')
            ->addOrderBy('teamNbMatch', 'DESC')
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function championshipsWithGamesWithoutOdds()
    {
        $dateStart = (new \DateTime())->format('Y-m-d 00:00:00');
        $dateEnd = (new \DateTime())->format('Y-m-d 23:59:59');
        $query = <<<SQL
SELECT * 
FROM championship c
    WHERE (SELECT COUNT(g.id) FROM game g 
                        WHERE g.date < '$dateEnd'
                        AND g.date > '$dateStart'
                        AND g.championship_id = c.id
                        AND (odd IS NULL OR winner_odd IS NULL) > 0
SQL;

        $em = $this->getEntityManager();
        return $em->getConnection()->executeQuery($query)->fetchAll();
    }
}