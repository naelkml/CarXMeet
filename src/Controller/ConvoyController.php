<?php

/**
 * =============================================================================
 * CONTRÔLEUR DES CONVOIS - ConvoyController
 * =============================================================================
 *
 * CE QUE FAIT CE CONTRÔLEUR :
 * Ce contrôleur gère les convois dans l'application CarXMeet. Un convoi est
 * un groupe de participants à un événement qui voyagent ensemble jusqu'au lieu
 * du meet. Les utilisateurs peuvent créer un convoi pour un événement, rejoindre
 * un convoi existant, ou le quitter.
 *
 * FONCTIONNALITÉS :
 *  - index()  : Page d'accueil générale des convois (non développée)
 *  - new()    : Créer un nouveau convoi pour un événement donné
 *  - join()   : Rejoindre un convoi existant
 *  - leave()  : Quitter un convoi qu'on a rejoint
 *
 * SÉCURITÉ :
 *  - Toutes les actions nécessitent d'être connecté (ROLE_USER)
 *  - Les formulaires sont protégés contre les attaques CSRF (token de sécurité)
 *
 * QU'EST-CE QU'UN TOKEN CSRF ?
 * CSRF (Cross-Site Request Forgery) est une attaque où un site malveillant
 * tente d'exécuter des actions à ton insu. Le token CSRF est un code secret
 * unique inclus dans chaque formulaire ; Symfony vérifie qu'il correspond avant
 * de traiter l'action, ce qui empêche les requêtes frauduleuses.
 * =============================================================================
 */

namespace App\Controller;

// Convoy : l'entité représentant un convoi en base de données
use App\Entity\Convoy;

// ConvoyParticipation : l'entité représentant la participation d'un user à un convoi
use App\Entity\ConvoyParticipation;

// Events : l'entité représentant un événement
use App\Entity\Events;

// User : l'entité représentant un utilisateur
use App\Entity\User;

// ConvoyType : le formulaire Symfony pour créer/éditer un convoi
use App\Form\ConvoyType;

// ConvoyParticipationRepository : pour chercher des participations à un convoi en BDD
use App\Repository\ConvoyParticipationRepository;

// EntityManagerInterface : le gestionnaire Doctrine pour sauvegarder/supprimer en BDD
use Doctrine\ORM\EntityManagerInterface;

// AbstractController : classe de base des contrôleurs Symfony
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Request : représente la requête HTTP reçue (données du formulaire, token CSRF, etc.)
use Symfony\Component\HttpFoundation\Request;

// Response : l'objet renvoyé au navigateur (page HTML ou redirection)
use Symfony\Component\HttpFoundation\Response;

// Route : attribut pour définir les URLs de chaque méthode
use Symfony\Component\Routing\Attribute\Route;

/**
 * Classe ConvoyController
 *
 * Gère la création, l'adhésion et la sortie des convois liés aux événements.
 */
final class ConvoyController extends AbstractController
{
    /**
     * Méthode index() — Page d'accueil générale de la section Convois
     *
     * ROUTE :
     *   - URL : /convoy
     *   - Nom : app_convoy
     *
     * PARAMÈTRES : aucun
     *
     * RETOUR : Response — la page HTML du template 'convoy/index.html.twig'
     *
     * NOTE : Cette page est encore vide et non développée.
     */
    #[Route('/convoy', name: 'app_convoy')]
    public function index(): Response
    {
        // Affiche le template Twig de la section convois
        return $this->render('convoy/index.html.twig', [
            'controller_name' => 'ConvoyController',
        ]);
    }

