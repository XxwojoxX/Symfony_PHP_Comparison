<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Posts;
use App\Entity\Users;
use App\Entity\Category;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;
// Usuń ten use: use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

// Usuń implementację DependentFixtureInterface
class PostsFixtures extends Fixture implements FixtureGroupInterface
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $batchSize = 2000;
        $totalRecords = 100000; // Example: Add 100,000 posts

        // Get existing users and categories
        // Pobieramy tylko ID i username/name, aby zredukować zużycie pamięci,
        // ponieważ potrzebujemy tylko referencji do przypisania
        $users = $manager->getRepository(Users::class)
                         ->createQueryBuilder('u')
                         ->select('u.id', 'u.username') // Wybieramy tylko potrzebne pola
                         ->getQuery()
                         ->getArrayResult(); // Pobieramy jako tablicę


        $categories = $manager->getRepository(Category::class)
                              ->createQueryBuilder('c')
                              ->select('c.id', 'c.name') // Wybieramy tylko potrzebne pola
                              ->getQuery()
                              ->getArrayResult(); // Pobieramy jako tablicę


        if (empty($users)) {
            throw new \RuntimeException('No users found. Please run UsersFixtures first.');
        }

        if (empty($categories)) {
            throw new \RuntimeException('No categories found. Please run CategoryFixtures first.');
        }

        // Mapujemy tablice wyników z powrotem na obiekty proxy/referencje
        $userReferences = array_map(function($userData) use ($manager) {
            return $manager->getReference(Users::class, $userData['id']);
        }, $users);

        $categoryReferences = array_map(function($categoryData) use ($manager) {
             return $manager->getReference(Category::class, $categoryData['id']);
        }, $categories);


        // Inicjalizacja paska postępu
        $output = new ConsoleOutput();
        $progressBar = new ProgressBar($output, $totalRecords);
        $progressBar->start();

        $startTime = microtime(true); // Zaczynamy mierzyć czas


        for ($i = 0; $i < $totalRecords; $i++) {
            $post = new Posts();
            $post->setTitle($faker->sentence(6));
            $post->setContent($faker->paragraphs(5, true));
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setUpdatedAt(new \DateTime());
            $post->setPublishedAt($faker->optional(0.8)->dateTimeBetween('-1 year', 'now'));

            // Assign a random user and category using references
            $post->setUser($faker->randomElement($userReferences));
            $post->setCategory($faker->randomElement($categoryReferences));
            $post->setSlug($this->slugger->slug($post->getTitle())->lower()->toString());

            // Możesz dodać losową nazwę obrazu, jeśli chcesz (bez generowania pliku)
            // $post->setImageName($faker->optional(0.7)->image(null, 640, 480, 'cats', true, true, 'Faker'));


            $manager->persist($post);

            $progressBar->advance(); // Aktualizacja paska postępu

            if (($i % $batchSize) === 0 && $i > 0) {
                $manager->flush();
                $manager->clear();
                // Po manager->clear(), wszystkie pobrane obiekty/referencje są oddzielone.
                // Trzeba ponownie załadować referencje, jeśli pętla będzie kontynuowana.
                 $userReferences = array_map(function($userData) use ($manager) {
                    return $manager->getReference(Users::class, $userData['id']);
                 }, $users); // Używamy oryginalnych tablic ID

                 $categoryReferences = array_map(function($categoryData) use ($manager) {
                    return $manager->getReference(Category::class, $categoryData['id']);
                 }, $categories); // Używamy oryginalnych tablic ID

                gc_collect_cycles();
            }

             // Wyświetlanie czasu, który minął na bieżąco
            $elapsedTime = round(microtime(true) - $startTime, 2);
            $remainingRecords = $totalRecords - $i - 1;
             // Unikaj dzielenia przez zero lub ujemne wartości
            $estimatedTime = ($i >= 0 && $remainingRecords > 0 && $elapsedTime > 0)
                             ? round($elapsedTime / ($i + 1) * $remainingRecords, 2)
                             : 0;
            $progressBar->setMessage("⏳ Czas: {$elapsedTime}s | Estymowany czas: {$estimatedTime}s", 'info');
        }

        $manager->flush();
        $manager->clear(); // Opcjonalne, aby zwolnić pamięć po zakończeniu pętli


        // Zakończenie paska postępu
        $progressBar->finish();
        $output->writeln("\nZakończono dodawanie postów!");
    }

    // Usunięto metodę getDependencies()

    public static function getGroups(): array
    {
         // Przypisujemy do grupy 'post_group' i opcjonalnie do 'initial_data' lub innej grupy globalnej
        return ['post_group', 'initial_data'];
    }
}