<?php

namespace App\Controller;

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

final class VehicleController extends AbstractController
{
    #[Route('/garage', name: 'app_garage', methods: ['GET', 'POST'])]
    public function garage(Request $request, VehicleRepository $vehicleRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        $vehicle = new Vehicle();
        $vehicle->setUserID($user);

        $form = $this->createForm(VehicleType::class, $vehicle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverPhoto = $form->get('coverPhoto')->getData();
            if ($coverPhoto) {
                $imageData = file_get_contents($coverPhoto->getPathname());
                $vehicle->setPhotos($imageData);
            }

            $galleryUploads = $form->get('galleryPhotos')->getData();
            if (is_array($galleryUploads) && count($galleryUploads) > 5) {
                $form->get('galleryPhotos')->addError(new FormError('Galerie: 5 photos maximum.'));
            }

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

            if (!$form->isValid()) {
                return $this->render('vehicle/garage.html.twig', [
                    'vehicles' => $vehicleRepository->findBy(['userID' => $user], ['id' => 'DESC']),
                    'form' => $form->createView(),
                ]);
            }

            $em->persist($vehicle);
            $em->flush();

            $this->addFlash('success', 'Véhicule ajouté.');
            return $this->redirectToRoute('app_garage');
        }

        return $this->render('vehicle/garage.html.twig', [
            'vehicles' => $vehicleRepository->findBy(['userID' => $user], ['id' => 'DESC']),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/garage/{id}', name: 'app_garage_vehicle_show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(Vehicle $vehicle, FriendshipRepository $friendshipRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        $owner = $vehicle->getUserID();
        $isOwner = $owner?->getId() === $user->getId();
        $isFriend = $owner instanceof User && $friendshipRepository->areFriends($user, $owner);

        if (!$isOwner && !$isFriend) {
            throw $this->createAccessDeniedException('Ce véhicule ne vous appartient pas.');
        }

        return $this->render('vehicle/show.html.twig', [
            'vehicle' => $vehicle,
            'isOwner' => $isOwner,
        ]);
    }

    #[Route('/garage/{id}/edit', name: 'app_garage_vehicle_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Vehicle $vehicle, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        if ($vehicle->getUserID()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Ce véhicule ne vous appartient pas.');
        }

        $form = $this->createForm(VehicleType::class, $vehicle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverPhoto = $form->get('coverPhoto')->getData();
            if ($coverPhoto) {
                $vehicle->setPhotos(file_get_contents($coverPhoto->getPathname()));
            }

            $existingCount = $vehicle->getGalleryPhotos()->count();
            $remaining = max(0, 5 - $existingCount);

            $galleryUploads = $form->get('galleryPhotos')->getData();
            if (is_array($galleryUploads) && count($galleryUploads) > $remaining) {
                $form->get('galleryPhotos')->addError(new FormError(sprintf('Galerie: %d photo(s) maximum supplémentaire(s).', $remaining)));
            }

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

            if ($form->isValid()) {
                $em->flush();
                $this->addFlash('success', 'Véhicule modifié.');
                return $this->redirectToRoute('app_garage_vehicle_show', ['id' => $vehicle->getId()]);
            }
        }

        return $this->render('vehicle/edit.html.twig', [
            'vehicle' => $vehicle,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/garage/{id}/photos/{photo}/delete', name: 'app_garage_vehicle_photo_delete', requirements: ['id' => '\\d+', 'photo' => '\\d+'], methods: ['POST'])]
    public function deletePhoto(Request $request, Vehicle $vehicle, VehiclePhoto $photo, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        if ($vehicle->getUserID()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Ce véhicule ne vous appartient pas.');
        }

        if ($photo->getVehicleID()?->getId() !== $vehicle->getId()) {
            throw $this->createNotFoundException('Photo introuvable.');
        }

        if (!$this->isCsrfTokenValid('delete_vehicle_photo_' . $photo->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_garage_vehicle_edit', ['id' => $vehicle->getId()]);
        }

        $em->remove($photo);
        $em->flush();

        $this->addFlash('success', 'Photo supprimée.');
        return $this->redirectToRoute('app_garage_vehicle_edit', ['id' => $vehicle->getId()]);
    }

    #[Route('/garage/{id}/delete', name: 'app_garage_vehicle_delete', methods: ['POST'])]
    public function delete(Request $request, Vehicle $vehicle, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('security.login');
        }

        if ($vehicle->getUserID()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Ce véhicule ne vous appartient pas.');
        }

        if (!$this->isCsrfTokenValid('delete_vehicle_' . $vehicle->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_garage');
        }

        $em->remove($vehicle);
        $em->flush();

        $this->addFlash('success', 'Véhicule supprimé.');
        return $this->redirectToRoute('app_garage');
    }
}
