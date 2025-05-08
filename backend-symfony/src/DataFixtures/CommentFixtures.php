<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Comment;
use App\Entity\Users;
use App\Entity\Posts;
use Faker\Factory;
// Usuń ten use: use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use DateTime;


// Usuń implementację DependentFixtureInterface
class CommentFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $batchSize = 10000;
        $totalRecords = 500000; // Example: Add 500,000 comments

        // Get existing users and posts using optimized fetching
        // Pobieramy tylko ID, aby zredukować zużycie pamięci,
        // ponieważ potrzebujemy tylko referencji do przypisania
        $users = $manager->getRepository(Users::class)
                         ->createQueryBuilder('u')
                         ->select('u.id') // Wybieramy tylko ID
                         ->getQuery()
                         ->getArrayResult(); // Pobieramy jako tablicę


        $posts = $manager->getRepository(Posts::class)
                              ->createQueryBuilder('p')
                              ->select('p.id') // Wybieramy tylko ID
                              ->getQuery()
                              ->getArrayResult(); // Pobieramy jako tablicę


        if (empty($users)) {
            throw new \RuntimeException('No users found. Please ensure UsersFixtures has been run.');
        }

        if (empty($posts)) {
            throw new \RuntimeException('No posts found. Please ensure PostsFixtures has been run.');
        }

         // Mapujemy tablice wyników z powrotem na obiekty proxy/referencje
        $userReferences = array_map(function($userData) use ($manager) {
            return $manager->getReference(Users::class, $userData['id']);
        }, $users);

        $postReferences = array_map(function($postData) use ($manager) {
             return $manager->getReference(Posts::class, $postData['id']);
        }, $posts);


        // Inicjalizacja paska postępu
        $output = new ConsoleOutput();
        $progressBar = new ProgressBar($output, $totalRecords);
        $progressBar->start();

        $startTime = microtime(true); // Zaczynamy mierzyć czas


        for ($i = 0; $i < $totalRecords; $i++) {
            $comment = new Comment();
            $comment->setContent($faker->paragraph(2));
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setUpdatedAt($faker->optional(0.5)->dateTimeBetween('-6 months', 'now'));


            // Assign a random user and post using references
            $comment->setUser($faker->randomElement($userReferences));
            $comment->setPost($faker->randomElement($postReferences));


            $manager->persist($comment);

            $progressBar->advance(); // Aktualizacja paska postępu

            if (($i % $batchSize) === 0 && $i > 0) {
                $manager->flush();
                $manager->clear();
                // Po manager->clear(), wszystkie pobrane obiekty/referencje są oddzielone.
                // Trzeba ponownie załadować referencje, jeśli pętla będzie kontynuowana.
                 $userReferences = array_map(function($userData) use ($manager) {
                    return $manager->getReference(Users::class, $userData['id']);
                 }, $users); // Używamy oryginalnych tablic ID

                 $postReferences = array_map(function($postData) use ($manager) {
                    return $manager->getReference(Posts::class, $postData['id']);
                 }, $posts); // Używamy oryginalnych tablic ID

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
        $output->writeln("\nZakończono dodawanie komentarzy!");
    }

    // Usunięto metodę getDependencies()

    public static function getGroups(): array
    {
         // Przypisujemy do grupy 'comment_group' i opcjonalnie do 'initial_data' lub innej grupy globalnej
        return ['comment_group', 'initial_data'];
    }
}