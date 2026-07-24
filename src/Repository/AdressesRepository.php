<?php

/*
 * ============================================================
 *  QU'EST-CE QU'UN REPOSITORY ?
 * ============================================================
 *
 * Imagine une base de données comme un grand classeur rempli
 * de tiroirs. Chaque tiroir contient des fiches d'un même type
 * (par exemple : toutes les adresses, tous les utilisateurs…).
 *
 * Un "Repository" (dépôt en français) est la classe PHP qui
 * sait comment aller chercher, filtrer et récupérer les fiches
 * d'UN tiroir précis. C'est l'intermédiaire entre ton code PHP
 * et la base de données.
 *
 * Concrètement :
 *   - Tu demandes au Repository : "donne-moi toutes les adresses"
 *   - Il traduit ta demande en requête SQL (le langage de la BDD)
 *   - Il te renvoie les résultats sous forme d'objets PHP
 *
 * Ce fichier gère le tiroir "Adresses" : toutes les opérations
 * liées à la table des adresses passent par ici.
 *
 * Doctrine est le composant Symfony qui fait le lien entre les
 * objets PHP (entités) et les tables de la base de données.
 * ServiceEntityRepository est la classe de base fournie par
 * Doctrine qui offre déjà des méthodes prêtes à l'emploi comme :
 *   - find($id)          → cherche une adresse par son identifiant
 *   - findAll()          → retourne TOUTES les adresses
 *   - findBy([...])      → retourne les adresses qui correspondent à des critères
 *   - findOneBy([...])   → retourne UNE SEULE adresse correspondant à des critères
 * ============================================================
 */

namespace App\Repository;

use App\Entity\Adresses;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Adresses>
 */
class AdressesRepository extends ServiceEntityRepository
{
    /*
     * Le constructeur est la méthode appelée automatiquement
     * quand on crée un objet de cette classe.
     *
     * $registry est le "registre" de Doctrine : il connaît
     * toutes les connexions à la base de données et toutes
     * les entités de l'application.
     *
     * On appelle parent::__construct() pour dire à la classe
     * mère (ServiceEntityRepository) qu'elle doit travailler
     * avec la table correspondant à l'entité "Adresses".
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adresses::class);
    }

    //    /**
    //     * @return Adresses[] Returns an array of Adresses objects
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

    //    public function findOneBySomeField($value): ?Adresses
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
