<?php

namespace App\Repository;

use App\Entity\EventRating;
use App\Entity\Events;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventRating>
 */
final class EventRatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventRating::class);
    }

    public function findOneForUser(Events $event, User $user): ?EventRating
    {
        return $this->findOneBy([
            'eventID' => $event,
            'userID' => $user,
        ]);
    }

    public function getAverageForEvent(Events $event): float
    {
        $avg = $this->createQueryBuilder('r')
            ->select('AVG(r.rating)')
            ->andWhere('r.eventID = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getSingleScalarResult();

        return $avg !== null ? (float) $avg : 0.0;
    }
}

