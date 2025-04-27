<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProduitRepository;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(Request $request, ProduitRepository $produitRepository): Response
    {
        $panier = $request->getSession()->get('panier', []);
        $produits = [];
        $total = 0;

        foreach ($panier as $id => $quantite) {
            $produit = $produitRepository->find($id);
            if ($produit) {
                $produits[] = [
                    'produit' => $produit,
                    'quantite' => $quantite
                ];
                $total += $produit->getPrix() * $quantite;
            }
        }

        return $this->render('panier/index.html.twig', [
            'produits' => $produits,
            'total' => $total
        ]);
    }

    #[Route('/panier/ajouter/{id}', name: 'panier_ajouter', methods: ['POST'])]
    public function ajouter(Request $request, int $id): JsonResponse
    {
        $session = $request->getSession();
        $panier = $session->get('panier', []);

        if (!isset($panier[$id])) {
            $panier[$id] = 0;
        }
        $panier[$id]++;

        $session->set('panier', $panier);

        return new JsonResponse([
            'success' => true,
            'cartCount' => array_sum($panier)
        ]);
    }

    #[Route('/panier/supprimer/{id}', name: 'panier_supprimer')]
    public function supprimer(Request $request, int $id): Response
    {
        $session = $request->getSession();
        $panier = $session->get('panier', []);

        if (isset($panier[$id])) {
            unset($panier[$id]);
            $session->set('panier', $panier);
        }

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/vider', name: 'panier_vider')]
    public function vider(Request $request): Response
    {
        $request->getSession()->remove('panier');
        return $this->redirectToRoute('app_panier');
    }
}