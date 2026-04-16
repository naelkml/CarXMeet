<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\RegionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final class SecurityController extends AbstractController
{
    #[Route('/home', name: 'app_home', methods: ['GET'])]
    public function home(RegionRepository $regionRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('security/home.html.twig', [
            'regions' => $regionRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/connexion', name: 'security.login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $showResendButton = $error instanceof CustomUserMessageAccountStatusException
            && str_contains($error->getMessageKey(), 'confirmer ton email');

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $error,
            'show_resend_button' => $showResendButton,
        ]);
    }

    #[Route('/deconnexion', name: 'security.logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/inscription', name: 'security.registration', methods: ['GET', 'POST'])]
    public function registration(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $passwordHasher,
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $profilePhoto = $form->get('profilePhoto')->getData();
            if ($profilePhoto) {
                $user->setProfilePhoto(file_get_contents($profilePhoto->getPathname()));
            }

            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setIsVerified(false);

            $manager->persist($user);
            $manager->flush();

            $this->sendVerificationEmail($user, $verifyEmailHelper, $mailer);

            $this->addFlash('success', 'Inscription réussie ! Un email de vérification vient de t\'être envoyé.');

            return $this->redirectToRoute('security.login');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs. Vérifie les champs et réessaie.');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'security.verify_email', methods: ['GET'])]
    public function verifyEmail(
        Request $request,
        UserRepository $userRepository,
        VerifyEmailHelperInterface $verifyEmailHelper,
        EntityManagerInterface $manager
    ): Response
    {
        $userId = $request->query->get('id');
        $user = $userRepository->find($userId);

        if (!$user) {
            $this->addFlash('error', 'Le lien de vérification est invalide.');
            return $this->redirectToRoute('security.registration');
        }

        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                (string) $user->getId(),
                (string) $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', 'Le lien de vérification a expiré ou est invalide.');

            return $this->redirectToRoute('security.registration');
        }

        if (!$user->isVerified()) {
            $user->setIsVerified(true);
            $manager->flush();
        }

        $this->addFlash('success', 'Ton email est confirmé, tu peux maintenant te connecter.');

        return $this->render('security/verify_email.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/renvoyer-email-verification', name: 'security.resend_verification_email', methods: ['POST'])]
    public function resendVerificationEmail(
        Request $request,
        UserRepository $userRepository,
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer
    ): Response {
        if (!$this->isCsrfTokenValid('resend_verification_email', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Requête invalide.');
            return $this->redirectToRoute('security.login');
        }

        $username = trim((string) $request->request->get('username'));
        if ($username === '') {
            $this->addFlash('error', 'Nom d\'utilisateur manquant.');
            return $this->redirectToRoute('security.login');
        }

        $user = $userRepository->findOneBy(['username' => $username]);
        if (!$user instanceof User) {
            $this->addFlash('error', 'Impossible de renvoyer l\'email de vérification.');
            return $this->redirectToRoute('security.login');
        }

        if ($user->isVerified()) {
            $this->addFlash('info', 'Ton compte est déjà vérifié.');
            return $this->redirectToRoute('security.login');
        }

        $this->sendVerificationEmail($user, $verifyEmailHelper, $mailer);
        $this->addFlash('success', 'Un nouvel email de vérification vient d\'être envoyé.');

        return $this->redirectToRoute('security.login');
    }

    private function sendVerificationEmail(
        User $user,
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer
    ): void {
        $signature = $verifyEmailHelper->generateSignature(
            'security.verify_email',
            (string) $user->getId(),
            (string) $user->getEmail(),
            ['id' => $user->getId()]
        );

        $email = (new TemplatedEmail())
            ->from('noreply@carxmeet.fr')
            ->to((string) $user->getEmail())
            ->subject('Confirme ton adresse email')
            ->htmlTemplate('emails/verify_email.html.twig')
            ->context([
                'signedUrl' => $signature->getSignedUrl(),
                'expiresAtMessageKey' => $signature->getExpirationMessageKey(),
                'expiresAtMessageData' => $signature->getExpirationMessageData(),
            ]);

        $mailer->send($email);
    }
}
