<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PanierService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class PanierController extends AbstractController{


#[Route('/panier', name: 'afficher_panier')]
public function afficher(PanierService $panierService): Response
{
    return $this->render('panier/index.html.twig', [
        'panier' => $panierService->getPanierComplet(),
        'total' => $panierService->getTotal(),
    ]);
}
// src/Controller/PanierController.php

#[Route('/panier/ajouter/{id}', name: 'panier_ajouter')]
public function ajouter(int $id, PanierService $panierService): RedirectResponse
{
    $panierService->ajouter($id);
    return $this->redirectToRoute('afficher_panier');
}

#[Route('/panier/diminuer/{id}', name: 'panier_diminuer')]
public function diminuer(int $id, PanierService $panierService): RedirectResponse
{
    $panierService->diminuer($id);
    return $this->redirectToRoute('afficher_panier');
}

#[Route('/panier/supprimer/{id}', name: 'panier_supprimer')]
public function supprimer(int $id, PanierService $panierService): RedirectResponse
{
    $panierService->supprimer($id);
    return $this->redirectToRoute('afficher_panier');
}
}