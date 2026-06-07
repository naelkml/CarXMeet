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
final class CreateEventController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function __invoke(Request $request): Events
    {
        $this->denyAccessUnlessGranted('ROLE_EVENT_MANAGER');

        $title = FormDataHelper::getString($request, 'title');
        if (!$title) {
            throw new BadRequestHttpException('Le titre est obligatoire.');
        }

        $event = new Events();
        $event->setTitle($title);
        $event->setDescription(FormDataHelper::getString($request, 'description') ?? '');
        $event->setType(FormDataHelper::getString($request, 'type') ?? '');
        $event->setDate(FormDataHelper::getString($request, 'Date') ?? '');
        $event->setLocation(FormDataHelper::getString($request, 'location') ?? '');
        $event->setOrganisateur(FormDataHelper::getString($request, 'organisateur') ?? '');
        $event->setCreatedAt(new \DateTimeImmutable());
        $event->setRatingAverage('0');
        $event->setGallery(null);

        $regionId = FormDataHelper::resolveIriId(FormDataHelper::getString($request, 'regionID'));
        if ($regionId) {
            $region = $this->em->getRepository(Region::class)->find($regionId);
            if (!$region) {
                throw new NotFoundHttpException('Région introuvable.');
            }
            $event->setRegionID($region);
        }

        $cover = FormDataHelper::getUploadedFiles($request, 'coverPhoto')[0] ?? null;
        if ($cover) {
            $event->setCoverPhoto(file_get_contents($cover->getPathname()));
        }

        $this->attachGalleryPhotos($event, $request, 8);

        $this->em->persist($event);
        $this->em->flush();

        return $event;
    }

    private function attachGalleryPhotos(Events $event, Request $request, int $max): void
    {
        $uploads = FormDataHelper::getUploadedFiles($request, 'galleryPhotos');
        if (count($uploads) > $max) {
            throw new BadRequestHttpException(sprintf('Galerie: %d photos maximum.', $max));
        }

        foreach ($uploads as $uploaded) {
            $photo = new EventPhoto();
            $photo->setPhoto(file_get_contents($uploaded->getPathname()));
            $event->addGalleryPhoto($photo);
            $this->em->persist($photo);
        }
    }
}
