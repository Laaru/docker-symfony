<?php

namespace App\Repository;

use App\Entity\Store;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class StoreRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly NormalizerInterface $normalizer
    ) {
        parent::__construct(
            $registry,
            Store::class,
            $normalizer
        );
    }
}
