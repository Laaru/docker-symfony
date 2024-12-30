<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @extends AbstractRepository<User>
 */
class UserRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry,
        NormalizerInterface $normalizer
    ) {
        parent::__construct(
            $registry,
            User::class,
            $normalizer
        );
    }
}
