<?php

namespace App\Repository;

use App\Entity\Basket;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BasketRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly NormalizerInterface $normalizer
    ) {
        parent::__construct(
            $registry,
            Basket::class,
            $normalizer
        );
    }
}
