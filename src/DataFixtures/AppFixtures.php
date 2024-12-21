<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Client;
use App\Entity\Dette;
use App\Entity\Paiement;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Création des utilisateurs
        $users = [];
        for ($i = 0; $i < 9; $i++) { // 9 utilisateurs au total : 3 par rôle
            $user = new User();
            $user->setEmail($faker->unique()->email);
            $passwordHasher = $this->encoder->hashPassword($user, 'password');
            $user->setPassword($passwordHasher); // Pour simplifier, le même mot de passe

            // Attribuer les rôles de façon cyclique
            if ($i % 3 === 0) {
                $user->setRoles(['ROLE_BOUTIQUIER']);
            } elseif ($i % 3 === 1) {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_CLIENT']);
            }

            $manager->persist($user);
            $users[] = $user;
        }

        // Création des clients
        $clients = [];
        for ($i = 0; $i < 10; $i++) {
            $client = new Client();
            $client->setSurname($faker->lastName)
                ->setTelephone($faker->unique()->phoneNumber)
                ->setAdress($faker->address)
                ->setUtilisateur($i < 5 ? $users[$i] : null); // Les 5 premiers clients ont des utilisateurs
            $manager->persist($client);
            $clients[] = $client;
        }

        // Création des articles
        $articles = [];
        for ($i = 0; $i < 10; $i++) {
            $article = new Article();
            $article->setNomArticle($faker->word)
                ->setPrix($faker->randomFloat(2, 10, 100))
                ->setQteStock($faker->numberBetween(1, 50));
            $manager->persist($article);
            $articles[] = $article;
        }

        // Création des dettes
        $dettes = [];
        $etats = ['en_cours', 'accepte', 'refuse']; // États possibles
        for ($i = 0; $i < 15; $i++) {
            $dette = new Dette();
            $dette->setDate($faker->dateTimeThisYear)
                ->setMontant($faker->randomFloat(2, 100, 1000))
                ->setMontantVerser($faker->randomFloat(2, 0, 500))
                ->setMontantRestant($faker->randomFloat(2, 500, 1000))
                ->setClient($clients[array_rand($clients)])
                ->setEtat($etats[array_rand($etats)]) // Assigner un état aléatoire
                ->addArticle($articles[array_rand($articles)]);
            $manager->persist($dette);
            $dettes[] = $dette;
        }

        // Création des paiements
        for ($i = 0; $i < 20; $i++) {
            $paiement = new Paiement();
            $paiement->setDate($faker->dateTimeThisYear)
                ->setMontant($faker->randomFloat(2, 10, 500))
                ->setDette($dettes[array_rand($dettes)]) // Associer un paiement à une dette existante
                ->setClient($clients[array_rand($clients)]);
            $manager->persist($paiement);
        }

        // Sauvegarde des données en base
        $manager->flush();
    }
}
