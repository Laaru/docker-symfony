<?php

namespace App\Repository;

use App\Entity\Color;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ColorRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly NormalizerInterface $normalizer
    ) {
        parent::__construct(
            $registry,
            Color::class,
            $normalizer
        );
    }
}
