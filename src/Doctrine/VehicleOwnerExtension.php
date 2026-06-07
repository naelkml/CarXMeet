<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Vehicle;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

final class VehicleOwnerExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if ($resourceClass !== Vehicle::class) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $ownerId = $request->query->get('ownerID') ?? $request->query->get('userID');
        if (!is_string($ownerId) && !is_int($ownerId)) {
            return;
        }
        if (!ctype_digit((string) $ownerId)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(sprintf('%s.userID = :ownerId', $rootAlias))
            ->setParameter('ownerId', (int) $ownerId);
    }
}
