<?php

namespace App\Controller;

use App\Repository\FriendshipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FriendshipController extends AbstractController
{
    #[Route('/friendship', name: 'app_friendship')]
    public function index(): Response
    {
        return $this->render('friendship/index.html.twig', [
            'controller_name' => 'FriendshipController',
        ]);
    }

    #[Route('/api/friendships', name: 'api_friendships_list', methods: ['GET'])]
    public function list(FriendshipRepository $friendshipRepository): Response
    {
        $friendships = $friendshipRepository->findAll();
        return $this->json($friendships);
    }
}
