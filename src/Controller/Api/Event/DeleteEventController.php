<?php

namespace App\Controller\Api\Event;

use App\Entity\Events;
use App\Service\Api\ApiJsonResponder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsController]
final class DeleteEventController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ApiJsonResponder $responder,
    ) {
    }

    public function __invoke(Events $event): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_EVENT_MANAGER');

        if ($event->getOrganisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $this->em->remove($event);
        $this->em->flush();

        return $this->responder->success([
            'message' => 'Event supprimé'
        ]);
    }
}
