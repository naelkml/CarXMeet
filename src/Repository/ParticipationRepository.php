<?php

namespace App\Repository;

use App\Entity\Events;
use App\Entity\Participation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participation>
 */
class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }

    public function findOneForUser(Events $event, User $user): ?Participation
    {
        return $this->findOneBy([
            'eventID' => $event,
            'userID' => $user,
        ]);
    }

    /**
     * @return Participation[]
     */
    public function findParticipantsForEvent(Events $event): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.userID', 'u')->addSelect('u')
            ->andWhere('p.eventID = :e')
            ->setParameter('e', $event)
            ->orderBy('p.joinedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Events[]
     */
    public function findEventsForUser(User $user): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.eventID', 'e')
            ->addSelect('e')
            ->andWhere('p.userID = :u')
            ->setParameter('u', $user)
            ->orderBy('p.joinedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Participation[] Returns an array of Participation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Participation
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
