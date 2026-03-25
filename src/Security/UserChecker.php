<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        // rien ici
    }

    public function checkPostAuth(UserInterface $user, TokenInterface|null $token = null): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Tu dois confirmer ton email avant de te connecter.'
            );
        }
    }
}
