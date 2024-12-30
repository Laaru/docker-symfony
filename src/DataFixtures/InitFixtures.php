<?php

namespace App\DataFixtures;

use App\Entity\Basket;
use App\Entity\Color;
use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Entity\Product;
use App\Entity\Store;
use App\Entity\User;
use Cocur\Slugify\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class InitFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $slugify = new Slugify();

        /** Users */
        $user = new User();
        $user->setFirstName('admin');
        $user->setEmail('admin');
        $user->setPhone('admin');
        $user->addRole('ROLE_ADMIN');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $manager->persist($user);

        $user = new User();
        $user->setFirstName('manager');
        $user->setEmail('manager');
        $user->setPhone('manager');
        $user->addRole('ROLE_MANAGER');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $manager->persist($user);

        $user = new User();
        $user->setFirstName('external-api');
        $user->setEmail('external-api');
        $user->setPhone('external-api');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $manager->persist($user);

        $users = [];
        for ($i = 1; $i <= 30; ++$i) {
            $user = new User();
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setEmail($faker->email());
            $user->setPhone($faker->phoneNumber());
            $user->addRole('ROLE_USER');
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

            $manager->persist($user);
            $users[] = $user;
        }

        /** Product colors */
        $colors = [];
        for ($i = 1; $i <= 10; ++$i) {
            $color = new Color();
            $name = $faker->unique()->colorName();
            $color->setName($name);
            $color->setExternalId($i);
            $color->setSlug($slugify->slugify($name));
            $manager->persist($color);
            $colors[] = $color;
        }

        /** Stores */
        $stores = [];
        for ($i = 1; $i <= 20; ++$i) {
            $store = new Store();
            $name = $faker->company();
            $store->setName($name);
            $store->setSlug($slugify->slugify($name));
            $store->setExternalId($i);
            $store->setAddress($faker->address());
            $manager->persist($store);
            $stores[] = $store;
        }

        /** Products */
        $products = [];
        for ($i = 1; $i <= 1000; ++$i) {
            $product = new Product();
            $name = $faker->sentence(mt_rand(1, 2));
            $externalId = $i;
            $product->setName($name);
            $product->setSlug($slugify->slugify($name) . '-' . $externalId);
            $product->setBasePrice(mt_rand(100, 50000));
            $product->setSalePrice($faker->optional()->numberBetween(50, 9000));
            $product->setDescription($faker->paragraph());
            $product->setColor($faker->randomElement($colors));
            $product->setExternalId($externalId);
            $product->setVersion(mt_rand(1, 5));
            $product->setHeight(mt_rand(10, 1000));
            $product->setLength(mt_rand(10, 1000));
            $product->setWidth(mt_rand(10, 1000));
            $product->setWeight(mt_rand(50, 5000));
            $product->setTax($faker->randomElement([0, 12, 20]));

            $assignedStores = $faker->randomElements($stores, mt_rand(1, 5));
            foreach ($assignedStores as $store) {
                $product->addInStockInStore($store);
            }

            $manager->persist($product);
            $products[] = $product;
        }

        /** Order statuses */
        $orderStatuses = [];

        $orderStatus = new OrderStatus();
        $name = 'принят';
        $orderStatus->setExternalId(1);
        $orderStatus->setName($name);
        $orderStatus->setSlug($slugify->slugify($name));
        $manager->persist($orderStatus);
        $orderStatuses[] = $orderStatus;

        $orderStatus = new OrderStatus();
        $name = 'оплачен и ждёт сборки';
        $orderStatus->setExternalId(2);
        $orderStatus->setName($name);
        $orderStatus->setSlug($slugify->slugify($name));
        $manager->persist($orderStatus);
        $orderStatuses[] = $orderStatus;

        $orderStatus = new OrderStatus();
        $name = 'в сборке';
        $orderStatus->setExternalId(3);
        $orderStatus->setName($name);
        $orderStatus->setSlug($slugify->slugify($name));
        $manager->persist($orderStatus);
        $orderStatuses[] = $orderStatus;

        $orderStatus = new OrderStatus();
        $name = 'доставляется';
        $orderStatus->setExternalId(4);
        $orderStatus->setName($name);
        $orderStatus->setSlug($slugify->slugify($name));
        $manager->persist($orderStatus);
        $orderStatuses[] = $orderStatus;

        $orderStatus = new OrderStatus();
        $name = 'готов к выдаче';
        $orderStatus->setExternalId(5);
        $orderStatus->setName($name);
        $orderStatus->setSlug($slugify->slugify($name));
        $manager->persist($orderStatus);
        $orderStatuses[] = $orderStatus;

        $orderStatus = new OrderStatus();
        $name = 'получен';
        $orderStatus->setExternalId(6);
        $orderStatus->setName($name);
        $orderStatus->setSlug($slugify->slugify($name));
        $manager->persist($orderStatus);
        $orderStatuses[] = $orderStatus;

        $orderStatus = new OrderStatus();
        $name = 'отменён';
        $orderStatus->setExternalId(7);
        $orderStatus->setName($name);
        $orderStatus->setSlug($slugify->slugify($name));
        $manager->persist($orderStatus);
        $orderStatuses[] = $orderStatus;

        /* Baskets */
        foreach ($users as $user) {
            $basket = new Basket();
            $basket->setUserRelation($user);
            $addProducts = $faker->randomElements($products, mt_rand(1, 20));
            foreach ($addProducts as $product) {
                $basket->createAndAddItem($product, mt_rand(1, 3));
            }
            $manager->persist($basket);
        }

        /* Orders */
        for ($i = 1; $i <= 100; ++$i) {
            $user = $users[array_rand($users)];

            $order = new Order();
            $order->setOrderStatus($orderStatuses[array_rand($orderStatuses)]);
            $order->setUserRelation($user);
            $order->setPhone($user->getPhone());
            $order->setDeliveryId(mt_rand(1, 2));
            $order->setPaymentId(mt_rand(1, 2));
            $addProducts = $faker->randomElements($products, mt_rand(1, 20));
            foreach ($addProducts as $product) {
                $order->createAndAddItem($product, mt_rand(1, 3), $product->getBasePrice());
            }
            $manager->persist($order);
        }

        $manager->flush();

        echo 'Fixtures loaded';
    }
}
