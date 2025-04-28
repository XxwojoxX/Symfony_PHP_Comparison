<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Posts;
use App\Entity\Users;
use App\Entity\Category;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PostsFixtures extends Fixture implements DependentFixtureInterface
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Get existing users and categories
        $users = $manager->getRepository(Users::class)->findAll();
        $categories = $manager->getRepository(Category::class)->findAll();

        if (empty($users)) {
            throw new \RuntimeException('No users found. Please run UsersFixtures first.');
        }

        if (empty($categories)) {
            throw new \RuntimeException('No categories found. Please run CategoryFixtures first.');
        }

        $batchSize = 2000;
        $totalRecords = 100000; // Example: Add 10,000 posts

        for ($i = 0; $i < $totalRecords; $i++) {
            $post = new Posts();
            $post->setTitle($faker->sentence(6));
            $post->setContent($faker->paragraphs(5, true));
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setUpdatedAt(new \DateTime());
            $post->setPublishedAt($faker->optional(0.8)->dateTimeBetween('-1 year', 'now'));

            // Assign a random user and category
            $post->setUser($faker->randomElement($users));
            $post->setCategory($faker->randomElement($categories));
            $post->setSlug($this->slugger->slug($post->getTitle())->lower()->toString());

            $manager->persist($post);

            if (($i % $batchSize) === 0 && $i > 0) {
                $manager->flush();
                $manager->clear();
                // Re-fetch users and categories after clearing the manager
                $users = $manager->getRepository(Users::class)->findAll();
                $categories = $manager->getRepository(Category::class)->findAll();
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsersFixtures::class,
            CategoryFixtures::class,
        ];
    }
}