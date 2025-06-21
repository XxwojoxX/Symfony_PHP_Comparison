<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Roles;

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
    public function findAllUsers(?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->createQueryBuilder('u')
            ->leftjoin('u.role', 'r')
            ->addSelect('r')
            ->orderBy('u.id', 'ASC');

            if($limit !== null)
            {
                $qb->setMaxResults($limit);
            }

            if($offset !== null)
            {
                $qb->setFirstResult($offset);
            }

            return $qb->getQuery()->getResult();
    }

    public function findAllUsersNative(?int $limit = null): array
{
    $conn = $this->getEntityManager()->getConnection();

    $sql = "
        SELECT u.id, u.username, u.email, u.created_at, r.name AS role_name
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        ORDER BY u.id ASC
    ";

    if ($limit !== null) {
        $sql .= " LIMIT " . (int) $limit;
    }

    return $conn->executeQuery($sql)->fetchAllAssociative();
}

    #znajdź użytkownika po id
    public function findUserById(int $id): ?Users
    {
        return $this->createQueryBuilder('u')
            ->leftjoin('u.role', 'r')
            ->addSelect('r')
            ->andWhere('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
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

    #znajdź użytkownika po roli
    public function findUserByRole(Roles $role): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.role = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult();
    }

    public function findUserByEmail(string $email): ?Users
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
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

    public function saveUser(Users $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}
