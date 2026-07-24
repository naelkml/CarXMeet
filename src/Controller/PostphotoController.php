<?php

/**
 * =============================================================================
 * CONTRÔLEUR DES PUBLICATIONS DE PHOTOS - PostphotoController
 * =============================================================================
 *
 * CE QUE FAIT CE CONTRÔLEUR :
 * Ce contrôleur est prévu pour gérer les publications de photos par les
 * utilisateurs dans l'application CarXMeet (photos de voitures, de meets, etc.).
 *
 * ÉTAT ACTUEL :
 * Ce contrôleur est encore à l'état initial. Il ne contient qu'une page
 * d'accueil basique. Les fonctionnalités d'upload et d'affichage de photos
 * sont à implémenter.
 *
 * NOTE : L'upload de photos liées aux événements est géré dans EventsController,
 * et les photos de véhicules dans VehicleController.
 * =============================================================================
 */

namespace App\Controller;

// AbstractController : classe de base de tous les contrôleurs Symfony
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Response : objet représentant la réponse HTTP renvoyée au navigateur
use Symfony\Component\HttpFoundation\Response;

// Route : attribut pour associer une URL à une méthode du contrôleur
use Symfony\Component\Routing\Attribute\Route;

/**
 * Classe PostphotoController
 *
 * Gère les publications de photos (non encore développé).
 * "final" signifie que cette classe ne peut pas être héritée.
 */
final class PostphotoController extends AbstractController
{
    /**
     * Méthode index() — Page principale de la section PostPhoto
     *
     * ROUTE :
     *   - URL  : /postphoto
     *   - Nom  : app_postphoto
     *
     * PARAMÈTRES : aucun
     *
     * RETOUR : Response — la page HTML rendue depuis le template
     *          'postphoto/index.html.twig'
     */
    #[Route('/postphoto', name: 'app_postphoto')]
    public function index(): Response
    {
        // On affiche le template Twig de la section publication de photos.
        // La variable 'controller_name' est utilisée dans le template pour
        // identifier quel contrôleur a généré la page.
        return $this->render('postphoto/index.html.twig', [
            'controller_name' => 'PostphotoController',
        ]);
    }
}
