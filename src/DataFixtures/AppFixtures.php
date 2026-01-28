<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Events;
use App\Entity\Vehicle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer 10 utilisateurs
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setFirstName("firstName$i");
            $user->setLastName("lastname$i");
            $user->setEmail("user$i@test.com");
            $user->setUsername("User$i");
            $user->setPhone("060000000$i");
            $user->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($user);

            // Chaque user a un véhicule
            $vehicle = new Vehicle();
            $vehicle->setBrand("Marque$i");
            $vehicle->setModel("Modele$i");
            $vehicle->setYear(2000 + $i);
            $vehicle->setEngine("Moteur$i");
            $vehicle->setUserID($user);
            $vehicle->setPreparation(preparation: "stance$i");
            $manager->persist($vehicle);
        }

        // Créer 3 events
        for ($i = 1; $i <= 3; $i++) {
            $event = new Events();
            $event->setTitle("Event $i");
            $event->setDescription("Description de l'event $i");
            $event->setLocation("Bouches-du-Rhône");
            $event->setType("Run");
            $event->setDate("2021-05-0$i");
            $event->setCoverPhoto(coverPhoto: "https://picsum.photos/200/300?random=$i");
            $event->setGallery(gallery:"photo1");
            $event->setRatingAverage(ratingAverage: 4.5);
            $event->setCreatedAt(new \DateTimeImmutable());
            $manager->persist($event);
        }

        $manager->flush(); // Envoie tout en BDD
    }
}
