<?php

namespace App\Repository;

use App\Entity\Events;
use App\Entity\Region;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Events>
 */
class EventsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Events::class);
    }

    /**
     * @return Events[]
     */
    public function findForIndex(?Region $region, string $sort): array
    {
        $allowed = [
            'date_asc' => ['e.Date', 'ASC'],
            'date_desc' => ['e.Date', 'DESC'],
            'type_asc' => ['e.type', 'ASC'],
            'type_desc' => ['e.type', 'DESC'],
            'rating_desc' => ['e.ratingAverage', 'DESC'],
            'rating_asc' => ['e.ratingAverage', 'ASC'],
            'created_desc' => ['e.createdAt', 'DESC'],
            'created_asc' => ['e.createdAt', 'ASC'],
        ];

        $order = $allowed[$sort] ?? $allowed['date_asc'];

        $qb = $this->createQueryBuilder('e');
        if ($region instanceof Region) {
            $qb->andWhere('e.regionID = :region')->setParameter('region', $region);
        }

        // Stable ordering for identical values.
        $qb->orderBy($order[0], $order[1])->addOrderBy('e.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Events[] Returns an array of Events objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Events
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
