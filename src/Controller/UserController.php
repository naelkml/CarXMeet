<?php

namespace App\Controller;

// Importation des classes nécessaires au fonctionnement du contrôleur
use App\Entity\User; // Représente un utilisateur de l'application
use App\Form\UserType; // Formulaire utilisé pour modifier un profil utilisateur
use App\Repository\FriendshipRepository; // Permet de récupérer les relations d'amitié
use App\Repository\ParticipationRepository; // Permet de récupérer les participations aux événements
use App\Repository\VehicleRepository; // Permet de récupérer les véhicules des utilisateurs
use Doctrine\DBAL\Exception\UniqueConstraintViolationException; // Gère les erreurs de doublons en base de données
use Doctrine\ORM\EntityManagerInterface; // Permet d'effectuer des opérations sur la base de données
use App\Repository\UserRepository; // Permet d'effectuer des recherches sur les utilisateurs
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Classe de base pour les contrôleurs Symfony
use Symfony\Component\HttpFoundation\Request; // Contient les données envoyées par le navigateur
use Symfony\Component\HttpFoundation\Response; // Représente une réponse HTTP
use Symfony\Component\Routing\Attribute\Route; // Permet de définir les routes accessibles par URL


// Contrôleur permettant de gérer les utilisateurs :
// affichage des utilisateurs, profils et modification des informations personnelles
final class UserController extends AbstractController
{

    // Route permettant d'afficher la liste de tous les utilisateurs
    #[Route('/users', name: 'app_users')]
    public function index(UserRepository $userRepository, FriendshipRepository $friendshipRepository): Response
    {
        // Vérifie que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('ROLE_USER');


        // Récupère l'utilisateur actuellement connecté
        $me = $this->getUser();


        // Récupère la liste des identifiants des utilisateurs qui sont ses amis
        // Cela permet ensuite d'afficher leur statut d'amitié dans la liste
        $friendIds = $me instanceof User ? $friendshipRepository->getFriendIds($me) : [];


        // Récupère tous les utilisateurs présents dans la base de données
        $users = $userRepository->findAll();


        // Envoie les données vers la page Twig permettant l'affichage
        return $this->render('user/index.html.twig', [
            // Nom du contrôleur utilisé
            'controller_name' => 'UserController',

            // Liste complète des utilisateurs
            'users' => $users,

            // Liste des utilisateurs déjà amis avec le compte connecté
            'friendIds' => $friendIds,
        ]);
    }



