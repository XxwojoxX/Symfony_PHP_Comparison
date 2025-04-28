<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Comment;
use App\Entity\Users;
use App\Entity\Posts;
use Faker\Factory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Get existing users and posts
        $users = $manager->getRepository(Users::class)->findAll();
        $posts = $manager->getRepository(Posts::class)->findAll();

        if (empty($users)) {
            throw new \RuntimeException('No users found. Please run UsersFixtures first.');
        }

        if (empty($posts)) {
            throw new \RuntimeException('No posts found. Please run PostsFixtures first.');
        }

        $batchSize = 10000;
        $totalRecords = 500000; // Example: Add 50,000 comments

        for ($i = 0; $i < $totalRecords; $i++) {
            $comment = new Comment();
            $comment->setContent($faker->paragraph(2));
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setUpdatedAt($faker->optional(0.5)->dateTimeBetween('-6 months', 'now'));

            // Assign a random user and post
            $comment->setUser($faker->randomElement($users));
            $comment->setPost($faker->randomElement($posts));

            $manager->persist($comment);

            if (($i % $batchSize) === 0 && $i > 0) {
                $manager->flush();
                $manager->clear();
                // Re-fetch users and posts after clearing the manager
                $users = $manager->getRepository(Users::class)->findAll();
                $posts = $manager->getRepository(Posts::class)->findAll();
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
            PostsFixtures::class,
        ];
    }
}