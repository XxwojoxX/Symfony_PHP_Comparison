<?php

namespace App\Service;

use App\Entity\Users;
use App\Entity\Roles;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthService
{
    private UsersRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(UsersRepository $userRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
    }

    public function register(string $username, string $email, string $plainpassword): Users
    {
        $user = new Users();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setCreatedAt(new \DateTime());
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainpassword));

        $role = $this->entityManager->getRepository(Roles::class)->find(2);
        $user->setRole($role);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function login(string $email, string $plainpassword): string
    {
        // Pobieramy użytkownika na podstawie emaila
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new UnauthorizedHttpException('Invalid credentials 1');
        }

        // Sprawdzamy, czy hasło jest poprawne
        if (!$this->passwordHasher->isPasswordValid($user, $plainpassword)) {
            throw new UnauthorizedHttpException('Invalid credentials 2');
        }

        // Generowanie tokenu JWT z rolami użytkownika
        $payload = [
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(), // Zwracamy role z użytkownika
            'username' => $user->getUsername(),
            'exp' => time() + 3600 // Możesz ustawić czas wygaśnięcia tokenu, np. 1 godzina
        ];

        // Tworzymy token JWT
        return $this->jwtManager->createFromPayload($payload);
    }
}