    /**
     * Méthode new() — Créer un nouveau convoi pour un événement
     *
     * ROUTE :
     *   - URL     : /events/{id}/convoys/new
     *   - Nom     : app_convoy_new
     *   - Méthodes: GET (afficher le formulaire) et POST (soumettre le formulaire)
     *   - {id}    : l'identifiant numérique (\d+ = digits uniquement) de l'événement
     *
     * PARAMTERIZATION AUTOMATIQUE DE L'ENTITÉ :
     * Symfony résout automatiquement "Events $event" à partir de {id} dans l'URL.
     * C'est le "param converter" : si l'URL contient {id}, Symfony cherche en BDD
     * l'entité correspondante et l'injecte directement comme paramètre.
     *
     * PARAMÈTRES :
     * @param Request                $request  La requête HTTP (formulaire soumis)
     * @param Events                 $event    L'événement auquel appartient le convoi
     *                                         (résolu automatiquement depuis l'URL)
     * @param EntityManagerInterface $em       Gestionnaire Doctrine pour sauvegarder
     *
     * RETOUR : Response — le formulaire de création ou une redirection vers l'événement
     */
    #[Route('/events/{id}/convoys/new', name: 'app_convoy_new', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function new(Request $request, Events $event, EntityManagerInterface $em): Response
    {
        // Seuls les utilisateurs connectés peuvent créer un convoi
        $this->denyAccessUnlessGranted('ROLE_USER');

        // On crée un nouvel objet Convoy vide
        $convoy = new Convoy();

        // On associe immédiatement ce convoi à l'événement passé dans l'URL
        $convoy->setEventID($event);

        // On crée le formulaire Symfony basé sur la classe ConvoyType.
        // Le formulaire est lié à l'objet $convoy : les données saisies
        // seront automatiquement mappées sur les propriétés de $convoy.
        $form = $this->createForm(ConvoyType::class, $convoy);

        // handleRequest() analyse la requête HTTP :
        // - Si c'est un GET (premier affichage), rien ne se passe
        // - Si c'est un POST (formulaire soumis), les données sont injectées dans $convoy
        $form->handleRequest($request);

        // On traite uniquement si le formulaire a été soumis ET si les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {
            // On dit à Doctrine de surveiller cet objet pour le sauvegarder
            $em->persist($convoy);

            // On exécute la requête SQL INSERT (sauvegarde effective en BDD)
            $em->flush();

            // addFlash() ajoute un message temporaire (session "flash") qui sera
            // affiché sur la prochaine page chargée. Idéal pour les confirmations.
            $this->addFlash('success', 'Convoi créé.');

            // On redirige vers la page de détail de l'événement
            return $this->redirectToRoute('app_events_show', ['id' => $event->getId()]);
        }

