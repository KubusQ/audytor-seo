<?php

namespace App\Repository;

use App\Entity\AuditsData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuditsData>
 *
 * @method AuditsData|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuditsData|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuditsData[]    findAll()
 * @method AuditsData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuditsDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditsData::class);
    }
    
//    /**
//     * @return AuditsData[] Returns an array of AuditsData objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AuditsData
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
