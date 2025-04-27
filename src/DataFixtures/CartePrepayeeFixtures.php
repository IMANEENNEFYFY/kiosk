<?php

namespace App\DataFixtures;

use App\Entity\CartePrepayee;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CartePrepayeeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $cartes = [
            [
                'code' => 'CARD123456',
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'solde' => 50.00
            ],
            [
                'code' => 'CARD789012',
                'nom' => 'Martin',
                'prenom' => 'Sophie',
                'solde' => 25.50
            ],
            [
                'code' => 'CARD345678',
                'nom' => 'Bernard',
                'prenom' => 'Pierre',
                'solde' => 100.00
            ]
        ];

        foreach ($cartes as $data) {
            $carte = new CartePrepayee();
            $carte->setCodeCarte($data['code'])
                  ->setNom($data['nom'])
                  ->setPrenom($data['prenom'])
                  ->setSolde($data['solde'])
                  ->setActif(true);
            
            $manager->persist($carte);
        }

        $manager->flush();
    }
}
