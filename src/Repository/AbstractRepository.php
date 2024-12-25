<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class AbstractRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        private readonly NormalizerInterface $normalizer
    ) {
        parent::__construct($registry, $entityClass);
    }

    public function normalize(object $entity, string $scope = 'detail')
    {
        return $this->normalizer->normalize(
            $entity,
            null,
            ['groups' => $scope]
        );
    }

    public function normalizeCollection(iterable $entities, string $scope = 'detail'): array
    {
        $normalizedData = [];

        foreach ($entities as $entity) {
            $normalizedData[] = $this->normalize($entity, $scope);
        }

        return $normalizedData;
    }

    public function findOneBySlug(string $slug): ?object
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.slug) = LOWER(:slug)')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByExternalId(int $externalId): ?object
    {
        return $this->findOneBy(['externalId' => $externalId]);
    }

    public function findManyByExternalIds(array $externalIds): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.externalId IN (:externalIds)')
            ->setParameter('externalIds', $externalIds)
            ->getQuery()
            ->getResult();
    }
}
