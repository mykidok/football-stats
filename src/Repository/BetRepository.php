<?php


namespace App\Repository;


use App\Entity\Bet;
use App\Entity\Game;
use App\Entity\Team;
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
    AND b.odd IS NOT NULL
ORDER BY 
      b.form DESC,
      b.percentage DESC,
      CASE
      WHEN (b.my_odd - b.odd) > 0 THEN (b.my_odd - b.odd)
      WHEN (b.odd - b.my_odd) > 0 THEN (b.odd - b.my_odd)
      END ASC,
      g.nb_match_for_teams DESC,
      b.odd DESC,
      b.my_odd DESC
SQL;


        $em = $this->getEntityManager();
        return $em->getConnection()->executeQuery($query)->fetchAll();
    }

    public function findBetsForTeams(Team $team, string $type)
    {
        $qb = $this->createQueryBuilder('b');

        $orStatement = $qb->expr()->orX();
        $orStatement->add('g.homeTeam = :team');
        $orStatement->add('g.awayTeam = :team');

        $qb
            ->leftJoin(Game::class, 'g', Join::WITH, 'g.id = b.game')
            ->where($orStatement)
            ->andWhere('b.type = :type')
            ->andWhere($qb->expr()->isNotNull('b.goodResult'))
            ->setParameter('team', $team->getId())
            ->setParameter('type', $type)
        ;

        return $qb->getQuery()->getResult();
    }

}