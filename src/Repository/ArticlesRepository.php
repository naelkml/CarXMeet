<?php

/*
 * ============================================================
 *  QU'EST-CE QU'UN REPOSITORY ?
 * ============================================================
 *
 * Un Repository est une classe qui joue le rôle de
 * "bibliothécaire" pour un type d'objet précis.
 * Il sait comment chercher, trier et récupérer des données
 * depuis la base de données pour te les livrer sous forme
 * d'objets PHP prêts à utiliser.
 *
 * Ce fichier gère les articles de l'application.
 * Toutes les requêtes liées à la table "articles" passent ici.
 *
 * Méthodes héritées automatiquement (gratuites, sans coder) :
 *   - find($id)          → retrouve un article par son ID
 *   - findAll()          → retourne tous les articles
 *   - findBy([...])      → filtre les articles selon des critères
 *   - findOneBy([...])   → retourne un seul article selon des critères
 *   - count([...])       → compte le nombre d'articles
 * ============================================================
 */

namespace App\Repository;

use App\Entity\Articles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Articles>
 */
class ArticlesRepository extends ServiceEntityRepository
{
    /*
     * Constructeur : initialise le repository en lui indiquant
     * qu'il doit travailler avec l'entité "Articles".
     * Le $registry contient toutes les informations de connexion
     * à la base de données.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Articles::class);
    }

    //    /**
    //     * @return Articles[] Returns an array of Articles objects
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

    //    public function findOneBySomeField($value): ?Articles
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
