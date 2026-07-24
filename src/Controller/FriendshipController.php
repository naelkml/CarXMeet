<?php

namespace App\Controller;

// Importation des classes nécessaires au fonctionnement du contrôleur
use App\Entity\Friendship; // Représente une relation d'amitié en base de données
use App\Entity\User; // Représente un utilisateur de l'application
use App\Repository\FriendshipRepository; // Permet de rechercher des amitiés dans la base de données
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Classe de base pour créer un contrôleur Symfony
use Doctrine\ORM\EntityManagerInterface; // Permet de gérer les opérations sur la base de données (ajout, suppression...)
use Symfony\Component\HttpFoundation\Request; // Représente une requête HTTP envoyée par un utilisateur
use Symfony\Component\HttpFoundation\Response; // Représente une réponse HTTP envoyée par le serveur
use Symfony\Component\Routing\Attribute\Route; // Permet de créer des routes accessibles depuis le navigateur


// Ce contrôleur gère toutes les actions liées aux relations d'amitié entre utilisateurs
final class FriendshipController extends AbstractController
{
    // Route permettant d'afficher la liste des amis de l'utilisateur connecté
    // Accessible avec une requête GET sur l'adresse /friends
    #[Route('/friends', name: 'app_friendship_list', methods: ['GET'])]
    public function index(FriendshipRepository $friendshipRepository): Response
    {
        // Vérifie que l'utilisateur est connecté et possède le rôle utilisateur
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifie que l'utilisateur récupéré est bien une instance de la classe User
        if (!$user instanceof User) {
            // Si aucun utilisateur valide n'est trouvé, redirection vers la page de connexion
            return $this->redirectToRoute('security.login');
        }

        // Affiche la page contenant la liste des amis de l'utilisateur
        // La méthode listFriends récupère les amis depuis la base de données
        return $this->render('friendship/index.html.twig', [
            'friends' => $friendshipRepository->listFriends($user),
        ]);
    }


    // Route permettant d'ajouter un utilisateur dans sa liste d'amis
    // L'identifiant de l'utilisateur ciblé est récupéré dans l'URL
    // Exemple : /friends/5/add
    #[Route('/friends/{id}/add', name: 'app_friendship_add', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function add(Request $request, User $target, FriendshipRepository $friendshipRepository, EntityManagerInterface $em): Response
    {
        // Vérifie que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifie que l'utilisateur existe bien
        if (!$user instanceof User) {
            // Redirection vers la page de connexion si nécessaire
            return $this->redirectToRoute('security.login');
        }


        // Vérifie que le formulaire envoyé possède un token CSRF valide
        // Cela protège contre les attaques qui pourraient envoyer des requêtes non autorisées
        if (!$this->isCsrfTokenValid('friend_add_' . $target->getId(), (string) $request->request->get('_token'))) {
            // Affiche un message d'erreur à l'utilisateur
            $this->addFlash('error', 'Token CSRF invalide.');

            // Retourne sur la page du profil de l'utilisateur ciblé
            return $this->redirectToRoute('app_users_show', ['id' => $target->getId()]);
        }


        // Vérifie que l'utilisateur ne tente pas de s'ajouter lui-même comme ami
        if ($user->getId() === $target->getId()) {
            // Affiche un message d'erreur
            $this->addFlash('error', 'Tu ne peux pas t\'ajouter toi-même.');

            // Retourne sur le profil concerné
            return $this->redirectToRoute('app_users_show', ['id' => $target->getId()]);
        }


        // Vérifie si une relation d'amitié existe déjà entre les deux utilisateurs
        if ($friendshipRepository->areFriends($user, $target)) {
            // Informe l'utilisateur que l'amitié existe déjà
            $this->addFlash('info', 'Vous êtes déjà amis.');

            // Retourne sur le profil de l'utilisateur ciblé
            return $this->redirectToRoute('app_users_show', ['id' => $target->getId()]);
        }


        // Création d'un nouvel objet Friendship représentant la nouvelle relation
        $friendship = new Friendship();

        // Définit l'utilisateur qui envoie la demande d'amitié
        $friendship->setRequesterId($user);

        // Définit l'utilisateur qui reçoit la demande
        $friendship->setReceiverId($target);

        // Définit directement la relation comme acceptée
        // (Il n'y a pas de système de demande en attente dans cette implémentation)
        $friendship->setStatus('accepted');


        // Prépare l'enregistrement de l'amitié dans la base de données
        $em->persist($friendship);

        // Enregistre réellement les modifications dans la base de données
        $em->flush();


        // Affiche un message de confirmation
        $this->addFlash('success', 'Ami ajouté.');

        // Retourne sur le profil de l'utilisateur ajouté
        return $this->redirectToRoute('app_users_show', ['id' => $target->getId()]);
    }


    // Route permettant de supprimer une relation d'amitié
    // Exemple : /friends/5/remove
    #[Route('/friends/{id}/remove', name: 'app_friendship_remove', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function remove(Request $request, User $target, FriendshipRepository $friendshipRepository, EntityManagerInterface $em): Response
    {
        // Vérifie que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('ROLE_USER');


        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifie que l'utilisateur existe bien
        if (!$user instanceof User) {
            // Redirection vers la page de connexion
            return $this->redirectToRoute('security.login');
        }


        // Vérifie que le formulaire de suppression possède un token CSRF valide
        if (!$this->isCsrfTokenValid('friend_remove_' . $target->getId(), (string) $request->request->get('_token'))) {
            // Affiche un message d'erreur
            $this->addFlash('error', 'Token CSRF invalide.');

            // Retourne vers la liste des utilisateurs
            return $this->redirectToRoute('app_users');
        }


        // Recherche une relation d'amitié existante entre les deux utilisateurs
        $existing = $friendshipRepository->findAcceptedBetween($user, $target);


        // Vérifie qu'une relation existe bien avant de la supprimer
        if (!$existing) {
            // Informe l'utilisateur qu'il n'existe aucune relation d'amitié
            $this->addFlash('info', 'Vous n\'êtes pas amis.');

            // Retourne vers la liste des utilisateurs
            return $this->redirectToRoute('app_users');
        }


        // Supprime la relation d'amitié de la base de données
        $em->remove($existing);

        // Valide définitivement la suppression dans la base de données
        $em->flush();


        // Informe l'utilisateur que la suppression est terminée
        $this->addFlash('success', 'Ami supprimé.');

        // Retourne vers la liste des utilisateurs
        return $this->redirectToRoute('app_users');
    }
}
