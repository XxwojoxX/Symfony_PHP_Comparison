<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Users>
 */
class UsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    #znajdź wszystkich użytkowników
    public function findAllUsers(): array
    {
        return $this->findAll();
    }

    #znajdź użytkownika po id
    public function findUserById(int $id): ?Users
    {
        return $this->find($id);
    }

    #znajdź użytkownika po nazwie użytkownika
    public function findUserByUsername(string $username): ?Users
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    #stworz nowego użytkownika
    public function createUser(string $username, string $email, string $password): Users
    {
        $user = new Users();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setCreatedAt(new \DateTime());

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    #edytuj użytkownika
    public function updateUser(Users $user): void
    {
        $this->getEntityManager()->flush();
    }

    #usuń użytkownika
    public function deleteUser(Users $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }
}
