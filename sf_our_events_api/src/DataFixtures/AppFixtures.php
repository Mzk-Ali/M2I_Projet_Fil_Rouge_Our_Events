<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Premise;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher) {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un user "normal"
        $user = new User();
        $user->setEmail("user@eventapi.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $user->setLastName("user_last");
        $user->setFirstName("user_first");
        $user->setIsVerified(true);
        $manager->persist($user);
        
        // Création d'un user admin
        $userAdmin = new User();
        $userAdmin->setEmail("admin@eventapi.com");
        $userAdmin->setRoles(["ROLE_USER", "ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $userAdmin->setLastName("admin_last");
        $userAdmin->setFirstName("admin_first");
        $userAdmin->setIsVerified(true);
        $manager->persist($userAdmin);


        // Categories
        $categories = [
            "Musique",
            "Sport",
            "Technologie",
            "Art",
            "Cuisine",
            "Voyage",
            "Santé",
            "Education"
        ];

        foreach ($categories as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);
        }


        // Premises
        $premisesData = [
            ["123 Rue de Paris", "Paris", "75001"],
            ["45 Avenue des Champs", "Lyon", "69000"],
            ["78 Boulevard Saint-Michel", "Marseille", "13001"],
            ["12 Rue Lafayette", "Toulouse", "31000"],
            ["99 Rue Victor Hugo", "Nice", "06000"]
        ];

        foreach ($premisesData as [$address, $city, $postalCode]) {
            $premise = new Premise();
            $premise->setAddress($address);
            $premise->setCity($city);
            $premise->setPostalCode($postalCode);
            $manager->persist($premise);
        }


        // --- Événements ---
        $eventsData = [
            [
                'title' => 'Concert Jazz Live',
                'description' => 'Un concert exceptionnel avec les meilleurs musiciens de jazz.',
                'image' => 'https://example.com/images/jazz.jpg',
                'capacity' => 150,
                'start' => '+10 days',
                'end' => '+10 days +2 hours'
            ],
            [
                'title' => 'Hackathon Tech 2025',
                'description' => 'Un week-end pour innover autour des nouvelles technologies.',
                'image' => 'https://example.com/images/hackathon.jpg',
                'capacity' => 300,
                'start' => '+15 days',
                'end' => '+17 days'
            ],
            [
                'title' => "Exposition d'Art Moderne",
                'description' => 'Découvrez les talents émergents de la scène artistique contemporaine.',
                'image' => 'https://example.com/images/art.jpg',
                'capacity' => 200,
                'start' => '+20 days',
                'end' => '+20 days +5 hours'
            ],
            [
                'title' => 'Cours de Cuisine Italienne',
                'description' => 'Apprenez les secrets de la cuisine italienne avec un chef renommé.',
                'image' => 'https://example.com/images/cuisine.jpg',
                'capacity' => 20,
                'start' => '+25 days',
                'end' => '+25 days +3 hours'
            ],
        ];

        foreach ($eventsData as $index => $data) {
            $event = new Event();
            $event->setTitle($data['title']);
            $event->setDescription($data['description']);
            $event->setImageUrl($data['image']);
            $event->setCapacity($data['capacity']);
            $event->setStartDatetime(new \DateTime($data['start']));
            $event->setEndDatetime(new \DateTime($data['end']));

            // si plus tard tu ajoutes des relations:
            // $event->setCategory($categories[$index % count($categories)]);
            // $event->setPremise($premises[$index % count($premises)]);

            $manager->persist($event);
        }

        
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
