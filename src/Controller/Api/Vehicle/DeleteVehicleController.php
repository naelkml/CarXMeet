<?php

namespace App\Controller\Api\Vehicle;

use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

#[AsController]
final class DeleteVehicleController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function __invoke(Vehicle $vehicle): void
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User || $vehicle->getUserID()?->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException('Ce véhicule ne vous appartient pas.');
        }

        $this->em->remove($vehicle);
        $this->em->flush();
    }
}
