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
final class CreateVehicleController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ApiJsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $brand = FormDataHelper::getString($request, 'brand');
        $model = FormDataHelper::getString($request, 'model');
        if (!$brand || !$model) {
            throw new BadRequestHttpException('La marque et le modèle sont obligatoires.');
        }

        $ownerRaw = FormDataHelper::getString($request, 'ownerID') ?? FormDataHelper::getString($request, 'userID');
        $ownerId = FormDataHelper::resolveIriId($ownerRaw) ?? $user->getId();
        if ($ownerId !== $user->getId()) {
            throw new AccessDeniedHttpException('Tu ne peux ajouter un véhicule qu\'à ton propre garage.');
        }

        $vehicle = new Vehicle();
        $vehicle->setUserID($user);
        $vehicle->setBrand($brand);
        $vehicle->setModel($model);
        $vehicle->setYear(FormDataHelper::getString($request, 'year') ?? '0000');
        $vehicle->setEngine(FormDataHelper::getString($request, 'engine') ?? '');
        $vehicle->setPreparation(FormDataHelper::getString($request, 'preparation') ?? '');
        $vehicle->setDescription(FormDataHelper::getString($request, 'description'));

        $cover = FormDataHelper::getUploadedFiles($request, 'coverPhoto')[0] ?? null;
        if ($cover) {
            $vehicle->setPhotos(file_get_contents($cover->getPathname()));
        }

        $uploads = FormDataHelper::getUploadedFiles($request, 'galleryPhotos');
        if (count($uploads) > 5) {
            throw new BadRequestHttpException('Galerie: 5 photos maximum.');
        }
        foreach ($uploads as $uploaded) {
            $photo = new VehiclePhoto();
            $photo->setPhoto(file_get_contents($uploaded->getPathname()));
            $vehicle->addGalleryPhoto($photo);
            $this->em->persist($photo);
        }

        $this->em->persist($vehicle);
        $this->em->flush();

        return $this->responder->item($vehicle, Response::HTTP_CREATED, ['vehicle:read']);
    }
}
