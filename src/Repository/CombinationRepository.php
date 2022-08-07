<?php

namespace App\Repository;

use App\Entity\Combination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CombinationRepository extends ServiceEntityRepository
{
    /**
     * @var string
     */
    public const COMBINATION_START_DATE = '2022-07-01';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Combination::class);
    }

    public function findCombinationOfTheDay(\DateTime $date)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->where('c.date > :date_start')
            ->andWhere('c.date < :date_end')
            ->setParameters([
                'date_start' => $date->format('Y-m-d 00:00:00'),
                'date_end' => $date->format('Y-m-d 23:59:59'),
            ])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findLastFiveCombinations()
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->where($qb->expr()->isNotNull('c.success'))
            ->andWhere($qb->expr()->gte('c.date', ':date'))
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(5)
            ->setParameter('date', new \DateTime(self::COMBINATION_START_DATE))
        ;

        return $qb->getQuery()->getResult();
    }

    public function findCombinationFinished()
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->where($qb->expr()->isNotNull('c.success'))
            ->andWhere($qb->expr()->gte('c.date', ':date'))
            ->orderBy('c.id', 'ASC')
            ->setParameter('date', new \DateTime(self::COMBINATION_START_DATE))
        ;

        return $qb->getQuery()->getResult();
    }
}