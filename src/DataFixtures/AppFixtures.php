<?php

namespace App\DataFixtures;

use App\Entity\Region;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\Events;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $regionNames = [
            'Auvergne-Rhône-Alpes',
            'Bourgogne-Franche-Comté',
            'Bretagne',
            'Centre-Val de Loire',
            'Corse',
            'Grand Est',
            'Hauts-de-France',
            'Île-de-France',
            'Normandie',
            'Nouvelle-Aquitaine',
            'Occitanie',
            'Pays de la Loire',
            'Provence-Alpes-Côte d\'Azur',
            'Guadeloupe',
            'Martinique',
            'Guyane',
            'La Réunion',
            'Mayotte',
        ];

        $regions = [];
        foreach ($regionNames as $name) {
            $region = new Region();
            $region->setName($name);
            $manager->persist($region);
            $regions[] = $region;
        }

        $users = [];

        // ----- USERS -----
        // Un compte "manager" pour pouvoir créer/modifier/supprimer des événements.
        $eventManager = new User();
        $eventManager->setFirstName('Event')
            ->setLastName('Manager')
            ->setUsername('event_manager')
            ->setEmail('event_manager@example.com')
            ->setPhone('0600000000')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setRoles(['ROLE_USER', 'ROLE_EVENT_MANAGER'])
            ->setPassword($this->passwordHasher->hashPassword($eventManager, 'password123'));
        $manager->persist($eventManager);
        $users[] = $eventManager;

        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setUsername($faker->userName())
                ->setEmail($faker->unique()->safeEmail())
                ->setPhone($faker->phoneNumber())
                ->setCreatedAt(new \DateTimeImmutable());

            // Réseaux sociaux aléatoires
            $user->setInstagram($faker->boolean(70) ? $faker->userName() : null);
            $user->setSnapchat($faker->boolean(50) ? $faker->userName() : null);
            $user->setTwitter($faker->boolean(40) ? $faker->userName() : null);
            $user->setTiktok($faker->boolean(30) ? $faker->userName() : null);

            // Password hashé
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

            $manager->persist($user);
            $users[] = $user;

            // 1 à 3 véhicules par user
            $numVehicles = $faker->numberBetween(1, 3);
            for ($j = 0; $j < $numVehicles; $j++) {
                $vehicle = new Vehicle();
                $vehicle->setBrand($faker->company())
                    ->setModel($faker->word())
                    ->setYear((string) $faker->numberBetween(1995, 2023))
                    ->setEngine($faker->randomElement(['1.2L', '1.6L', '2.0L', '2.5L turbo']))
                    ->setPreparation($faker->randomElement(['stance', 'drift', 'jdm', 'run']))
                    ->setUserID($user);

                $manager->persist($vehicle);
            }
        }

        // ----- EVENTS -----
        $eventTypes = ['Run', 'JDM', 'Drift', 'Stance'];

        for ($i = 0; $i < 15; $i++) {
            $event = new Events();
            $date = $faker->dateTimeBetween('-1 year', '+1 year');
            $event->setDate($date->format('Y-m-d'));
            $event->setTitle($faker->sentence(3))
                ->setDescription($faker->paragraph(2))
                ->setLocation($faker->city())
                ->setType($faker->randomElement($eventTypes))
                ->setCoverPhoto($faker->imageUrl(640, 480, 'cars', true))
                ->setGallery(implode(',', [$faker->imageUrl(), $faker->imageUrl(), $faker->imageUrl()]))
                ->setRatingAverage((string) $faker->randomFloat(1, 1, 5))
                ->setCreatedAt(new \DateTimeImmutable())
                ->setRegionID($faker->randomElement($regions));

            $manager->persist($event);
        }

        $manager->flush();
    }
}
