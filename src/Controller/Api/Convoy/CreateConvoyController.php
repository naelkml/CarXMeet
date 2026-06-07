<?php

namespace App\Controller\Api\Convoy;

use App\Entity\Convoy;
use App\Entity\Events;
use App\Entity\User;
use App\Service\Api\ApiJsonResponder;
use App\Service\Api\FormDataHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

#[AsController]
final class CreateConvoyController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ApiJsonResponder $responder,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->getUser() instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $payload = $this->decodePayload($request);

        $eventRaw = $payload['eventID'] ?? null;
        if (!is_string($eventRaw)) {
            throw new BadRequestHttpException('L\'événement est obligatoire.');
        }

        $eventId = FormDataHelper::resolveIriId($eventRaw);
        $event = $eventId ? $this->em->getRepository(Events::class)->find($eventId) : null;
        if (!$event) {
            throw new NotFoundHttpException('Événement introuvable.');
        }

        $departureLocation = is_string($payload['departureLocation'] ?? null)
            ? trim($payload['departureLocation'])
            : '';
        if ($departureLocation === '') {
            throw new BadRequestHttpException('Le lieu de départ est obligatoire.');
        }

        $convoy = new Convoy();
        $convoy->setEventID($event);
        $convoy->setDepartureLocation($departureLocation);
        $convoy->setDepartureDate(
            is_string($payload['departureDate'] ?? null) && $payload['departureDate'] !== ''
                ? $payload['departureDate']
                : null
        );
        $convoy->setDepartureTime(
            is_string($payload['departureTime'] ?? null) && $payload['departureTime'] !== ''
                ? $payload['departureTime']
                : '00:00'
        );

        $this->em->persist($convoy);
        $this->em->flush();

        return $this->responder->item($convoy, Response::HTTP_CREATED, ['convoy:read']);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodePayload(Request $request): array
    {
        $content = $request->getContent();
        if ($content === '') {
            return $request->request->all();
        }

        $decoded = (new JsonDecode([JsonDecode::ASSOCIATIVE => true]))->decode($content, JsonEncoder::FORMAT);
        return is_array($decoded) ? $decoded : [];
    }
}
