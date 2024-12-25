<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly NormalizerInterface $normalizer
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

            return $this->find($data['id']);
        } catch (DbalException $e) {
            return null;
        }
    }
}
