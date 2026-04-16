<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\FriendshipRepository;
use App\Repository\ParticipationRepository;
use App\Repository\VehicleRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/users', name: 'app_users')]
    public function index(UserRepository $userRepository, FriendshipRepository $friendshipRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $me = $this->getUser();
        $friendIds = $me instanceof User ? $friendshipRepository->getFriendIds($me) : [];

        $users = $userRepository->findAll();
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
            'friendIds' => $friendIds,
        ]);
    }

    #[Route('/users/{id}', name: 'app_users_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(
        User $profileUser,
        FriendshipRepository $friendshipRepository,
        VehicleRepository $vehicleRepository,
        ParticipationRepository $participationRepository
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $me = $this->getUser();
        if (!$me instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        $isMe = $me->getId() === $profileUser->getId();
        $isFriend = $friendshipRepository->areFriends($me, $profileUser);

        $vehicles = [];
        $events = [];
        if ($isMe || $isFriend) {
            $vehicles = $vehicleRepository->findBy(['userID' => $profileUser], ['id' => 'DESC']);
            $events = $participationRepository->findEventsForUser($profileUser);
        }

        return $this->render('user/show.html.twig', [
            'profileUser' => $profileUser,
            'isMe' => $isMe,
            'isFriend' => $isFriend,
            'vehicles' => $vehicles,
            'events' => $events,
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
            $profilePhoto = $form->get('profilePhoto')->getData();
            if ($profilePhoto) {
                $user->setProfilePhoto(file_get_contents($profilePhoto->getPathname()));
            }

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
