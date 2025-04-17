<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\ProduitRepository;
use App\Service\PanierService; // ✅ IMPORT CORRECT
use App\Repository\ProduitRepository as ProduitRepo; // pour la nouvelle méthode
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

    #[Route('/panier/ajouter/{id}', name: 'ajouter_au_panier')]
    public function ajouterAuPanier(int $id, PanierService $panierService, ProduitRepo $produitRepository): Response
    {
        $panierService->ajouter($id);

        $produit = $produitRepository->find($id);
        $categorie = $produit->getCategorie();

        return $this->redirectToRoute('app_produits_par_categorie', ['id' => $categorie->getId()]);
    }
}
