<?php

namespace App\Repository;

use App\Entity\OrderStatus;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @extends AbstractRepository<OrderStatus>
 */
class OrderStatusRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        NormalizerInterface $normalizer
    ) {
        parent::__construct(
            $registry,
            OrderStatus::class,
            $normalizer
        );
    }

    public function getInitialStatus(): OrderStatus
    {
        /** @var null|OrderStatus $statusBySlug */
        $statusBySlug = $this->findOneBySlug('prinyat');
        /** @var null|OrderStatus $firstStatus */
        $firstStatus = $this->findOneByExternalId(1);

        $initialStatus = $statusBySlug ?: $firstStatus;
        if (!$initialStatus) {
            throw new \RuntimeException('Initial order status not set. Create at least one order status.');
        }

        return $initialStatus;
    }
}
