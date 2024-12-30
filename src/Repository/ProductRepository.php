<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @extends AbstractRepository<Product>
 */
class ProductRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        NormalizerInterface $normalizer
    ) {
        parent::__construct(
            $registry,
            Product::class,
            $normalizer
        );
    }

    public function getRandomProduct(): ?Product
    {
        try {
            $conn = $this->getEntityManager()->getConnection();
            $sql = 'SELECT id FROM product ORDER BY RANDOM() LIMIT 1';
            $stmt = $conn->prepare($sql);
            $resultSet = $stmt->executeQuery();
            $data = $resultSet->fetchAssociative();

            if ($data && isset($data['id'])) {
                /** @var Product $product */
                $product = $this->find($data['id']);

                return $product;
            }
        } catch (DbalException $e) {
        }

        return null;
    }

    public function getMinPrice(): ?int
    {
        $result = $this->createQueryBuilder('p')
            ->select('MIN(p.basePrice)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($result) {
            return (int) $result;
        }

        return null;

    }

    public function getMaxPrice(): ?int
    {
        $result = $this->createQueryBuilder('p')
            ->select('MAX(p.basePrice)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($result) {
            return (int) $result;
        }

        return null;

    }

    /**
     * @return array<int, string>
     */
    public function getAvailableColors(): array
    {
        $result = $this->createQueryBuilder('p')
            ->select('c.id, c.name')
            ->join('p.color', 'c')
            ->getQuery()
            ->getResult();

        $colors = [];
        foreach ($result as $color) {
            $colors[$color['id']] = $color['name'];
        }

        return $colors;
    }

    /**
     * @return array<int, string>
     */
    public function getAvailableStores(): array
    {
        $result = $this->createQueryBuilder('p')
            ->select('s.id, s.name')
            ->join('p.stores', 's')
            ->getQuery()
            ->getResult();

        $stores = [];
        foreach ($result as $store) {
            $stores[$store['id']] = $store['name'];
        }

        return $stores;
    }
}
