<?php

namespace App\Controller;

// Importation des classes nécessaires au fonctionnement du contrôleur
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehiclePhoto;
use App\Form\VehicleType;
use App\Repository\FriendshipRepository;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Contrôleur permettant de gérer les véhicules du garage utilisateur
final class VehicleController extends AbstractController
{
    /**
     * Affiche le garage de l'utilisateur et permet d'ajouter un véhicule.
     *
     * Route accessible depuis /garage.
     * Accepte les requêtes GET (affichage) et POST (envoi du formulaire).
     */
    #[Route('/garage', name: 'app_garage', methods: ['GET', 'POST'])]
    public function garage(Request $request, VehicleRepository $vehicleRepository, EntityManagerInterface $em): Response
    {
        // Vérifie que l'utilisateur connecté possède bien le rôle utilisateur
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Récupération de l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Sécurité : vérifie que l'utilisateur est bien une instance de User
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        // Création d'un nouveau véhicule vide
        $vehicle = new Vehicle();

        // Association du véhicule avec l'utilisateur connecté
        $vehicle->setUserID($user);

        // Création du formulaire basé sur VehicleType
        $form = $this->createForm(VehicleType::class, $vehicle);

        // Récupération des données envoyées par l'utilisateur
        $form->handleRequest($request);

        // Vérifie que le formulaire a été envoyé et qu'il est valide
        if ($form->isSubmitted() && $form->isValid()) {

            // Récupération de la photo principale du véhicule
            $coverPhoto = $form->get('coverPhoto')->getData();

            // Si une image existe, elle est convertie en données binaires
            if ($coverPhoto) {
                $imageData = file_get_contents($coverPhoto->getPathname());
                $vehicle->setPhotos($imageData);
            }

            // Récupération des photos supplémentaires
            $galleryUploads = $form->get('galleryPhotos')->getData();

            // Vérifie que la galerie ne dépasse pas 5 photos
            if (is_array($galleryUploads) && count($galleryUploads) > 5) {
                $form->get('galleryPhotos')->addError(new FormError('Galerie: 5 photos maximum.'));
            }

            // Si les photos sont valides, elles sont enregistrées une par une
            if ($form->isValid() && is_array($galleryUploads)) {

                foreach ($galleryUploads as $uploaded) {

                    // Ignore les fichiers vides
                    if (!$uploaded) {
                        continue;
                    }

                    // Création d'une nouvelle entité photo
                    $photo = new VehiclePhoto();

                    // Stockage de l'image dans la base de données
                    $photo->setPhoto(file_get_contents($uploaded->getPathname()));

                    // Association de la photo au véhicule
                    $vehicle->addGalleryPhoto($photo);

                    // Préparation de l'enregistrement en base
                    $em->persist($photo);
                }
            }

            // Si une erreur est apparue dans le formulaire, on réaffiche la page
            if (!$form->isValid()) {
                return $this->render('vehicle/garage.html.twig', [
                    'vehicles' => $vehicleRepository->findBy(['userID' => $user], ['id' => 'DESC']),
                    'form' => $form->createView(),
                ]);
            }

            // Préparation de l'enregistrement du véhicule
            $em->persist($vehicle);

            // Exécution des requêtes SQL vers la base de données
            $em->flush();

            // Message affiché après l'ajout réussi
            $this->addFlash('success', 'Véhicule ajouté.');

            // Retour vers le garage
            return $this->redirectToRoute('app_garage');
        }

        // Affichage du garage avec les véhicules existants et le formulaire
        return $this->render('vehicle/garage.html.twig', [
            'vehicles' => $vehicleRepository->findBy(['userID' => $user], ['id' => 'DESC']),
            'form' => $form->createView(),
        ]);
    }


    /**
     * Affiche les détails d'un véhicule.
     * Accessible uniquement au propriétaire ou à ses amis.
     */
    #[Route('/garage/{id}', name: 'app_garage_vehicle_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(Vehicle $vehicle, FriendshipRepository $friendshipRepository): Response
    {
        // Vérification de connexion
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Récupération de l'utilisateur connecté
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        // Récupération du propriétaire du véhicule
        $owner = $vehicle->getUserID();

        // Vérifie si l'utilisateur est le propriétaire
        $isOwner = $owner?->getId() === $user->getId();

        // Vérifie si l'utilisateur est un ami du propriétaire
        $isFriend = $owner instanceof User && $friendshipRepository->areFriends($user, $owner);

        // Bloque l'accès si l'utilisateur n'est ni propriétaire ni ami
        if (!$isOwner && !$isFriend) {
            throw $this->createAccessDeniedException('Ce véhicule ne vous appartient pas.');
        }

        // Affichage de la page du véhicule
        return $this->render('vehicle/show.html.twig', [
            'vehicle' => $vehicle,
            'isOwner' => $isOwner,
        ]);
    }


