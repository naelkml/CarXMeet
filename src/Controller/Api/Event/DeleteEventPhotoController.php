<?php

namespace App\Controller\Api\Event;

use App\Entity\EventPhoto;
use App\Service\Api\ApiJsonResponder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class DeleteEventPhotoController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ApiJsonResponder $responder,
    ) {
    }

    public function __invoke(EventPhoto $photo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EVENT_MANAGER');

        $this->em->remove($photo);
        $this->em->flush();

        return $this->responder->empty();
    }
}
