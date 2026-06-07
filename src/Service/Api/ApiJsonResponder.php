<?php

namespace App\Service\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

final class ApiJsonResponder
{
    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    public function item(mixed $data, int $status = Response::HTTP_OK, array $groups = []): JsonResponse
    {
        $context = ['groups' => $groups];

        return new JsonResponse(
            $this->serializer->serialize($data, 'json', $context),
            $status,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    public function empty(int $status = Response::HTTP_NO_CONTENT): Response
    {
        return new Response(null, $status);
    }
}
