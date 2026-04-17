<?php

namespace App\Controller;

use App\Entity\Friendship;
use App\Entity\User;
use App\Repository\FriendshipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FriendshipController extends AbstractController
{
    #[Route('/friends', name: 'app_friendship_list', methods: ['GET'])]
    public function index(FriendshipRepository $friendshipRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        return $this->render('friendship/index.html.twig', [
            'friends' => $friendshipRepository->listFriends($user),
        ]);
    }

    #[Route('/friends/{id}/add', name: 'app_friendship_add', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function add(Request $request, User $target, FriendshipRepository $friendshipRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        if (!$this->isCsrfTokenValid('friend_add_' . $target->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_users_show', ['id' => $target->getId()]);
        }

        if ($user->getId() === $target->getId()) {
            $this->addFlash('error', 'Tu ne peux pas t\'ajouter toi-même.');
            return $this->redirectToRoute('app_users_show', ['id' => $target->getId()]);
        }

        if ($friendshipRepository->areFriends($user, $target)) {
            $this->addFlash('info', 'Vous êtes déjà amis.');
            return $this->redirectToRoute('app_users_show', ['id' => $target->getId()]);
        }

        $friendship = new Friendship();
        $friendship->setRequesterId($user);
        $friendship->setReceiverId($target);
        $friendship->setStatus('accepted');

        $em->persist($friendship);
        $em->flush();

        $this->addFlash('success', 'Ami ajouté.');
        return $this->redirectToRoute('app_users_show', ['id' => $target->getId()]);
    }

    #[Route('/friends/{id}/remove', name: 'app_friendship_remove', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function remove(Request $request, User $target, FriendshipRepository $friendshipRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        if (!$this->isCsrfTokenValid('friend_remove_' . $target->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_users');
        }

        $existing = $friendshipRepository->findAcceptedBetween($user, $target);
        if (!$existing) {
            $this->addFlash('info', 'Vous n\'êtes pas amis.');
            return $this->redirectToRoute('app_users');
        }

        $em->remove($existing);
        $em->flush();

        $this->addFlash('success', 'Ami supprimé.');
        return $this->redirectToRoute('app_users');
    }

    #[Route('/api/friendships', name: 'api_friendships_list', methods: ['GET'])]
    public function list(FriendshipRepository $friendshipRepository): Response
    {
        $friendships = $friendshipRepository->findAll();
        return $this->json($friendships);
    }
}
