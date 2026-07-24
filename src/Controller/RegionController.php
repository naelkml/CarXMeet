<?php

/**
 * =============================================================================
 * CONTRÔLEUR DES RÉGIONS - RegionController
 * =============================================================================
 *
 * CE QUE FAIT CE CONTRÔLEUR :
 * Ce contrôleur gère l'affichage de la liste des régions géographiques
 * disponibles dans l'application CarXMeet. Les régions sont utilisées pour
 * filtrer les événements par zone géographique.
 *
 * FONCTIONNALITÉS :
 *  - Afficher toutes les régions disponibles, triées par ordre alphabétique
 *
 * SÉCURITÉ :
 *  - La page est accessible uniquement aux utilisateurs connectés (ROLE_USER)
 * =============================================================================
 */

namespace App\Controller;

// RegionRepository : permet de récupérer des régions depuis la base de données
use App\Repository\RegionRepository;

// AbstractController : classe de base de tous les contrôleurs Symfony
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Response : objet représentant la réponse HTTP renvoyée au navigateur
use Symfony\Component\HttpFoundation\Response;

// Route : attribut pour définir l'URL qui déclenchera cette méthode
use Symfony\Component\Routing\Attribute\Route;

/**
 * Classe RegionController
 *
 * Gère l'affichage des régions géographiques.
 * "final" indique que cette classe ne peut pas être héritée.
 */
final class RegionController extends AbstractController
{
    /**
     * Méthode index() — Liste de toutes les régions
     *
     * ROUTE :
     *   - URL     : /regions
     *   - Nom     : app_regions
     *   - Méthode : GET uniquement (on consulte une liste, on ne modifie rien)
     *
     * PARAMÈTRES (injectés automatiquement par Symfony) :
     * @param RegionRepository $regionRepository  Le repository Doctrine pour
     *                                             accéder aux régions en BDD.
     *                                             Un "repository" est un objet
     *                                             spécialisé dans la récupération
     *                                             de données d'un type donné.
     *
     * RETOUR : Response — une page HTML avec la liste des régions
     *
     * SÉCURITÉ :
     * denyAccessUnlessGranted('ROLE_USER') bloque la page si l'utilisateur n'est
     * pas connecté. Symfony lève alors une exception d'accès refusé (403).
     */
    #[Route('/regions', name: 'app_regions', methods: ['GET'])]
    public function index(RegionRepository $regionRepository): Response
    {
        // Vérification d'authentification : seuls les utilisateurs connectés
        // avec le rôle ROLE_USER peuvent accéder à cette page.
        // Si ce n'est pas le cas, Symfony redirige vers la page de connexion.
        $this->denyAccessUnlessGranted('ROLE_USER');

        // On affiche le template Twig en lui passant la liste des régions.
        // findBy([], ['name' => 'ASC']) récupère toutes les régions ([] = sans filtre)
        // et les trie par nom dans l'ordre alphabétique croissant (ASC = ascending).
        return $this->render('region/index.html.twig', [
            'regions' => $regionRepository->findBy([], ['name' => 'ASC']),
        ]);
    }
}
