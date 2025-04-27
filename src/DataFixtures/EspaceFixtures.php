<?php

namespace App\DataFixtures;

use App\Entity\Espace;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EspaceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $restoUniv = new Espace();
        $restoUniv->setIdentifiant('resto_universitaire')
                 ->setNom('Restaurant Universitaire')
                 ->setDescription('Restaurant universitaire avec paiement par carte prépayée');

        $manager->persist($restoUniv);
        $manager->flush();
    }
} 