<?php

namespace App\Repository;

use App\Entity\BothTeamsScoreBet;
use App\Entity\Championship;
use App\Entity\Game;
use App\Entity\Team;
use App\Entity\UnderOverBet;
use App\Entity\WinnerBet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use function Doctrine\ORM\QueryBuilder;

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
                            CASE WHEN (b.goodResult IS NOT NULL AND g.championship = c.id AND b.form = 1)
                            THEN 1
                            ELSE 0 END
                    ) as nbMatchWithForm"
            )
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
                            CASE WHEN (b.goodResult = 1 AND g.championship = c.id AND b.form = 1)
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
                            CASE WHEN (g.homeTeam = t.id AND b.goodResult IS NOT NULL)
                            THEN 1
                            ELSE 0 END
                        ) as teamNbMatchHome"
            )
            ->addSelect(
                "SUM(
                            CASE WHEN (g.awayTeam = t.id AND b.goodResult IS NOT NULL)
                            THEN 1
                            ELSE 0 END
                        ) as teamNbMatchAway"
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
            ->addSelect(
                "SUM(
                            CASE WHEN (b.goodResult = 1 AND g.homeTeam = t.id)
                            THEN 1
                            ELSE 0 END
                    ) * 100 /
                    SUM(
                            CASE WHEN (g.homeTeam = t.id AND b.goodResult IS NOT NULL)
                            THEN 1
                            ELSE 0 END
                        ) as teamHomePercentage"
            )
            ->addSelect(
                "SUM(
                            CASE WHEN (b.goodResult = 1 AND g.awayTeam = t.id)
                            THEN 1
                            ELSE 0 END
                    ) * 100 /
                    SUM(
                            CASE WHEN (g.awayTeam = t.id AND b.goodResult IS NOT NULL)
                            THEN 1
                            ELSE 0 END
                        ) as teamAwayPercentage"
            )
            ->leftJoin(Team::class, 't', Join::WITH, 'c.id = t.championship')
            ->leftJoin(Game::class, 'g', Join::WITH, 'c.id = g.championship')
            ->groupBy('c.id, teamName')
            ->where($qb->expr()->gte('g.date', 'c.startDate'))
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('teamPercentage', 'DESC')
        ;

        $betAlias = 'b';
        if ((string)UnderOverBet::LIMIT_2_5 === $type || (string)UnderOverBet::LIMIT_3_5 ===  $type) {
            $qb->leftJoin(UnderOverBet::class, $betAlias, Join::WITH, sprintf("%s.game = g.id AND (%s.type = '+ %s' OR %s.type = '- %s')", $betAlias, $betAlias, $type, $betAlias, $type));
        } elseif (WinnerBet::WINNER_TYPE === $type) {
            $qb->leftJoin(WinnerBet::class, $betAlias, Join::WITH, sprintf('%s.game = g.id', $betAlias));
        } elseif (BothTeamsScoreBet::BOTH_TEAMS_GOAL_TYPE === $type) {
            $qb->leftJoin(BothTeamsScoreBet::class, $betAlias, Join::WITH, sprintf('%s.game = g.id', $betAlias));
        }


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