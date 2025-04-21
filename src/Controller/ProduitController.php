<?php

// src/Controller/ProduitController.php
namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use App\Service\PanierService;
use App\Repository\ProduitRepository;
use App\Service\BorneContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProduitController extends AbstractController
{
    public function __construct(
        private BorneContext $borneContext
    ) {}

    #[Route('/panier/ajouter/{id}', name: 'ajouter_au_panier', methods: ['POST'])]
    public function ajouterAuPanier(
        int $id,
        PanierService $panierService,
        ProduitRepository $produitRepo
    ): Response {
        $produit = $produitRepo->find($id);
        
        if (!$produit || !$produit->isEstDisponible()) {
            $this->addFlash('error', 'Produit indisponible');
            return $this->redirectToRoute('app_accueil');
        }

        $panierService->ajouter($id);
        $this->addFlash('success', $produit->getNom().' ajouté au panier');

        return $this->redirectToRoute('app_produits_par_categorie', [
            'id' => $produit->getCategorie()->getId()
        ]);
    }

   // src/Controller/ProduitController.php
#[Route('/categorie/{id}/produits', name: 'app_produits_par_categorie')]
public function produitsParCategorie(
    Categorie $categorie,
    CategorieRepository $categorieRepo
): Response {
    $espace = $this->borneContext->getEspace();

    // Vérification que la catégorie appartient à l'espace
    if ($categorie->getEspace()->getId() !== $espace->getId()) {
        throw $this->createNotFoundException();
    }

    return $this->render('produit/index.html.twig', [
        'espace' => $espace,
        'categorie' => $categorie,
        'produits' => $categorie->getProduits()->filter(
            fn($p) => $p->isEstDisponible()
        ),
        'categories' => $categorieRepo->findWithAvailableProducts($espace)
    ]);
}}