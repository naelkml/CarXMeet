<?php

namespace App\Controller;

use App\Repository\PostphotoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostphotoController extends AbstractController
{
    #[Route('/postphoto', name: 'app_postphoto')]
    public function index(): Response
    {
        return $this->render('postphoto/index.html.twig', [
            'controller_name' => 'PostphotoController',
        ]);
    }

    #[Route('/api/postphotos', name: 'api_postphotos_list', methods: ['GET'])]
    public function list(PostphotoRepository $postphotoRepository): Response
    {
        $postphotos = $postphotoRepository->findAll();
        return $this->json($postphotos);
    }
}
