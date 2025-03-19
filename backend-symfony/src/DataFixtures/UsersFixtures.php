<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Users;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($i = 1; $i <= 10; $i++)
        {
            $user = new Users();
            $user->setEmail("user$i@example.com");
            $user->setPassword('password1234');
            $user->setUsername("user$i");
            $user->setCreatedAt(new \DateTime());

            $manager->persist($user);
        }

        $manager->flush();
    }
}
