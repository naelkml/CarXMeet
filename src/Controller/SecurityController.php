<?php

namespace App\Controller;

// Importation des classes nécessaires au fonctionnement du contrôleur
use App\Entity\User; // Représente un utilisateur enregistré dans l'application
use App\Form\RegistrationType; // Formulaire utilisé lors de l'inscription
use App\Repository\RegionRepository; // Permet de récupérer les régions depuis la base de données
use App\Repository\UserRepository; // Permet d'effectuer des recherches sur les utilisateurs
use Doctrine\ORM\EntityManagerInterface; // Gère les interactions avec la base de données
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Classe de base pour les contrôleurs Symfony
use Symfony\Component\HttpFoundation\Request; // Contient les informations envoyées par le navigateur
use Symfony\Component\HttpFoundation\Response; // Représente une réponse envoyée au navigateur
use Symfony\Component\Mailer\MailerInterface; // Permet d'envoyer des emails
use Symfony\Bridge\Twig\Mime\TemplatedEmail; // Permet de créer des emails utilisant un template Twig
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Permet de chiffrer les mots de passe
use Symfony\Component\Routing\Attribute\Route; // Permet de créer des routes accessibles par URL
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException; // Exception liée aux problèmes de connexion
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils; // Fournit les informations liées à l'authentification
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface; // Gestion des erreurs lors de la vérification d'email
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface; // Génère et vérifie les liens de confirmation d'email
use Symfony\Component\HttpFoundation\RedirectResponse; // Permet de rediriger l'utilisateur vers une autre page


// Contrôleur qui regroupe toutes les fonctionnalités liées à la sécurité :
// connexion, inscription, déconnexion et validation des comptes
final class SecurityController extends AbstractController
{

    // Route de la racine du site "/"
    // Elle redirige automatiquement vers la page d'accueil
    #[Route('/', name: 'app_root')]
    public function root(): RedirectResponse
    {
        // Redirection vers la route correspondant à la page d'accueil
        return $this->redirectToRoute('app_home');
    }


    // Route permettant d'afficher la page d'accueil après connexion
    #[Route('/home.html', name: 'app_home', methods: ['GET'])]
    public function home(RegionRepository $regionRepository): Response
    {
        // Vérifie que l'utilisateur possède un compte connecté
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Affiche la page d'accueil et transmet la liste des régions
        // Les régions sont triées par ordre alphabétique
        return $this->render('security/home.html.twig', [
            'regions' => $regionRepository->findBy([], ['name' => 'ASC']),
        ]);
    }


    // Route permettant d'afficher la page de connexion
    #[Route('/connexion.html', name: 'security.login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère l'erreur éventuelle lors de la dernière tentative de connexion
        $error = $authenticationUtils->getLastAuthenticationError();

        // Vérifie si l'erreur est liée à un compte dont l'email n'a pas encore été confirmé
        $showResendButton = $error instanceof CustomUserMessageAccountStatusException
            && str_contains($error->getMessageKey(), 'confirmer ton email');


