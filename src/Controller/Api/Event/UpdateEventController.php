<?php

namespace App\Controller\Api\Event;

use App\Entity\EventPhoto;
use App\Entity\Events;
use App\Entity\Region;
use App\Service\Api\FormDataHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsController]
final class UpdateEventController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function __invoke(Events $event, Request $request): Events
    {
        $this->denyAccessUnlessGranted('ROLE_EVENT_MANAGER');

        if ($title = FormDataHelper::getString($request, 'title')) {
            $event->setTitle($title);
        }
        if ($request->request->has('description')) {
            $event->setDescription(FormDataHelper::getString($request, 'description') ?? '');
        }
        if ($request->request->has('type')) {
            $event->setType(FormDataHelper::getString($request, 'type') ?? '');
        }
        if ($request->request->has('Date')) {
            $event->setDate(FormDataHelper::getString($request, 'Date') ?? '');
        }
        if ($request->request->has('location')) {
            $event->setLocation(FormDataHelper::getString($request, 'location') ?? '');
        }
        if ($request->request->has('organisateur')) {
            $event->setOrganisateur(FormDataHelper::getString($request, 'organisateur') ?? '');
        }

        if ($request->request->has('regionID')) {
            $regionRaw = FormDataHelper::getString($request, 'regionID');
            if (!$regionRaw) {
                $event->setRegionID(null);
            } else {
                $regionId = FormDataHelper::resolveIriId($regionRaw);
                $region = $regionId ? $this->em->getRepository(Region::class)->find($regionId) : null;
                if (!$region) {
                    throw new NotFoundHttpException('Région introuvable.');
                }
                $event->setRegionID($region);
            }
        }

        $cover = FormDataHelper::getUploadedFiles($request, 'coverPhoto')[0] ?? null;
        if ($cover) {
            $event->setCoverPhoto(file_get_contents($cover->getPathname()));
        }

        $existingCount = $event->getGalleryPhotos()->count();
        $remaining = max(0, 8 - $existingCount);
        $uploads = FormDataHelper::getUploadedFiles($request, 'galleryPhotos');
        if (count($uploads) > $remaining) {
            throw new BadRequestHttpException(sprintf('Galerie: %d photo(s) maximum supplémentaire(s).', $remaining));
        }
        foreach ($uploads as $uploaded) {
            $photo = new EventPhoto();
            $photo->setPhoto(file_get_contents($uploaded->getPathname()));
            $event->addGalleryPhoto($photo);
            $this->em->persist($photo);
        }

        if (!$event->getRatingAverage()) {
            $event->setRatingAverage('0');
        }

        $this->em->flush();

        return $event;
    }
}
