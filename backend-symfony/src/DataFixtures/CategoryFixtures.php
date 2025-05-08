<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Category;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class CategoryFixtures extends Fixture implements FixtureGroupInterface
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $categories = [
            'Technology',
            'Science',
            'Politics',
            'Sports',
            'Entertainment',
            'Business',
            'Travel',
            'Food',
            'Health',
            'Education',
        ];

        foreach ($categories as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $category->setSlug($this->slugger->slug($categoryName)->lower()->toString());
            $manager->persist($category);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['category_group', 'initial_data']; // Możesz przypisać fixturę do jednej lub wielu grup
    }
}