<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
        ]);
    }

    #[Route('/profil', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            // Should not happen if ROLE_USER is enforced, but keep it safe.
            return $this->redirectToRoute('security.login');
        }

        $originalUsername = (string) $user->getUsername();
        $originalEmail = (string) $user->getEmail();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newUsername = (string) $user->getUsername();
            $newEmail = (string) $user->getEmail();

            if ($newUsername !== $originalUsername) {
                $existing = $userRepository->findOneBy(['username' => $newUsername]);
                if ($existing && $existing->getId() !== $user->getId()) {
                    $this->addFlash('error', "Ce nom d'utilisateur est deja utilise.");
                    return $this->redirectToRoute('app_user_edit');
                }
            }

            if ($newEmail !== $originalEmail) {
                $existing = $userRepository->findOneBy(['email' => $newEmail]);
                if ($existing && $existing->getId() !== $user->getId()) {
                    $this->addFlash('error', 'Cet email est deja utilise.');
                    return $this->redirectToRoute('app_user_edit');
                }
            }

            try {
                $em->flush();
                $this->addFlash('success', 'Profil mis a jour.');
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('error', "Email ou nom d'utilisateur deja utilise.");
            }

            return $this->redirectToRoute('app_user_edit');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
