<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Vehicle;
use App\Form\VehicleType;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

