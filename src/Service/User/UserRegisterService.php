<?php

namespace App\Service\User;

use App\Entity\DTO\UserRegisterDTO;
use App\Entity\User;
use App\Event\UserRegisteredEvent;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegisterService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function register(UserRegisterDTO $userRegisterDTO): User
    {
        if ($this->userRepository->findOneBy(['email' => $userRegisterDTO->email])) {
            throw new ConflictHttpException('User with this email already exists.', null, Response::HTTP_CONFLICT);
        }

        if ($this->userRepository->findOneBy(['phone' => $userRegisterDTO->phone])) {
            throw new ConflictHttpException('User with this phone number already exists.', null, Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setFirstName($userRegisterDTO->firstName);
        $user->setLastName($userRegisterDTO->lastName);
        $user->setEmail($userRegisterDTO->email);
        $user->setPhone($userRegisterDTO->phone);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $userRegisterDTO->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(
            new UserRegisteredEvent($user->getEmail(), $user->getPhone())
        );

        return $user;
    }
}
