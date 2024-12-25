<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly NormalizerInterface $normalizer
    ) {
        parent::__construct(
            $registry,
            User::class,
            $normalizer
        );
    }
}
