<?php


namespace App\Repository;


use App\Entity\Bet;
use App\Entity\BothTeamsScoreBet;
use App\Entity\Championship;
use App\Entity\Game;
use App\Entity\Team;
use App\Entity\UnderOverBet;
use App\Entity\WinnerBet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

class BetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bet::class);
    }

    public function findBetsOfTheDayOrderByOddAndPercentage()
    {
        $dateStart = (new \DateTime())->format('Y-m-d 00:00:00');
        $dateEnd = (new \DateTime())->format('Y-m-d 23:59:59');
        $query = <<<SQL
SELECT b.* 
FROM bet b
LEFT JOIN game g ON b.game_id = g.id
    WHERE g.date > '$dateStart'
    AND g.date < '$dateEnd'
    AND b.odd IS NOT NULL AND b.odd > 0
ORDER BY 
      b.form DESC,
      b.percentage DESC,
      b.my_odd ASC,
      CASE
      WHEN (b.my_odd - b.odd) > 0 THEN (b.my_odd - b.odd)
      WHEN (b.odd - b.my_odd) > 0 THEN (b.odd - b.my_odd)
      END ASC,
      b.odd DESC
SQL;


        $em = $this->getEntityManager();
        return $em->getConnection()->executeQuery($query)->fetchAll();
    }

    public function findBetsForTeams(Team $team, Bet $bet)
    {
        $qb = $this->createQueryBuilder('b');

        $orStatement = $qb->expr()->orX();
        $orStatement->add('g.homeTeam = :team');
        $orStatement->add('g.awayTeam = :team');

        $qb
            ->leftJoin(Game::class, 'g', Join::WITH, 'g.id = b.game')
            ->leftJoin(Championship::class, 'c', Join::WITH, 'c.id = g.championship')
            ->where($orStatement)
            ->andWhere($qb->expr()->isNotNull('b.goodResult'))
            ->andWhere($qb->expr()->gte('g.date', 'c.startDate'))
            ->setParameter('team', $team->getId())
        ;

        if (UnderOverBet::LESS_TWO_AND_A_HALF === $bet->getType() || UnderOverBet::PLUS_TWO_AND_A_HALF === $bet->getType()) {
            $orBetTypeStatement = $qb->expr()->orX();
            $orBetTypeStatement->add('b.type = :type_under');
            $orBetTypeStatement->add('b.type = :type_over');

            $qb
                ->andWhere($orBetTypeStatement)
                ->setParameter('type_under', UnderOverBet::LESS_TWO_AND_A_HALF)
                ->setParameter('type_over', UnderOverBet::PLUS_TWO_AND_A_HALF)
            ;
        }

        if (UnderOverBet::LESS_THREE_AND_A_HALF === $bet->getType() || UnderOverBet::PLUS_THREE_AND_A_HALF === $bet->getType()) {
            $orBetTypeStatement = $qb->expr()->orX();
            $orBetTypeStatement->add('b.type = :type_under');
            $orBetTypeStatement->add('b.type = :type_over');

            $qb
                ->andWhere($orBetTypeStatement)
                ->setParameter('type_under', UnderOverBet::LESS_THREE_AND_A_HALF)
                ->setParameter('type_over', UnderOverBet::PLUS_THREE_AND_A_HALF)
            ;
        }

        if (WinnerBet::WINNER_TYPE === $bet->getType()) {
            $qb
                ->andWhere('b.type = :type')
                ->setParameter('type', WinnerBet::WINNER_TYPE)
            ;
        }

        if (BothTeamsScoreBet::BOTH_TEAMS_GOAL_TYPE === $bet->getType()) {
            $qb
                ->andWhere('b.type = :type')
                ->setParameter('type', BothTeamsScoreBet::BOTH_TEAMS_GOAL_TYPE)
            ;
        }

        return $qb->getQuery()->getResult();
    }

}