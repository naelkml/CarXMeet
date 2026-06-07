<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final class ApiRegistrationController extends AbstractController
{
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
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['errors' => ['Données JSON invalides']], 400);
        }

        $errors = [];

        if (empty($data['firstName'])) $errors[] = 'Le prénom est obligatoire';
        if (empty($data['lastName'])) $errors[] = 'Le nom est obligatoire';
        if (empty($data['username'])) $errors[] = 'Le nom d\'utilisateur est obligatoire';
        if (empty($data['phone'])) $errors[] = 'Le téléphone est obligatoire';
        if (empty($data['email'])) $errors[] = 'L\'email est obligatoire';
        if (empty($data['password'])) $errors[] = 'Le mot de passe est obligatoire';
        elseif (strlen($data['password']) < 8) $errors[] = 'Le mot de passe doit contenir au moins 8 caractères';

        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        if ($userRepository->findOneBy(['username' => $data['username']])) {
            return $this->json(['errors' => ['Ce nom d\'utilisateur est déjà pris']], 422);
        }

        if ($userRepository->findOneBy(['email' => $data['email']])) {
            return $this->json(['errors' => ['Cette adresse email est déjà utilisée']], 422);
        }

        $user = new User();
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setUsername($data['username']);
        $user->setPhone($data['phone']);
        $user->setEmail($data['email']);
        $user->setSnapchat($data['snapchat'] ?? null);
        $user->setInstagram($data['instagram'] ?? null);
        $user->setTwitter($data['twitter'] ?? null);
        $user->setTiktok($data['tiktok'] ?? null);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setIsVerified(false);

        $violations = $validator->validate($user);
        if (count($violations) > 0) {
            $violationMessages = [];
            foreach ($violations as $violation) {
                $violationMessages[] = $violation->getMessage();
            }
            return $this->json(['errors' => $violationMessages], 422);
        }

        $manager->persist($user);
        $manager->flush();

        try {
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
        } catch (\Exception) {
            // erreur
        }

        return $this->json([
            'message' => 'Inscription réussie ! Vérifie ton email pour confirmer ton compte.',
            'id' => $user->getId(),
        ], 201);
    }
}
