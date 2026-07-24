<?php

/**
 * =============================================================================
 * CONTRÔLEUR D'INSCRIPTION VIA API - ApiRegistrationController
 * =============================================================================
 *
 * CE QUE FAIT CE CONTRÔLEUR :
 * Ce contrôleur gère l'inscription des nouveaux utilisateurs via une API REST.
 * Contrairement à l'inscription classique (via formulaire HTML), cette version
 * reçoit des données au format JSON (ex : depuis une application mobile) et
 * répond également en JSON.
 *
 * QU'EST-CE QU'UNE API REST ?
 * Une API (Application Programming Interface) REST est un système qui permet
 * à deux applications de communiquer. Ici, une application externe (ex: mobile)
 * envoie des données en JSON et reçoit une réponse en JSON. Il n'y a pas de
 * page HTML, tout est du texte structuré.
 *
 * FONCTIONNALITÉS :
 *  - Valider les données d'inscription (prénom, nom, email, mot de passe, etc.)
 *  - Vérifier que le mot de passe est suffisamment fort
 *  - Créer le compte utilisateur en base de données
 *  - Envoyer un email de confirmation
 * =============================================================================
 */

namespace App\Controller;

// L'entité User représente un utilisateur dans la base de données
use App\Entity\User;

// UserRepository : permet de chercher des utilisateurs en base de données
use App\Repository\UserRepository;

// EntityManagerInterface : le gestionnaire de base de données de Doctrine.
// Doctrine est l'outil qui traduit les objets PHP en lignes de base de données.
use Doctrine\ORM\EntityManagerInterface;

// TemplatedEmail : permet d'envoyer un email en utilisant un template Twig
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

// AbstractController : la classe de base de tous les contrôleurs Symfony
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// JsonResponse : comme Response, mais spécialisée pour renvoyer du JSON
use Symfony\Component\HttpFoundation\JsonResponse;

// Request : représente la requête HTTP reçue (headers, body, paramètres, etc.)
use Symfony\Component\HttpFoundation\Request;

// MailerInterface : le service Symfony pour envoyer des emails
use Symfony\Component\Mailer\MailerInterface;

// UserPasswordHasherInterface : permet de chiffrer (hacher) les mots de passe
// On ne stocke JAMAIS un mot de passe en clair en base de données !
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Route : attribut pour définir l'URL qui déclenchera cette méthode
use Symfony\Component\Routing\Attribute\Route;

// ValidatorInterface : le service Symfony qui valide les entités selon leurs contraintes
use Symfony\Component\Validator\Validator\ValidatorInterface;

// VerifyEmailHelperInterface : outil du bundle SymfonyCasts pour générer et
// valider des liens de confirmation d'email sécurisés
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * Classe ApiRegistrationController
 *
 * Ce contrôleur expose un point d'accès (endpoint) API pour l'inscription.
 * Il reçoit du JSON, valide les données, crée l'utilisateur et envoie un email.
 */
