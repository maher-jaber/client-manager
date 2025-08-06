<?php

namespace App\Repository;

use App\Entity\ClientActionLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClientActionLog>
 */
class ClientActionLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientActionLog::class);
    }

    //    /**
    //     * @return ClientActionLog[] Returns an array of ClientActionLog objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ClientActionLog
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findAllLogsQuery()
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.clients', 'c')
            ->leftJoin('l.actions', 'a')
            ->addSelect('c', 'a')
            ->orderBy('l.performedAt', 'DESC');
    }
}
