<?php

namespace App\Controller;

use App\Repository\ArticlesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArticlesController extends AbstractController
{
    #[Route('/articles', name: 'app_articles')]
    public function index(): Response
    {
        return $this->render('articles/index.html.twig', [
            'controller_name' => 'ArticlesController',
        ]);
    }

    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(ArticlesRepository $articlesRepository): Response
    {
        $articles = $articlesRepository->findAll();
        return $this->json($articles);
    }
}
