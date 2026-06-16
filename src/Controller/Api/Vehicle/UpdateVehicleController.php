<?php

namespace App\Controller\Api\Vehicle;

use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehiclePhoto;
use App\Service\Api\ApiJsonResponder;
use App\Service\Api\FormDataHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
final class UpdateVehicleController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ApiJsonResponder $responder,
    ) {
    }

    public function __invoke(Vehicle $vehicle, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $this->assertOwner($vehicle);

        if ($brand = FormDataHelper::getString($request, 'brand')) {
            $vehicle->setBrand($brand);
        }
        if ($model = FormDataHelper::getString($request, 'model')) {
            $vehicle->setModel($model);
        }
        if ($request->request->has('year')) {
            $vehicle->setYear(FormDataHelper::getString($request, 'year') ?? '0000');
        }
        if ($request->request->has('engine')) {
            $vehicle->setEngine(FormDataHelper::getString($request, 'engine') ?? '');
        }
        if ($request->request->has('preparation')) {
            $vehicle->setPreparation(FormDataHelper::getString($request, 'preparation') ?? '');
        }
        if ($request->request->has('description')) {
            $vehicle->setDescription(FormDataHelper::getString($request, 'description'));
        }

        $cover = FormDataHelper::getUploadedFiles($request, 'coverPhoto')[0] ?? null;
        if ($cover && $cover->isValid()) {
            $vehicle->setPhotos(file_get_contents($cover->getPathname()));
        }

        $existingCount = $vehicle->getGalleryPhotos()->count();
        $remaining = max(0, 5 - $existingCount);
        $uploads = array_filter(
            FormDataHelper::getUploadedFiles($request, 'galleryPhotos'),
            static fn ($f) => $f->isValid()
        );
        if (count($uploads) > $remaining) {
            throw new BadRequestHttpException(sprintf('Galerie: %d photo(s) maximum supplémentaire(s).', $remaining));
        }
        foreach ($uploads as $uploaded) {
            $photo = new VehiclePhoto();
            $photo->setPhoto(file_get_contents($uploaded->getPathname()));
            $vehicle->addGalleryPhoto($photo);
            $this->em->persist($photo);
        }

        $this->em->flush();

        return $this->responder->item($vehicle, Response::HTTP_OK, ['vehicle:read']);
    }

    private function assertOwner(Vehicle $vehicle): void
    {
        $user = $this->getUser();
        if (!$user instanceof User || $vehicle->getUserID()?->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException('Ce véhicule ne vous appartient pas.');
        }
    }
}
