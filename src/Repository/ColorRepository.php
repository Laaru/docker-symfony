<?php

namespace App\Repository;

use App\Entity\Color;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @extends AbstractRepository<Color>
 */
class ColorRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        NormalizerInterface $normalizer
    ) {
        parent::__construct(
            $registry,
            Color::class,
            $normalizer
        );
    }
}
