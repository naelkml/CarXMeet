<?php

namespace App\Repository;

use App\Entity\Convoy;
use App\Entity\ConvoyParticipation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConvoyParticipation>
 */
final class ConvoyParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConvoyParticipation::class);
    }

    public function findOneForUser(Convoy $convoy, User $user): ?ConvoyParticipation
    {
        return $this->findOneBy([
            'convoyID' => $convoy,
            'userID' => $user,
        ]);
    }

    /**
     * @return ConvoyParticipation[]
     */
    public function findMembersForConvoy(Convoy $convoy): array
    {
        return $this->createQueryBuilder('cp')
            ->leftJoin('cp.userID', 'u')->addSelect('u')
            ->andWhere('cp.convoyID = :c')
            ->setParameter('c', $convoy)
            ->orderBy('cp.joinedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

