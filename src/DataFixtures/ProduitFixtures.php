<?php

namespace App\DataFixtures;

use App\Entity\Produit;
use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProduitFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création des catégories
        $boissons = new Categorie();
$boissons->setNom('Boissons')
         ->setImage('boisson.jpg');  // Nom du fichier dans public/images/categories/

        $manager->persist($boissons);
        
        $nourriture = new Categorie();
        $nourriture->setNom('Nourriture')
        ->setImage('nourriture.jpg');
        $manager->persist($nourriture);
    
        // Produits avec toutes les propriétés requises
        $produits = [
            ['nom' => 'Café', 'prix' => 1.50, 'categorie' => $boissons, 'disponible' => true],
            ['nom' => 'Thé', 'prix' => 1.20, 'categorie' => $boissons, 'disponible' => true],
            ['nom' => 'Sandwich', 'prix' => 4.50, 'categorie' => $nourriture, 'disponible' => true],
        ];
    
        foreach ($produits as $data) {
            $produit = new Produit();
            $produit->setNom($data['nom'])
                   ->setPrix($data['prix'])
                   ->setCategorie($data['categorie'])
                   ->setEstDisponible($data['disponible']); // Ajout de cette ligne
            $manager->persist($produit);
        }
    
        $manager->flush();
    }
}