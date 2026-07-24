<?php

/**
 * =============================================================================
 * CONTRÔLEUR DES ARTICLES - ArticlesController
 * =============================================================================
 *
 * CE QUE FAIT CE CONTRÔLEUR :
 * Ce contrôleur gère la section "articles" de l'application CarXMeet.
 * Il contient une page d'accueil (index) prête à afficher des articles
 * (actualités, tutoriels, news automobiles, etc.).
 *
 * ÉTAT ACTUEL :
 * Ce contrôleur est encore basique : la méthode index() affiche uniquement
 * un template vide. La logique de récupération des articles en base de données
 * n'est pas encore implémentée.
 * =============================================================================
 */

namespace App\Controller;

// AbstractController : classe de base de tous les contrôleurs Symfony
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Response : objet qui représente la réponse HTTP renvoyée au navigateur
use Symfony\Component\HttpFoundation\Response;

// Route : attribut PHP pour associer une URL à une méthode
use Symfony\Component\Routing\Attribute\Route;

/**
 * Classe ArticlesController
 *
 * Gère les pages liées aux articles du site.
 * "final" indique que cette classe ne peut pas être héritée.
 */
final class ArticlesController extends AbstractController
{
    /**
     * Méthode index() — Page principale de la section Articles
     *
     * ROUTE :
     *   - URL  : /articles
     *   - Nom  : app_articles
     *
     * PARAMÈTRES : aucun
     *
     * RETOUR : Response — la page HTML rendue depuis le template
     *          'articles/index.html.twig'
     *
     * NOTE : Pour l'instant, cette page n'affiche pas encore d'articles réels.
     * Il faudrait injecter un ArticleRepository en paramètre et passer les
     * articles au template pour les afficher.
     */
    #[Route('/articles', name: 'app_articles')]
    public function index(): Response
    {
        // On affiche le template Twig de la section articles.
        // Le tableau ['controller_name' => ...] transmet une variable au template,
        // utile pendant le développement pour identifier quel contrôleur est actif.
        return $this->render('articles/index.html.twig', [
            'controller_name' => 'ArticlesController',
        ]);
    }
}
