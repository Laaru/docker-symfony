<?php

namespace App\Repository;

use App\Entity\OrderStatus;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OrderStatusRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly NormalizerInterface $normalizer
    ) {
        parent::__construct(
            $registry,
            OrderStatus::class,
            $normalizer
        );
    }

    public function getInitialStatus(): ?OrderStatus
    {
        return $this->findOneBySlug('prinyat') ?: $this->findOneByExternalId(1);
    }
}
