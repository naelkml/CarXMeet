<?php

namespace App\Repository;

use App\Entity\Friendship;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Friendship>
 */
class FriendshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friendship::class);
    }

    public function findAcceptedBetween(User $a, User $b): ?Friendship
    {
        $qb = $this->createQueryBuilder('f');
    
        return $qb
            ->andWhere('f.status = :status')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        'f.requesterId = :a',
                        'f.receiverId = :b'
                    ),
                    $qb->expr()->andX(
                        'f.requesterId = :b',
                        'f.receiverId = :a'
                    )
                )
            )
            ->setParameter('status', 'accepted')
            ->setParameter('a', $a)
            ->setParameter('b', $b)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function areFriends(User $a, User $b): bool
    {
        if ($a->getId() === null || $b->getId() === null) {
            return false;
        }
        if ($a->getId() === $b->getId()) {
            return true;
        }

        return $this->findAcceptedBetween($a, $b) !== null;
    }

    /**
     * @return int[]
     */
    public function getFriendIds(User $user): array
    {
        $rows = $this->createQueryBuilder('f')
            ->select('IDENTITY(f.requesterId) AS requesterId, IDENTITY(f.receiverId) AS receiverId')
            ->andWhere('f.status = :status')
            ->andWhere('f.requesterId = :u OR f.receiverId = :u')
            ->setParameter('status', 'accepted')
            ->setParameter('u', $user)
            ->getQuery()
            ->getArrayResult();

        $ids = [];
        $myId = (int) $user->getId();
        foreach ($rows as $r) {
            $a = (int) ($r['requesterId'] ?? 0);
            $b = (int) ($r['receiverId'] ?? 0);
            $other = $a === $myId ? $b : $a;
            if ($other > 0) {
                $ids[$other] = true;
            }
        }

        return array_map('intval', array_keys($ids));
    }

    /**
     * @return User[]
     */
    public function listFriends(User $user): array
    {
        $friendIds = $this->getFriendIds($user);
        if (!$friendIds) {
            return [];
        }

        return $this->getEntityManager()
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->andWhere('u.id IN (:ids)')
            ->setParameter('ids', $friendIds)
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Friendship[] Returns an array of Friendship objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Friendship
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