    /**
     * Permet au propriétaire de modifier son véhicule.
     */
    #[Route('/garage/{id}/edit', name: 'app_garage_vehicle_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Vehicle $vehicle, EntityManagerInterface $em): Response
    {
        // Vérification utilisateur connecté
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        // Vérifie que le véhicule appartient bien à l'utilisateur
        if ($vehicle->getUserID()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Ce véhicule ne vous appartient pas.');
        }

        // Création du formulaire avec les données actuelles du véhicule
        $form = $this->createForm(VehicleType::class, $vehicle);

        // Traitement du formulaire envoyé
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Mise à jour de la photo principale si une nouvelle est envoyée
            $coverPhoto = $form->get('coverPhoto')->getData();

            if ($coverPhoto) {
                $vehicle->setPhotos(file_get_contents($coverPhoto->getPathname()));
            }

            // Nombre de photos déjà présentes
            $existingCount = $vehicle->getGalleryPhotos()->count();

            // Nombre de photos encore autorisées
            $remaining = max(0, 5 - $existingCount);

            // Photos ajoutées
            $galleryUploads = $form->get('galleryPhotos')->getData();


            // Vérification de la limite maximale
            if (is_array($galleryUploads) && count($galleryUploads) > $remaining) {
                $form->get('galleryPhotos')->addError(
                    new FormError(sprintf('Galerie: %d photo(s) maximum supplémentaire(s).', $remaining))
                );
            }


            // Ajout des nouvelles photos
            if ($form->isValid() && is_array($galleryUploads)) {

                foreach ($galleryUploads as $uploaded) {

                    if (!$uploaded) {
                        continue;
                    }

                    $photo = new VehiclePhoto();

                    $photo->setPhoto(file_get_contents($uploaded->getPathname()));

                    $vehicle->addGalleryPhoto($photo);

                    $em->persist($photo);
                }
            }


            // Enregistrement des modifications
            if ($form->isValid()) {

                $em->flush();

                $this->addFlash('success', 'Véhicule modifié.');

                return $this->redirectToRoute('app_garage_vehicle_show', [
                    'id' => $vehicle->getId()
                ]);
            }
        }

        // Affichage du formulaire de modification
        return $this->render('vehicle/edit.html.twig', [
            'vehicle' => $vehicle,
            'form' => $form->createView(),
        ]);
    }


    /**
     * Supprime une photo de galerie.
     */
    #[Route('/garage/{id}/photos/{photo}/delete', name: 'app_garage_vehicle_photo_delete', requirements: ['id' => '\\d+', 'photo' => '\\d+'], methods: ['POST'])]
    public function deletePhoto(Request $request, Vehicle $vehicle, VehiclePhoto $photo, EntityManagerInterface $em): Response
    {
        // Vérifie que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        // Vérifie que le véhicule appartient à l'utilisateur
        if ($vehicle->getUserID()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Ce véhicule ne vous appartient pas.');
        }

        // Vérifie que la photo appartient bien au véhicule
        if ($photo->getVehicleID()?->getId() !== $vehicle->getId()) {
            throw $this->createNotFoundException('Photo introuvable.');
        }

        // Protection contre les attaques CSRF
        if (!$this->isCsrfTokenValid('delete_vehicle_photo_' . $photo->getId(), (string) $request->request->get('_token'))) {

            $this->addFlash('error', 'Token CSRF invalide.');

            return $this->redirectToRoute('app_garage_vehicle_edit', [
                'id' => $vehicle->getId()
            ]);
        }

        // Suppression de la photo en base
        $em->remove($photo);

        // Application de la suppression
        $em->flush();

        $this->addFlash('success', 'Photo supprimée.');

        return $this->redirectToRoute('app_garage_vehicle_edit', [
            'id' => $vehicle->getId()
        ]);
    }


    /**
     * Supprime complètement un véhicule.
     */
    #[Route('/garage/{id}/delete', name: 'app_garage_vehicle_delete', methods: ['POST'])]
    public function delete(Request $request, Vehicle $vehicle, EntityManagerInterface $em): Response
    {
        // Vérification utilisateur connecté
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        // Vérifie que le véhicule appartient à l'utilisateur
        if ($vehicle->getUserID()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Ce véhicule ne vous appartient pas.');
        }

        // Vérification du token CSRF avant suppression
        if (!$this->isCsrfTokenValid('delete_vehicle_' . $vehicle->getId(), (string) $request->request->get('_token'))) {

            $this->addFlash('error', 'Token CSRF invalide.');

            return $this->redirectToRoute('app_garage');
        }

        // Suppression du véhicule
        $em->remove($vehicle);

        // Validation de la suppression en base de données
        $em->flush();

        $this->addFlash('success', 'Véhicule supprimé.');

        return $this->redirectToRoute('app_garage');
    }
}
