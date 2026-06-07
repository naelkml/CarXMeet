<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Api\FormDataHelper;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
final class UpdateUserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(User $user, Request $request): User
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $current = $this->getUser();
        if (!$current instanceof User || $current->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException('Tu ne peux modifier que ton propre profil.');
        }

        $originalUsername = (string) $user->getUsername();
        $originalEmail = (string) $user->getEmail();

        foreach (['firstName', 'lastName', 'username', 'email', 'phone', 'snapchat', 'instagram', 'twitter', 'tiktok'] as $field) {
            if (!$request->request->has($field)) {
                continue;
            }
            $value = FormDataHelper::getString($request, $field);
            match ($field) {
                'firstName' => $user->setFirstName($value ?? ''),
                'lastName' => $user->setLastName($value ?? ''),
                'username' => $user->setUsername($value ?? $originalUsername),
                'email' => $user->setEmail($value ?? $originalEmail),
                'phone' => $user->setPhone($value ?? ''),
                'snapchat' => $user->setSnapchat($value),
                'instagram' => $user->setInstagram($value),
                'twitter' => $user->setTwitter($value),
                'tiktok' => $user->setTiktok($value),
            };
        }

        $profilePhoto = FormDataHelper::getUploadedFiles($request, 'profilePhoto')[0] ?? null;
        if ($profilePhoto) {
            $user->setProfilePhoto(file_get_contents($profilePhoto->getPathname()));
        }

        $newUsername = (string) $user->getUsername();
        if ($newUsername !== $originalUsername) {
            $existing = $this->userRepository->findOneBy(['username' => $newUsername]);
            if ($existing && $existing->getId() !== $user->getId()) {
                throw new BadRequestHttpException('Ce nom d\'utilisateur est déjà utilisé.');
            }
        }

        $newEmail = (string) $user->getEmail();
        if ($newEmail !== $originalEmail) {
            $existing = $this->userRepository->findOneBy(['email' => $newEmail]);
            if ($existing && $existing->getId() !== $user->getId()) {
                throw new BadRequestHttpException('Cet email est déjà utilisé.');
            }
        }

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            throw new BadRequestHttpException('Email ou nom d\'utilisateur déjà utilisé.');
        }

        return $user;
    }
}
