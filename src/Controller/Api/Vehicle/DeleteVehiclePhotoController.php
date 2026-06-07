<?php

namespace App\Controller\Api\Vehicle;

use App\Entity\User;
use App\Entity\VehiclePhoto;
use App\Service\Api\ApiJsonResponder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[AsController]
final class DeleteVehiclePhotoController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ApiJsonResponder $responder,
    ) {
    }

    public function __invoke(VehiclePhoto $photo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $vehicle = $photo->getVehicleID();
        if (!$user instanceof User || !$vehicle || $vehicle->getUserID()?->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException('Cette photo ne vous appartient pas.');
        }

        $this->em->remove($photo);
        $this->em->flush();

        return $this->responder->empty();
    }
}
