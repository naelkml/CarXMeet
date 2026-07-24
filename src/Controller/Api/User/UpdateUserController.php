<?php

/**
 * Fichier : UpdateUserController.php
 *
 * Ce fichier contient le contrôleur chargé de METTRE À JOUR le profil d'un utilisateur via l'API.
 *
 * Un utilisateur peut modifier ses informations personnelles : prénom, nom, pseudo, email,
 * téléphone, réseaux sociaux (Snapchat, Instagram, Twitter, TikTok) et sa photo de profil.
 *
 * Règle importante : un utilisateur ne peut modifier QUE son propre profil.
 * Il ne peut pas modifier le profil d'un autre utilisateur.
 *
 * Ce contrôleur gère aussi deux contraintes d'unicité importantes :
 * - Le pseudo (username) doit être unique parmi tous les utilisateurs.
 * - L'adresse email doit être unique parmi tous les utilisateurs.
 */

namespace App\Controller\Api\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Api\ApiJsonResponder;
use App\Service\Api\FormDataHelper;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * #[AsController] : indique à Symfony que c'est un contrôleur d'API.
 * "final" : cette classe ne peut pas être étendue.
 */
#[AsController]
final class UpdateUserController extends AbstractController
{
    /**
     * Constructeur : injection automatique des dépendances par Symfony.
     *
     * @param EntityManagerInterface $em             Gestionnaire Doctrine pour la base de données.
     * @param UserRepository         $userRepository Repository dédié aux requêtes sur les utilisateurs.
     * @param ApiJsonResponder        $responder      Service de réponses JSON.
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly ApiJsonResponder $responder,
    ) {
    }

    /**
     * Point d'entrée du contrôleur.
     *
     * API Platform injecte automatiquement l'objet User cible à partir de l'identifiant dans l'URL.
     * Par exemple, PATCH /api/users/5 injecte l'objet User dont l'id est 5.
     *
     * Étapes du traitement :
     * 1. Vérifier que l'utilisateur est connecté (ROLE_USER).
     * 2. Vérifier que l'utilisateur connecté est bien la même personne que celle à modifier.
     * 3. Sauvegarder les valeurs originales du pseudo et de l'email pour détecter les changements.
     * 4. Mettre à jour les champs présents dans la requête.
     * 5. Gérer l'upload d'une nouvelle photo de profil.
     * 6. Vérifier l'unicité du nouveau pseudo si celui-ci a changé.
     * 7. Vérifier l'unicité du nouvel email si celui-ci a changé.
     * 8. Sauvegarder en base de données en gérant les éventuelles violations d'unicité.
     * 9. Retourner l'utilisateur mis à jour au format JSON.
     *
     * @param User    $user    L'utilisateur cible (résolu par API Platform depuis l'URL).
     * @param Request $request La requête HTTP contenant les données de mise à jour.
     * @return Response         La réponse JSON avec l'utilisateur mis à jour.
     */
    public function __invoke(User $user, Request $request): Response
    {
        // Vérifie que l'utilisateur est bien connecté.
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Récupère l'utilisateur actuellement connecté.
        $current = $this->getUser();
        // Vérifie deux choses :
        // 1. $current est bien une instance de notre entité User (et pas juste une interface).
        // 2. L'ID de l'utilisateur connecté est le même que celui qu'on veut modifier.
        // Si l'une des deux conditions échoue, on refuse l'accès (403).
        if (!$current instanceof User || $current->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException('Tu ne peux modifier que ton propre profil.');
        }

        // On mémorise les valeurs originales du pseudo et de l'email avant modification.
        // Cela permettra de savoir plus tard si ces valeurs ont changé.
        // (string) : cast explicite en chaîne au cas où getUsername()/getEmail() retourne null.
        $originalUsername = (string) $user->getUsername();
        $originalEmail = (string) $user->getEmail();

        // Tableau des champs que l'on peut modifier via cette API.
        // On itère dessus pour éviter de répéter le même code pour chaque champ.
        foreach (['firstName', 'lastName', 'username', 'email', 'phone', 'snapchat', 'instagram', 'twitter', 'tiktok'] as $field) {
            // Si le champ n'est pas du tout présent dans la requête, on le saute (mise à jour partielle).
            if (!$request->request->has($field)) {
                continue;
            }
            // Récupère la valeur nettoyée du champ.
            $value = FormDataHelper::getString($request, $field);
            // match() est une structure de contrôle PHP moderne (comme un switch, mais plus strict).
            // Selon le nom du champ, on appelle le setter correspondant sur l'entité User.
            match ($field) {
                'firstName'  => $user->setFirstName($value ?? ''),
                'lastName'   => $user->setLastName($value ?? ''),
                // Pour le pseudo et l'email, si la valeur est vide, on garde l'ancienne valeur.
                'username'   => $user->setUsername($value ?? $originalUsername),
                'email'      => $user->setEmail($value ?? $originalEmail),
                'phone'      => $user->setPhone($value ?? ''),
                // Les réseaux sociaux sont optionnels : on accepte null.
                'snapchat'   => $user->setSnapchat($value),
                'instagram'  => $user->setInstagram($value),
                'twitter'    => $user->setTwitter($value),
                'tiktok'     => $user->setTiktok($value),
            };
        }

        // Gestion de la mise à jour de la photo de profil.
        $profilePhoto = FormDataHelper::getUploadedFiles($request, 'profilePhoto')[0] ?? null;
        if ($profilePhoto) {
            // Lit les données binaires de l'image et les stocke en base de données.
            $user->setProfilePhoto(file_get_contents($profilePhoto->getPathname()));
        }

        // Vérification de l'unicité du nouveau pseudo (si celui-ci a été modifié).
        $newUsername = (string) $user->getUsername();
        if ($newUsername !== $originalUsername) {
            // Cherche un autre utilisateur ayant déjà ce pseudo.
            $existing = $this->userRepository->findOneBy(['username' => $newUsername]);
            // Si un utilisateur différent de celui qu'on modifie possède déjà ce pseudo, on refuse.
            if ($existing && $existing->getId() !== $user->getId()) {
                throw new BadRequestHttpException('Ce nom d\'utilisateur est déjà utilisé.');
            }
        }

        // Vérification de l'unicité du nouvel email (si celui-ci a été modifié).
        $newEmail = (string) $user->getEmail();
        if ($newEmail !== $originalEmail) {
            $existing = $this->userRepository->findOneBy(['email' => $newEmail]);
            if ($existing && $existing->getId() !== $user->getId()) {
                throw new BadRequestHttpException('Cet email est déjà utilisé.');
            }
        }

        try {
            // Enregistre les modifications en base de données (requête SQL UPDATE).
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            // Cas où deux requêtes simultanées ont passé les vérifications ci-dessus en même temps.
            // La base de données elle-même rejette la violation de contrainte d'unicité.
            // On attrape cette exception et on renvoie un message d'erreur clair.
            throw new BadRequestHttpException('Email ou nom d\'utilisateur déjà utilisé.');
        }

        // Retourne l'utilisateur mis à jour au format JSON avec le code 200 OK.
        return $this->responder->item($user, Response::HTTP_OK, ['user:read']);
    }
}
