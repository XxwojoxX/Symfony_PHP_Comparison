<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Users;
use App\Entity\Roles;
use Faker\Factory;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class UsersFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $batchSize = 5000;
        $totalRecords = 1000000;

        // Pobierz repozytorium ról
        $rolesRepository = $manager->getRepository(Roles::class);

        // Pobierz rolę o ID 2, jeśli nie istnieje, możemy ją dodać
        $role = $rolesRepository->findOneBy(['id' => 2]);

        // Jeżeli rola o ID 2 nie istnieje, możemy utworzyć ją tutaj
        if (!$role) {
            $role = new Roles();
            $role->setName('ROLE_USER');
            $manager->persist($role);
            $manager->flush(); // Zapewni to zapisanie nowej roli do bazy
        }

        // Inicjalizacja paska postępu
        $output = new ConsoleOutput();
        $progressBar = new ProgressBar($output, $totalRecords);
        $progressBar->start();

        $startTime = microtime(true); // Zaczynamy mierzyć czas

        for ($i = 0; $i < $totalRecords; $i++) {
            $user = new Users();
            $user->setEmail($faker->unique()->safeEmail());
            $user->setPassword(password_hash('zaq1@WSX', PASSWORD_BCRYPT));
            $user->setUsername($faker->userName());
            $user->setCreatedAt(new \DateTime());

            // Przypisanie roli
            $user->setRole($role);

            $manager->persist($user);
            $progressBar->advance(); // Aktualizacja paska postępu

            // Wykonaj flush co 1000 użytkowników, aby nie zapełniać pamięci
            if (($i % $batchSize) === 0 && $i > 0) {
                $manager->flush();
                $manager->clear();
                gc_collect_cycles();
            }

            // Wyświetlanie czasu, który minął na bieżąco
            $elapsedTime = round(microtime(true) - $startTime, 2);
            $estimatedTime = round($elapsedTime / ($i + 1) * ($totalRecords - $i - 1), 2);
            $progressBar->setMessage("⏳ Czas: {$elapsedTime}s | Estymowany czas: {$estimatedTime}s", 'info');
        }

        // Zapewniamy zapisanie pozostałych danych
        $manager->flush();

        // Zakończenie paska postępu
        $progressBar->finish();
        $output->writeln("\nZakończono dodawanie użytkowników!");
    }

    public static function getGroups(): array
    {
        // Przypisujemy do grupy 'user_group' i opcjonalnie do 'initial_data' lub innej grupy globalnej
        return ['user_group', 'initial_data'];
    }
}
