<?php


namespace App\Repository;


use App\Entity\Bet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

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
SELECT * 
FROM bet b
LEFT JOIN game g ON b.game_id = g.id
    WHERE g.date > '$dateStart'
    AND g.date < '$dateEnd'
    AND b.odd IS NOT NULL
    AND b.odd > 1.39
ORDER BY 
      b.form DESC,
      CASE
      WHEN (b.my_odd - b.odd) > 0 THEN (b.my_odd - b.odd)
      WHEN (b.odd - b.my_odd) > 0 THEN (b.odd - b.my_odd)
      END ASC,
      b.percentage DESC,
      g.nb_match_for_teams DESC,
      b.odd DESC,
      b.my_odd DESC
SQL;


        $em = $this->getEntityManager();
        return $em->getConnection()->executeQuery($query)->fetchAll();
    }

}