<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProduitRepository;
use App\Entity\Categorie; 

class ProduitController extends AbstractController
{
    #[Route('/categorie/{id}/produits', name: 'app_produits_par_categorie')]
    public function produitsParCategorie(Categorie $categorie, ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findBy(['categorie' => $categorie]);
        
        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'categorie' => $categorie
        ]);
}
}