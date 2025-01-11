<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @extends AbstractRepository<Order>
 */
class OrderRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        NormalizerInterface $normalizer
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

    public function getAllOrdersViaGenerator(int $chunkSize): \Generator
    {
        $lastId = 0;
        while (true) {
            $orders = $this->createQueryBuilder('o')
                ->where('o.id > :lastId')
                ->setParameter('lastId', $lastId)
                ->orderBy('o.id', 'ASC')
                ->setMaxResults($chunkSize)
                ->getQuery()
                ->getResult();

            if (empty($orders)) {
                break;
            }

            $lastId = end($orders)->getId();

            yield $orders;
        }
    }
}
