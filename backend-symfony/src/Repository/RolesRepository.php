<?php

namespace App\Repository;

use App\Entity\Roles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Roles>
 */
class RolesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Roles::class);
    }

    #znajdź wszystkie role
    public function findAllRoles(): array
    {
        return $this->findAll();
    }

    #znajdź rolę po id
    public function findRoleById(int $id): ?Roles
    {
        return $this->find($id);
    }

    #stworz nową rolę
    public function createRole(string $name): Roles
    {
        $role = new Roles();
        $role->setName($name);

        $this->getEntityManager()->persist($role);
        $this->getEntityManager()->flush();

        return $role;
    }

    #usuń rolę
    public function deleteRole(Roles $role): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($role);
        $entityManager->flush();
    }
}
