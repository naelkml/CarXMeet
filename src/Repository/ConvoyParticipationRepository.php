<?php

/*
 * ============================================================
 *  QU'EST-CE QU'UN REPOSITORY ?
 * ============================================================
 *
 * Un Repository est une classe dédiée à la communication avec
 * la base de données pour un type d'objet précis.
 * Pense-y comme à un guichetier spécialisé : tu lui poses une
 * question ("qui participe à ce convoi ?") et il va chercher
 * la réponse dans la base de données pour toi.
 *
 * Ce fichier gère la table "convoy_participation", qui enregistre
 * quels utilisateurs ont rejoint quels convois (une sorte de
 * liste d'inscrits pour chaque convoi).
 *
 * Méthodes héritées automatiquement :
 *   - find($id)          → retrouve une participation par son ID
 *   - findAll()          → retourne toutes les participations
 *   - findBy([...])      → filtre les participations selon des critères
 *   - findOneBy([...])   → retourne une seule participation selon des critères
 * ============================================================
 */

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
    /*
     * Constructeur : configure ce repository pour qu'il travaille
     * avec l'entité ConvoyParticipation et sa table en base de données.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConvoyParticipation::class);
    }

    /*
     * findOneForUser — Vérifie si un utilisateur participe déjà à un convoi donné.
     *
     * Cette méthode cherche UNE SEULE participation qui correspond
     * à la fois au convoi ET à l'utilisateur passés en paramètres.
     *
     * C'est utile pour éviter qu'un utilisateur s'inscrive deux fois
     * au même convoi, ou pour savoir si un bouton "Je participe" doit
     * s'afficher ou non.
     *
     * Paramètres :
     *   - $convoy : l'objet Convoy (le convoi concerné)
     *   - $user   : l'objet User (l'utilisateur concerné)
     *
     * Retourne :
     *   - Un objet ConvoyParticipation si la participation existe
     *   - null si l'utilisateur ne participe pas à ce convoi
     *
     * Le "?" devant ConvoyParticipation signifie que la valeur peut
     * être null (c'est ce qu'on appelle un type "nullable" en PHP).
     */
    public function findOneForUser(Convoy $convoy, User $user): ?ConvoyParticipation
    {
        return $this->findOneBy([
            'convoyID' => $convoy,
            'userID' => $user,
        ]);
    }

    /*
     * findMembersForConvoy — Récupère tous les participants d'un convoi.
     *
     * Cette méthode construit une requête plus complexe pour récupérer
     * toutes les participations liées à un convoi précis, triées par
     * ordre d'inscription (du premier inscrit au dernier).
     *
     * Fonctionnement pas à pas :
     *   1. createQueryBuilder('cp') → démarre la construction d'une
     *      requête SQL. 'cp' est un alias pour la table ConvoyParticipation
     *      (comme un surnom pour raccourcir l'écriture dans la requête).
     *
     *   2. leftJoin('cp.userID', 'u')->addSelect('u') → fait une
     *      "jointure". La base de données contient deux tables séparées :
     *      une pour les participations et une pour les utilisateurs.
     *      La jointure permet de récupérer les données des DEUX tables
     *      en une seule requête. Ici on récupère les infos de l'utilisateur
     *      en même temps que sa participation (évite de faire une 2e requête).
     *
     *   3. andWhere('cp.convoyID = :c') → filtre : on ne veut que les
     *      participations du convoi demandé (équivaut à un WHERE en SQL).
     *
     *   4. setParameter('c', $convoy) → remplace le ":c" par la vraie
     *      valeur du convoi. Cela protège contre les injections SQL
     *      (une attaque où quelqu'un insère du code malveillant dans
     *      une requête).
     *
     *   5. orderBy('cp.joinedAt', 'ASC') → trie par date d'inscription
     *      croissante (ASC = Ascending = du plus ancien au plus récent).
     *
     *   6. getQuery()->getResult() → exécute vraiment la requête et
     *      retourne les résultats sous forme de tableau d'objets PHP.
     *
     * Retourne :
     *   - Un tableau (array) d'objets ConvoyParticipation, chacun ayant
     *     déjà les données de l'utilisateur chargées.
     *
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
