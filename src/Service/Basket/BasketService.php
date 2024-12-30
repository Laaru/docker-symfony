<?php

namespace App\Service\Basket;

use App\Entity\Basket;
use App\Entity\User;
use App\Repository\BasketRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class BasketService
{
    public function __construct(
        private BasketRepository $basketRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function getUserBasket(User $user): Basket
    {
        /** @var Basket $basket */
        $basket = $this->basketRepository->findOneBy(['userRelation' => $user]);
        if (!$basket) {
            $basket = new Basket();
            $basket->setUserRelation($user);
            $this->entityManager->persist($basket);
            $this->entityManager->flush();
        }

        return $basket;
    }
}
