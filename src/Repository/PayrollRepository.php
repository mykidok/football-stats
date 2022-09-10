<?php

namespace App\Repository;

use App\Entity\Payroll;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class PayrollRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payroll::class);
    }

    public function findPayrollOfDay(\DateTime $date)
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->where('p.date > :date_start')
            ->andWhere('p.date < :date_end')
            ->setParameters([
                'date_start' => $date->format('Y-m-d 00:00:00'),
                'date_end' => $date->format('Y-m-d 23:59:59'),
            ])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findLastPayroll()
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getResult();
    }
}