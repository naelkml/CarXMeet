<?php

namespace App\Controller\Api\Event;

use App\Entity\EventPhoto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class DeleteEventPhotoController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function __invoke(EventPhoto $photo): void
    {
        $this->denyAccessUnlessGranted('ROLE_EVENT_MANAGER');

        $this->em->remove($photo);
        $this->em->flush();
    }
}