final class ApiRegistrationController extends AbstractController
{
    /**
     * Méthode register() — Inscription d'un nouvel utilisateur via l'API
     *
     * ROUTE :
     *   - URL     : POST /api/register
     *   - Nom     : api.register
     *   - Méthode : uniquement POST (on envoie des données pour créer un compte)
     *
     * QU'EST-CE QUE POST ?
     * HTTP définit plusieurs "méthodes" (GET, POST, PUT, DELETE...).
     * POST signifie qu'on envoie des données au serveur pour les traiter
     * (ici : les informations d'inscription).
     *
     * PARAMÈTRES (injectés automatiquement par Symfony) :
     * @param Request                      $request         La requête HTTP reçue
     *                                                       (contient le JSON du corps)
     * @param EntityManagerInterface       $manager         Gestionnaire Doctrine pour
     *                                                       sauvegarder en base de données
     * @param UserPasswordHasherInterface  $passwordHasher  Service de chiffrement
     *                                                       des mots de passe
     * @param UserRepository               $userRepository  Pour chercher des utilisateurs
     *                                                       existants
     * @param ValidatorInterface           $validator       Pour valider l'entité User
     * @param VerifyEmailHelperInterface   $verifyEmailHelper Pour générer le lien de
     *                                                        confirmation d'email
     * @param MailerInterface              $mailer          Pour envoyer l'email
     *
     * RETOUR : JsonResponse — une réponse JSON avec un message de succès ou d'erreur
     *
     * QU'EST-CE QUE L'INJECTION DE DÉPENDANCES ?
     * Symfony crée et fournit automatiquement les objets dont cette méthode a besoin
     * (le "manager", le "passwordHasher", etc.) grâce à son système d'injection
     * de dépendances. On n'a pas besoin de créer ces objets manuellement.
     */
    #[Route('/api/register', name: 'api.register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer
    ): JsonResponse {
        // On lit le corps de la requête HTTP et on décode le JSON en tableau PHP.
        // json_decode($json, true) transforme '{"key":"value"}' en ['key' => 'value']
        $data = json_decode($request->getContent(), true);

        // Vérification basique : le JSON doit être valide et être un tableau
        if (!is_array($data)) {
            // On retourne une erreur 400 (Bad Request = mauvaise requête)
            return $this->json(['errors' => ['Données JSON invalides']], 400);
        }

        // Tableau qui va collecter toutes les erreurs de validation
        $errors = [];

        // --- Vérification que les champs obligatoires sont présents et non vides ---
        // empty() retourne true si la valeur est vide, null, ou n'existe pas dans le tableau
        if (empty($data['firstName'])) $errors[] = 'Le prénom est obligatoire';
        if (empty($data['lastName'])) $errors[] = 'Le nom est obligatoire';
        if (empty($data['username'])) $errors[] = 'Le nom d\'utilisateur est obligatoire';
        if (empty($data['phone'])) $errors[] = 'Le téléphone est obligatoire';
        if (empty($data['email'])) $errors[] = 'L\'email est obligatoire';

        //Conditions mot de passe
        if (empty($data['password'])) $errors[] = 'Le mot de passe est obligatoire';
        else {
            // --- Validation de la force du mot de passe ---

            // Le mot de passe doit faire au moins 12 caractères
            if (strlen($data['password']) < 12) {
                $errors[] = 'Le mot de passe doit contenir au moins 12 caractères';
            }

            // preg_match() teste une expression régulière (regex) contre une chaîne.
            // '/[a-z]/' vérifie la présence d'au moins une lettre minuscule
            if (!preg_match('/[a-z]/', ($data['password']))) {
                $errors[] = "Ajoute au moins une lettre minuscule.";
            }

            // '/[A-Z]/' vérifie la présence d'au moins une lettre majuscule
            if (!preg_match('/[A-Z]/', ($data['password']))) {
                $errors[] = "Ajoute au moins une lettre majuscule.";
            }

            // '/[0-9]/' vérifie la présence d'au moins un chiffre
            if (!preg_match('/[0-9]/', ($data['password']))) {
                $errors[] = "Ajoute au moins un chiffre.";
            }

            // '/[^a-zA-Z0-9]/' vérifie la présence d'au moins un caractère
            // qui n'est PAS une lettre ni un chiffre (donc un caractère spécial)
            if (!preg_match('/[^a-zA-Z0-9]/', ($data['password']))) {
                $errors[] = "Ajoute au moins un caractère spécial.";
            }
        }



        // Si le tableau $errors n'est pas vide, on retourne toutes les erreurs
        // avec le code HTTP 422 (Unprocessable Entity = données invalides)
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        // --- Vérification d'unicité du nom d'utilisateur ---
        // findOneBy() cherche un utilisateur par critère dans la base de données
        if ($userRepository->findOneBy(['username' => $data['username']])) {
            return $this->json(['errors' => ['Ce nom d\'utilisateur est déjà pris']], 422);
        }

        // --- Vérification d'unicité de l'adresse email ---
        if ($userRepository->findOneBy(['email' => $data['email']])) {
            return $this->json(['errors' => ['Cette adresse email est déjà utilisée']], 422);
        }

        // --- Création de l'objet utilisateur ---
        // On crée une nouvelle instance de l'entité User (objet PHP vide)
        $user = new User();

        // On remplit chaque propriété avec les données reçues depuis le JSON
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setUsername($data['username']);
        $user->setPhone($data['phone']);
        $user->setEmail($data['email']);

        // Les réseaux sociaux sont optionnels : l'opérateur ?? retourne null
        // si la clé n'existe pas dans le tableau $data
        $user->setSnapchat($data['snapchat'] ?? null);
        $user->setInstagram($data['instagram'] ?? null);
        $user->setTwitter($data['twitter'] ?? null);
        $user->setTiktok($data['tiktok'] ?? null);

        // On CHIFFRE le mot de passe avant de le stocker.
        // hashPassword() transforme "MotDePasse123!" en une chaîne illisible
        // comme "$2y$12$abc..." (algorithme bcrypt). Sécurité indispensable !
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));

        // On enregistre la date et l'heure exacte de création du compte
        // DateTimeImmutable = un objet date qui ne peut pas être modifié après création
        $user->setCreatedAt(new \DateTimeImmutable());

        // Le compte n'est pas encore vérifié (l'utilisateur doit confirmer son email)
        $user->setIsVerified(false);

        // --- Validation de l'entité via les contraintes Symfony ---
        // Le validateur vérifie les annotations/attributs #[Assert\...] sur l'entité User
        $violations = $validator->validate($user);
        if (count($violations) > 0) {
            // On transforme les violations en tableau de messages lisibles
            $violationMessages = [];
            foreach ($violations as $violation) {
                $violationMessages[] = $violation->getMessage();
            }
            return $this->json(['errors' => $violationMessages], 422);
        }

        // --- Sauvegarde en base de données ---
        // persist() : dit à Doctrine de surveiller cet objet (le "marquer" pour sauvegarde)
        $manager->persist($user);
        // flush() : exécute réellement la requête SQL INSERT pour sauvegarder en BDD
        $manager->flush();

        // --- Envoi de l'email de confirmation ---
        // On entoure ceci dans un try/catch pour que l'inscription réussisse même
        // si l'envoi d'email échoue (problème serveur mail, etc.)
        try {
            // generateSignature() crée une URL unique et sécurisée avec une signature
            // cryptographique. Cette URL sera envoyée par email pour confirmer l'adresse.
            // Arguments : nom de la route de vérification, ID user, email, paramètres
            $signature = $verifyEmailHelper->generateSignature(
                'security.verify_email',
                (string) $user->getId(),
                (string) $user->getEmail(),
                ['id' => $user->getId()]
            );

            // On construit l'email avec le builder "fluent" (méthodes chaînées)
            $email = (new TemplatedEmail())
                ->from('noreply@carxmeet.fr')          // Expéditeur
                ->to((string) $user->getEmail())        // Destinataire
                ->subject('Confirme ton adresse email') // Sujet
                ->htmlTemplate('emails/verify_email.html.twig') // Template Twig de l'email
                ->context([
                    // Ces variables seront disponibles dans le template Twig de l'email
                    'signedUrl' => $signature->getSignedUrl(),
                    'expiresAtMessageKey' => $signature->getExpirationMessageKey(),
                    'expiresAtMessageData' => $signature->getExpirationMessageData(),
                ]);

            // On envoie l'email via le service Mailer de Symfony
            $mailer->send($email);
        } catch (\Exception) {
            // erreur
            // Si l'envoi d'email échoue, on ignore l'exception.
            // L'utilisateur est quand même créé, mais il devra redemander l'email.
        }

        // --- Retour de la réponse de succès ---
        // Code 201 = "Created" (ressource créée avec succès)
        return $this->json([
            'message' => 'Inscription réussie ! Vérifie ton email pour confirmer ton compte.',
            'id' => $user->getId(),
        ], 201);
    }
}
