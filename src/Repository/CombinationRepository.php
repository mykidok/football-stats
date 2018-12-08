<?php

namespace App\Repository;

use App\Entity\Combination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CombinationRepository extends ServiceEntityRepository
{
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
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(5)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findCombinationFinished()
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->where($qb->expr()->isNotNull('c.success'))
            ->orderBy('c.id', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }
}