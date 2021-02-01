<?php

namespace App\Repository;

use App\Entity\Championship;
use App\Entity\Game;
use App\Entity\Team;
use App\Entity\UnderOverBet;
use App\Entity\WinnerBet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

class ChampionshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Championship::class);
    }

    public function findChampionshipsWithStatistics(string $type): array
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select('c.name, c.logo')
            ->addSelect('t.name as teamName')
            ->addSelect(
                "SUM(
                            CASE WHEN (b.goodResult IS NOT NULL AND g.championship = c.id)
                            THEN 1
                            ELSE 0 END
                    ) as nbMatch"
            )
            ->addSelect(
                "SUM(
                            CASE WHEN (b.goodResult = 1 AND g.championship = c.id)
                            THEN 1
                            ELSE 0 END
                    ) * 100 /
                    SUM(
                            CASE WHEN (g.championship = c.id AND b.goodResult IS NOT NULL)
                            THEN 1
                            ELSE 0 END
                        ) as championshipPercentage"
            )
            ->addSelect(
                "SUM(
                            CASE WHEN (b.goodResult = 1 AND g.championship = c.id  AND b.form = 1)
                            THEN 1
                            ELSE 0 END
                    ) * 100 /
                    SUM(
                            CASE WHEN (g.championship = c.id AND b.goodResult IS NOT NULL AND b.form = 1)
                            THEN 1
                            ELSE 0 END
                        ) as championshipPercentageWithForm"
            )
            ->addSelect(
                "SUM(
                            CASE WHEN ((g.homeTeam = t.id OR g.awayTeam = t.id) AND b.goodResult IS NOT NULL)
                            THEN 1
                            ELSE 0 END
                        ) as teamNbMatch"
            )
            ->addSelect(
                "SUM(
                            CASE WHEN (b.goodResult = 1 AND (g.homeTeam = t.id OR g.awayTeam = t.id))
                            THEN 1
                            ELSE 0 END
                    ) * 100 /
                    SUM(
                            CASE WHEN ((g.homeTeam = t.id OR g.awayTeam = t.id) AND b.goodResult IS NOT NULL)
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

        $betAlias = 'b';
        if ((string)UnderOverBet::LIMIT_2_5 === $type || (string)UnderOverBet::LIMIT_3_5 ===  $type) {
            $qb->leftJoin(UnderOverBet::class, $betAlias, Join::WITH, sprintf("%s.game = g.id AND (%s.type = '+ %s' OR %s.type = '- %s')", $betAlias, $betAlias, $type, $betAlias, $type));
        } else {
            $qb->leftJoin(WinnerBet::class, $betAlias, Join::WITH, sprintf('%s.game = g.id', $betAlias));
        }


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
                            CASE WHEN (uob2.goodResult = 1 AND (g.homeTeam = t.id OR g.awayTeam = t.id))
                            THEN 1
                            ELSE 0 END
                    ) * 100 /
                    SUM(
                            CASE WHEN ((g.homeTeam = t.id OR g.awayTeam = t.id) AND uob2.goodResult IS NOT NULL)
                            THEN 1
                            ELSE 0 END
                        ) as teamPercentageTwoHalf'
            )
            ->addSelect(
                'SUM(
                            CASE WHEN (uob3.goodResult = 1 AND (g.homeTeam = t.id OR g.awayTeam = t.id))
                            THEN 1
                            ELSE 0 END
                    ) * 100 /
                    SUM(
                            CASE WHEN ((g.homeTeam = t.id OR g.awayTeam = t.id) AND uob3.goodResult IS NOT NULL)
                            THEN 1
                            ELSE 0 END
                        ) as teamPercentageThreeHalf'
            )
            ->addSelect(
                'SUM(
                            CASE WHEN (wb.goodResult = 1 AND (g.homeTeam = t.id OR g.awayTeam = t.id))
                            THEN 1
                            ELSE 0 END
                    ) * 100 /
                    SUM(
                            CASE WHEN ((g.homeTeam = t.id OR g.awayTeam = t.id) AND wb.goodResult IS NOT NULL)
                            THEN 1
                            ELSE 0 END
                        ) as teamWinnerPercentage'
            )
            ->leftJoin(Game::class, 'g', Join::WITH, 'c.id = g.championship')
            ->leftJoin(UnderOverBet::class, 'uob2', Join::WITH, "uob2.game = g.id AND (uob2.type = '+ 2.5' OR uob2.type = '- 2.5')")
            ->leftJoin(UnderOverBet::class, 'uob3', Join::WITH, "uob3.game = g.id AND (uob3.type = '+ 3.5' OR uob3.type = '- 3.5')")
            ->leftJoin(WinnerBet::class, 'wb', Join::WITH, 'wb.game = g.id')
            ->leftJoin(Team::class, 't', Join::WITH, 'c.id = t.championship')
            ->groupBy('c.id, teamName')
            ->addOrderBy('teamPercentageTwoHalf', 'DESC')
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
    WHERE (SELECT COUNT(b.id) FROM bet b
                LEFT JOIN game g ON g.id = b.game_id
                WHERE g.date < '$dateEnd'
                AND g.date > '$dateStart'
                AND g.championship_id = c.id
                AND (b.odd IS NULL OR b.odd = 0) ) > 0
SQL;

        $em = $this->getEntityManager();
        return $em->getConnection()->executeQuery($query)->fetchAll();
    }
}