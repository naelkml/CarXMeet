<?php

/**
 * =============================================================================
 * CONTRÔLEUR DES CREWS - CrewController
 * =============================================================================
 *
 * CE QUE FAIT CE CONTRÔLEUR :
 * Ce contrôleur gère la section "crew" (équipe / groupe de voitures) de
 * l'application CarXMeet. Un "crew" est un groupe d'utilisateurs qui partagent
 * une passion commune pour un style ou une marque de voiture.
 *
 * ÉTAT ACTUEL :
 * Ce contrôleur est encore à l'état embryonnaire : seule la page d'accueil
 * (index) est définie. Les fonctionnalités de création, adhésion et gestion
 * de crews restent à implémenter.
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
 * Classe CrewController
 *
 * Gère les pages liées aux crews (groupes d'utilisateurs).
 * "final" indique que cette classe ne peut pas être héritée par d'autres classes.
 */
final class CrewController extends AbstractController
{
    /**
     * Méthode index() — Page principale de la section Crews
     *
     * ROUTE :
     *   - URL  : /crew
     *   - Nom  : app_crew
     *
     * PARAMÈTRES : aucun — la page ne nécessite pas de données en entrée
     *
     * RETOUR : Response — la page HTML rendue depuis le template 'crew/index.html.twig'
     *
     * NOTE : Pour afficher des crews réels, il faudrait injecter un CrewRepository
     * en paramètre, récupérer la liste des crews depuis la base de données,
     * et les passer au template.
     */
    #[Route('/crew', name: 'app_crew')]
    public function index(): Response
    {
        // On affiche le template Twig de la section crew.
        // La variable 'controller_name' est transmise au template pour identifier
        // quel contrôleur est utilisé (pratique pendant le développement).
        return $this->render('crew/index.html.twig', [
            'controller_name' => 'CrewController',
        ]);
    }
}
