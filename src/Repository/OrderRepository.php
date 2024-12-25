<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OrderRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly NormalizerInterface $normalizer
    ) {
        parent::__construct(
            $registry,
            Order::class,
            $normalizer
        );
    }

    public function getLastOrder(): ?Order
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