    // Route permettant d'afficher le profil d'un utilisateur précis
    // Exemple : /users/5 affiche le profil de l'utilisateur ayant l'id 5
    #[Route('/users/{id}', name: 'app_users_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(
        User $profileUser,
        FriendshipRepository $friendshipRepository,
        VehicleRepository $vehicleRepository,
        ParticipationRepository $participationRepository
    ): Response
    {

        // Vérifie que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('ROLE_USER');


        // Récupère l'utilisateur actuellement connecté
        $me = $this->getUser();


        // Sécurité supplémentaire : vérifie que l'utilisateur existe bien
        if (!$me instanceof User) {

            // Redirige vers la page de connexion si aucun utilisateur n'est trouvé
            return $this->redirectToRoute('security.login');
        }


        // Vérifie si l'utilisateur consulte son propre profil
        $isMe = $me->getId() === $profileUser->getId();


        // Vérifie si l'utilisateur connecté est ami avec le profil consulté
        $isFriend = $friendshipRepository->areFriends($me, $profileUser);



        // Initialisation des tableaux contenant les données privées du profil
        $vehicles = [];
        $events = [];


        // Les véhicules et événements sont visibles uniquement :
        // - par l'utilisateur lui-même
        // - par ses amis
        if ($isMe || $isFriend) {

            // Récupère les véhicules appartenant à l'utilisateur du profil
            // Triés du plus récent au plus ancien
            $vehicles = $vehicleRepository->findBy(['userID' => $profileUser], ['id' => 'DESC']);


            // Récupère les événements auxquels cet utilisateur participe
            $events = $participationRepository->findEventsForUser($profileUser);
        }



        // Affiche la page du profil utilisateur
        return $this->render('user/show.html.twig', [

            // Utilisateur dont le profil est affiché
            'profileUser' => $profileUser,

            // Indique si le profil appartient à l'utilisateur connecté
            'isMe' => $isMe,

            // Indique si les deux utilisateurs sont amis
            'isFriend' => $isFriend,

            // Liste des véhicules accessibles
            'vehicles' => $vehicles,

            // Liste des événements accessibles
            'events' => $events,
        ]);
    }




    // Route permettant de modifier son propre profil utilisateur
    #[Route('/profil', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {

        // Vérifie que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('ROLE_USER');


        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();


        // Vérifie que l'utilisateur récupéré est bien un objet User
        if (!$user instanceof User) {

            // Cette situation ne devrait pas arriver grâce au contrôle ROLE_USER,
            // mais cette vérification évite une erreur du programme
            return $this->redirectToRoute('security.login');
        }



        // Sauvegarde les anciennes valeurs avant modification
        // Cela permettra de vérifier si elles ont changé
        $originalUsername = (string) $user->getUsername();
        $originalEmail = (string) $user->getEmail();



        // Création du formulaire de modification du profil
        $form = $this->createForm(UserType::class, $user);


        // Récupère les données envoyées par l'utilisateur
        $form->handleRequest($request);



        // Vérifie que le formulaire a été envoyé et qu'il ne contient pas d'erreur
        if ($form->isSubmitted() && $form->isValid()) {


            // Récupère une éventuelle nouvelle photo de profil
            $profilePhoto = $form->get('profilePhoto')->getData();


            // Si une photo est envoyée, elle est enregistrée en base de données
            if ($profilePhoto) {

                $user->setProfilePhoto(file_get_contents($profilePhoto->getPathname()));
            }



            // Récupération des nouvelles informations saisies
            $newUsername = (string) $user->getUsername();
            $newEmail = (string) $user->getEmail();



            // Vérifie si le nom d'utilisateur a été modifié
            if ($newUsername !== $originalUsername) {


                // Recherche un utilisateur possédant déjà ce nom
                $existing = $userRepository->findOneBy(['username' => $newUsername]);


                // Vérifie qu'il ne s'agit pas du même utilisateur
                if ($existing && $existing->getId() !== $user->getId()) {


                    // Informe l'utilisateur que le nom est déjà utilisé
                    $this->addFlash('error', "Ce nom d'utilisateur est deja utilise.");


                    return $this->redirectToRoute('app_user_edit');
                }
            }



            // Vérifie si l'adresse email a été modifiée
            if ($newEmail !== $originalEmail) {


                // Recherche un utilisateur avec cette adresse email
                $existing = $userRepository->findOneBy(['email' => $newEmail]);


                // Vérifie que l'adresse email n'appartient pas déjà à quelqu'un d'autre
                if ($existing && $existing->getId() !== $user->getId()) {


                    // Informe l'utilisateur que l'email existe déjà
                    $this->addFlash('error', 'Cet email est deja utilise.');


                    return $this->redirectToRoute('app_user_edit');
                }
            }



            try {

                // Enregistre les modifications du profil dans la base de données
                $em->flush();


                // Message de confirmation
                $this->addFlash('success', 'Profil mis a jour.');

            } catch (UniqueConstraintViolationException) {


                // Gestion d'une erreur liée à une valeur déjà existante
                $this->addFlash('error', "Email ou nom d'utilisateur deja utilise.");
            }



            // Recharge la page de modification du profil
            return $this->redirectToRoute('app_user_edit');
        }



        // Affiche la page contenant le formulaire de modification
        return $this->render('user/edit.html.twig', [

            // Envoie le formulaire créé à la vue Twig
            'form' => $form->createView(),
        ]);
    }
}
