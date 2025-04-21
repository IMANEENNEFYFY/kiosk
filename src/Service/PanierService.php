<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProduitRepository;

class PanierService
{
    private $session;
    private $produitRepository;

    public function __construct(RequestStack $requestStack, ProduitRepository $produitRepository)
    {
        $this->session = $requestStack->getSession();
        $this->produitRepository = $produitRepository;
    }

    public function getPanier(): array
    {
        return $this->session->get('panier', []);
    }

    public function getPanierComplet(): array
    {
        $panierComplet = [];

        foreach ($this->getPanier() as $id => $quantite) {
            $produit = $this->produitRepository->find($id);
            if ($produit) {
                $panierComplet[] = [
                    'produit' => $produit,
                    'quantite' => $quantite,
                ];
            }
        }

        return $panierComplet;
    }

    public function getTotal(): float
    {
        $total = 0;

        foreach ($this->getPanierComplet() as $item) {
            $total += $item['produit']->getPrix() * $item['quantite'];
        }

        return $total;
    }

    public function viderPanier(): void
    {
        $this->session->remove('panier');
    }

    public function ajouter(int $id, int $quantite = 1): void
    {
        $panier = $this->session->get('panier', []);

        if (!empty($panier[$id])) {
            $panier[$id] += $quantite;
        } else {
            $panier[$id] = $quantite;
        }

        $this->session->set('panier', $panier);
    }

    public function diminuer(int $id, int $quantite = 1): void
    {
        $panier = $this->session->get('panier', []);

        if (!empty($panier[$id])) {
            if ($panier[$id] > $quantite) {
                $panier[$id] -= $quantite;
            } else {
                unset($panier[$id]);
            }
            $this->session->set('panier', $panier);
        }
    }

    public function supprimer(int $id): void
    {
        $panier = $this->session->get('panier', []);

        if (!empty($panier[$id])) {
            unset($panier[$id]);
            $this->session->set('panier', $panier);
        }
    }
}