        // Si le formulaire n'est pas encore soumis (GET) ou s'il est invalide,
        // on affiche la page de création avec le formulaire
        return $this->render('convoy/new.html.twig', [
            'event' => $event,
            // createView() transforme l'objet formulaire en objet prêt pour Twig
            'form' => $form->createView(),
        ]);
    }

    /**
     * Méthode join() — Rejoindre un convoi existant
     *
     * ROUTE :
     *   - URL    : /convoys/{id}/join
     *   - Nom    : app_convoy_join
     *   - Méthode: POST uniquement (action de modification, pas simple consultation)
     *   - {id}   : l'identifiant numérique du convoi
     *
     * PARAMÈTRES :
     * @param Request                          $request                     Requête HTTP
     *                                                                        (contient le token CSRF)
     * @param Convoy                           $convoy                      Le convoi à rejoindre
     *                                                                        (résolu depuis l'URL)
     * @param ConvoyParticipationRepository    $convoyParticipationRepository Pour vérifier si
     *                                                                          l'user est déjà membre
     * @param EntityManagerInterface           $em                          Pour sauvegarder
     *
     * RETOUR : Response — redirection vers la page de l'événement
     */
    #[Route('/convoys/{id}/join', name: 'app_convoy_join', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function join(Request $request, Convoy $convoy, ConvoyParticipationRepository $convoyParticipationRepository, EntityManagerInterface $em): Response
    {
        // Vérification : l'utilisateur doit être connecté
        $this->denyAccessUnlessGranted('ROLE_USER');

        // On récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérification de type : getUser() peut théoriquement retourner autre chose
        // qu'un User. Cette vérification est une précaution de sécurité.
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        // --- Protection CSRF ---
        // On vérifie que le token CSRF dans la requête POST est valide.
        // 'join_convoy_' . $convoy->getId() est le nom unique du token pour ce convoi.
        // $request->request->get('_token') lit le champ caché '_token' du formulaire.
        if (!$this->isCsrfTokenValid('join_convoy_' . $convoy->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            // En cas d'erreur CSRF, on redirige vers l'événement parent du convoi
            // L'opérateur ?-> (nullsafe) évite une erreur si getEventID() retourne null
            return $this->redirectToRoute('app_events_show', ['id' => $convoy->getEventID()?->getId()]);
        }

        // --- Vérification si l'utilisateur est déjà dans ce convoi ---
        // findOneForUser() est une méthode personnalisée du repository qui cherche
        // une participation existante pour cet utilisateur dans ce convoi
        $existing = $convoyParticipationRepository->findOneForUser($convoy, $user);
        if ($existing) {
            // L'utilisateur est déjà membre, pas besoin de le re-ajouter
            $this->addFlash('info', 'Tu es déjà dans ce convoi.');
            return $this->redirectToRoute('app_events_show', ['id' => $convoy->getEventID()?->getId()]);
        }

        // --- Création de la participation ---
        // On crée un nouvel objet ConvoyParticipation qui lie l'utilisateur au convoi
        $member = new ConvoyParticipation();
        $member->setConvoyID($convoy); // On lie la participation au convoi
        $member->setUserID($user);     // On lie la participation à l'utilisateur

        // On sauvegarde la participation en base de données
        $em->persist($member);
        $em->flush();

        $this->addFlash('success', 'Tu as rejoint le convoi.');
        return $this->redirectToRoute('app_events_show', ['id' => $convoy->getEventID()?->getId()]);
    }

    /**
     * Méthode leave() — Quitter un convoi
     *
     * ROUTE :
     *   - URL    : /convoys/{id}/leave
     *   - Nom    : app_convoy_leave
     *   - Méthode: POST uniquement
     *   - {id}   : l'identifiant numérique du convoi
     *
     * PARAMÈTRES :
     * @param Request                          $request                     Requête HTTP
     *                                                                        (contient le token CSRF)
     * @param Convoy                           $convoy                      Le convoi à quitter
     *                                                                        (résolu depuis l'URL)
     * @param ConvoyParticipationRepository    $convoyParticipationRepository Pour trouver
     *                                                                          la participation à supprimer
     * @param EntityManagerInterface           $em                          Pour supprimer en BDD
     *
     * RETOUR : Response — redirection vers la page de l'événement
     */
    #[Route('/convoys/{id}/leave', name: 'app_convoy_leave', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function leave(Request $request, Convoy $convoy, ConvoyParticipationRepository $convoyParticipationRepository, EntityManagerInterface $em): Response
    {
        // Vérification : l'utilisateur doit être connecté
        $this->denyAccessUnlessGranted('ROLE_USER');

        // On récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérification de type pour la sécurité
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        // --- Protection CSRF ---
        // Même principe que dans join() : on vérifie le token secret du formulaire
        if (!$this->isCsrfTokenValid('leave_convoy_' . $convoy->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_events_show', ['id' => $convoy->getEventID()?->getId()]);
        }

        // --- Recherche de la participation existante ---
        // On cherche si l'utilisateur est bien membre de ce convoi
        $existing = $convoyParticipationRepository->findOneForUser($convoy, $user);
        if (!$existing) {
            // L'utilisateur n'est pas dans ce convoi, rien à supprimer
            $this->addFlash('info', 'Tu ne fais pas partie de ce convoi.');
            return $this->redirectToRoute('app_events_show', ['id' => $convoy->getEventID()?->getId()]);
        }

        // --- Suppression de la participation ---
        // remove() dit à Doctrine de supprimer cet objet de la base de données
        $em->remove($existing);
        // flush() exécute la requête SQL DELETE
        $em->flush();

        $this->addFlash('success', 'Tu as quitté le convoi.');
        return $this->redirectToRoute('app_events_show', ['id' => $convoy->getEventID()?->getId()]);
    }
}
