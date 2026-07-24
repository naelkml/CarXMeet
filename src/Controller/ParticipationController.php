<?php

/**
 * =============================================================================
 * CONTRÔLEUR DES PARTICIPATIONS - ParticipationController
 * =============================================================================
 *
 * CE QUE FAIT CE CONTRÔLEUR :
 * Ce contrôleur est prévu pour gérer les participations des utilisateurs
 * aux événements CarXMeet.
 *
 * REMARQUE IMPORTANTE :
 * La logique principale de participation (rejoindre / quitter un événement)
 * est déjà implémentée directement dans EventsController.php
 * (méthodes participate() et leave()).
 *
 * Ce contrôleur est encore vide et ne contient qu'une page d'accueil basique.
 * Il pourrait à l'avenir servir à afficher la liste de toutes les participations
 * d'un utilisateur.
 * =============================================================================
 */

namespace App\Controller;

// AbstractController : classe de base de tous les contrôleurs Symfony
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Response : objet représentant la réponse HTTP renvoyée au navigateur
use Symfony\Component\HttpFoundation\Response;

// Route : attribut pour définir l'URL qui déclenchera cette méthode
use Symfony\Component\Routing\Attribute\Route;

/**
 * Classe ParticipationController
 *
 * Contrôleur dédié aux participations (actuellement non développé).
 * "final" signifie que cette classe ne peut pas être héritée.
 */
final class ParticipationController extends AbstractController
{
    /**
     * Méthode index() — Page principale de la section Participations
     *
     * ROUTE :
     *   - URL  : /participation
     *   - Nom  : app_participation
     *
     * PARAMÈTRES : aucun
     *
     * RETOUR : Response — la page HTML rendue depuis le template
     *          'participation/index.html.twig'
     */
    #[Route('/participation', name: 'app_participation')]
    public function index(): Response
    {
        // Affiche le template Twig de la section participation.
        // 'controller_name' est passé pour identifier le contrôleur dans le template.
        return $this->render('participation/index.html.twig', [
            'controller_name' => 'ParticipationController',
        ]);
    }
}
