<?php

/**
 * =============================================================================
 * CONTRÔLEUR DES ADRESSES - AdressesController
 * =============================================================================
 *
 * QU'EST-CE QU'UN CONTRÔLEUR ?
 * Un contrôleur est la partie du code qui reçoit les requêtes HTTP (ce que
 * l'utilisateur demande via son navigateur) et qui retourne une réponse
 * (la page HTML, des données JSON, etc.).
 *
 * CE QUE FAIT CE CONTRÔLEUR :
 * Ce contrôleur gère la section "adresses" de l'application CarXMeet.
 * Pour l'instant, il contient uniquement une page d'accueil vide (page index)
 * prête à être développée.
 *
 * EMPLACEMENT DANS L'APPLICATION :
 * Ce fichier est dans le dossier src/Controller/, qui contient tous les
 * contrôleurs de l'application.
 * =============================================================================
 */

namespace App\Controller;

// On importe AbstractController : la classe de base de Symfony dont héritent
// tous les contrôleurs. Elle fournit des méthodes pratiques comme render(),
// redirectToRoute(), addFlash(), etc.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Response : l'objet qui représente la réponse HTTP renvoyée au navigateur.
// Toute action d'un contrôleur doit retourner une Response.
use Symfony\Component\HttpFoundation\Response;

// Route : l'attribut PHP qui permet de définir l'URL qui déclenchera cette méthode.
use Symfony\Component\Routing\Attribute\Route;

/**
 * Classe AdressesController
 *
 * Le mot-clé "final" signifie que cette classe ne peut pas être étendue
 * (héritée) par d'autres classes. C'est une bonne pratique pour les contrôleurs.
 *
 * "extends AbstractController" : ce contrôleur hérite de la classe de base
 * Symfony, ce qui lui donne accès à des outils pratiques.
 */
final class AdressesController extends AbstractController
{
    /**
     * Méthode index() — Page principale de la section Adresses
     *
     * QU'EST-CE QUE #[Route(...)] ?
     * C'est un "attribut PHP". Il indique à Symfony que quand un utilisateur
     * visite l'URL '/adresses', c'est cette méthode qui doit être appelée.
     *   - '/adresses'         : l'URL dans le navigateur
     *   - name: 'app_adresses': le nom interne de cette route, utilisé dans le
     *                           code pour générer des liens vers cette page
     *
     * PARAMÈTRES : aucun — cette page ne nécessite aucune donnée en entrée.
     *
     * RETOUR : Response — une page HTML rendue depuis le template Twig
     *          'adresses/index.html.twig'.
     *
     * QU'EST-CE QUE TWIG ?
     * Twig est le moteur de templates de Symfony. Les fichiers .html.twig sont
     * des fichiers HTML avec des balises spéciales ({{ }}, {% %}) qui permettent
     * d'afficher des données dynamiques.
     */
    #[Route('/adresses', name: 'app_adresses')]
    public function index(): Response
    {
        // $this->render() charge le fichier de template Twig indiqué et retourne
        // la page HTML générée. Le tableau en second argument transmet des variables
        // au template (ici, le nom du contrôleur, utilisé dans le template).
        return $this->render('adresses/index.html.twig', [
            'controller_name' => 'AdressesController',
        ]);
    }
}