        // Affiche la page de connexion avec les informations nécessaires
        return $this->render('security/login.html.twig', [
            // Dernier identifiant saisi par l'utilisateur
            'last_username' => $authenticationUtils->getLastUsername(),

            // Message d'erreur éventuel
            'error' => $error,

            // Indique s'il faut afficher le bouton pour renvoyer l'email de vérification
            'show_resend_button' => $showResendButton,
        ]);
    }


    // Route utilisée pour la déconnexion
    // Symfony intercepte automatiquement cette méthode grâce à la configuration de sécurité
    #[Route('/deconnexion', name: 'security.logout')]
    public function logout(): void
    {
        // Cette méthode reste volontairement vide.
        // Symfony gère automatiquement la suppression de la session utilisateur.
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    // Route permettant de créer un nouveau compte utilisateur
    #[Route('/inscription', name: 'security.registration', methods: ['GET', 'POST'])]
    public function registration(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $passwordHasher,
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer
    ): Response {

        // Création d'un nouvel utilisateur vide
        $user = new User();

        // Création du formulaire d'inscription associé à cet utilisateur
        $form = $this->createForm(RegistrationType::class, $user);


        // Récupère les données envoyées par l'utilisateur et les associe au formulaire
        $form->handleRequest($request);


        // Vérifie que le formulaire a été envoyé et que toutes les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {


            // Récupération de la photo de profil envoyée par l'utilisateur
            $profilePhoto = $form->get('profilePhoto')->getData();

            // Vérifie si une photo a été ajoutée
            if ($profilePhoto) {
                // Enregistre le contenu de l'image dans la base de données
                $user->setProfilePhoto(file_get_contents($profilePhoto->getPathname()));
            }


            // Chiffre le mot de passe avant de l'enregistrer
            // Le mot de passe n'est donc jamais stocké en clair dans la base de données
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

            // Enregistre la date de création du compte
            $user->setCreatedAt(new \DateTimeImmutable());

            // Définit le compte comme non vérifié tant que l'utilisateur n'a pas confirmé son email
            $user->setIsVerified(false);


            // Prépare l'utilisateur pour l'enregistrement en base de données
            $manager->persist($user);

            // Enregistre définitivement les données
            $manager->flush();


            // Envoie un email contenant le lien de confirmation
            $this->sendVerificationEmail($user, $verifyEmailHelper, $mailer);


            // Affiche un message de confirmation
            $this->addFlash('success', 'Inscription réussie ! Un email de vérification vient de t\'être envoyé.');


            // Redirection vers la page de connexion
            return $this->redirectToRoute('security.login');
        }


        // Si le formulaire a été envoyé mais contient des erreurs
        if ($form->isSubmitted() && !$form->isValid()) {

            // Affiche un message d'erreur
            $this->addFlash('error', 'Le formulaire contient des erreurs. Vérifie les champs et réessaie.');
        }


        // Affiche la page d'inscription avec le formulaire
        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    // Route permettant de confirmer l'adresse email d'un utilisateur
    #[Route('/verify/email', name: 'security.verify_email', methods: ['GET'])]
    public function verifyEmail(
        Request $request,
        UserRepository $userRepository,
        VerifyEmailHelperInterface $verifyEmailHelper,
        EntityManagerInterface $manager
    ): Response
    {

        // Récupère l'identifiant de l'utilisateur présent dans le lien reçu par email
        $userId = $request->query->get('id');

        // Recherche l'utilisateur correspondant dans la base de données
        $user = $userRepository->find($userId);


        // Vérifie que l'utilisateur existe
        if (!$user) {

            // Affiche une erreur si le lien est incorrect
            $this->addFlash('error', 'Le lien de vérification est invalide.');

            return $this->redirectToRoute('security.registration');
        }


        try {

            // Vérifie que le lien reçu est valide et n'a pas expiré
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                (string) $user->getId(),
                (string) $user->getEmail()
            );


        } catch (VerifyEmailExceptionInterface $exception) {

            // Affiche une erreur si le lien est invalide
            $this->addFlash('error', 'Le lien de vérification a expiré ou est invalide.');

            return $this->redirectToRoute('security.registration');
        }


        // Si le compte n'était pas encore vérifié
        if (!$user->isVerified()) {

            // Change l'état du compte en vérifié
            $user->setIsVerified(true);

            // Sauvegarde la modification
            $manager->flush();
        }


        // Informe l'utilisateur que son compte est activé
        $this->addFlash('success', 'Ton email est confirmé, tu peux maintenant te connecter.');


        // Affiche la page de confirmation
        return $this->render('security/verify_email.html.twig', [
            'user' => $user,
        ]);
    }


    // Route permettant de renvoyer un email de confirmation si l'utilisateur ne l'a pas reçu
    #[Route('/renvoyer-email-verification', name: 'security.resend_verification_email', methods: ['POST'])]
    public function resendVerificationEmail(
        Request $request,
        UserRepository $userRepository,
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer
    ): Response {


        // Vérifie que la requête provient bien du formulaire prévu
        if (!$this->isCsrfTokenValid('resend_verification_email', (string) $request->request->get('_token'))) {

            $this->addFlash('error', 'Requête invalide.');

            return $this->redirectToRoute('security.login');
        }


        // Récupère le nom d'utilisateur fourni dans le formulaire
        $username = trim((string) $request->request->get('username'));


        // Vérifie qu'un nom d'utilisateur a bien été renseigné
        if ($username === '') {

            $this->addFlash('error', 'Nom d\'utilisateur manquant.');

            return $this->redirectToRoute('security.login');
        }


        // Recherche l'utilisateur correspondant
        $user = $userRepository->findOneBy(['username' => $username]);


        // Vérifie que l'utilisateur existe
        if (!$user instanceof User) {

            $this->addFlash('error', 'Impossible de renvoyer l\'email de vérification.');

            return $this->redirectToRoute('security.login');
        }


        // Vérifie si le compte est déjà validé
        if ($user->isVerified()) {

            $this->addFlash('info', 'Ton compte est déjà vérifié.');

            return $this->redirectToRoute('security.login');
        }


        // Renvoie un nouvel email de confirmation
        $this->sendVerificationEmail($user, $verifyEmailHelper, $mailer);


        $this->addFlash('success', 'Un nouvel email de vérification vient d\'être envoyé.');

        return $this->redirectToRoute('security.login');
    }


    // Fonction privée utilisée pour envoyer un email de confirmation
    private function sendVerificationEmail(
        User $user,
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer
    ): void {


        // Génère une URL sécurisée unique pour confirmer l'adresse email
        $signature = $verifyEmailHelper->generateSignature(
            'security.verify_email',
            (string) $user->getId(),
            (string) $user->getEmail(),
            ['id' => $user->getId()]
        );


        // Création de l'email avec un modèle Twig
        $email = (new TemplatedEmail())

            // Adresse d'envoi utilisée par l'application
            ->from('noreply@carxmeet.fr')

            // Adresse du destinataire
            ->to((string) $user->getEmail())

            // Sujet du mail
            ->subject('Confirme ton adresse email')

            // Template HTML utilisé pour afficher le contenu du mail
            ->htmlTemplate('emails/verify_email.html.twig')

            // Données envoyées au template
            ->context([
                'signedUrl' => $signature->getSignedUrl(),
                'expiresAtMessageKey' => $signature->getExpirationMessageKey(),
                'expiresAtMessageData' => $signature->getExpirationMessageData(),
            ]);


        // Envoi de l'email
        $mailer->send($email);
    }
}
