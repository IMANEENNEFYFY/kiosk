<?php

namespace App\Controller;

use App\Entity\CartePrepayee;
use App\Form\CartePrepayeeType;
use App\Repository\CartePrepayeeRepository;
use App\Service\BorneContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartePrepayeeController extends AbstractController
{
    #[Route('/', name: 'app_scan_carte')]
    public function scanCarte(Request $request, CartePrepayeeRepository $carteRepo, BorneContext $borneContext): Response
    {
        $espace = $borneContext->getEspace();
        
        // Vérifier si l'espace est un restaurant universitaire
        if ($espace->getIdentifiant() !== 'resto_universitaire') {
            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('carte_prepayee/scan.html.twig', [
            'espace' => $espace
        ]);
    }

    #[Route('/verifier-carte', name: 'app_verifier_carte', methods: ['POST'])]
    public function verifierCarte(Request $request, CartePrepayeeRepository $carteRepo, BorneContext $borneContext): Response
    {
        $espace = $borneContext->getEspace();
        $codeCarte = $request->request->get('code_carte');
        $carte = $carteRepo->findOneBy(['codeCarte' => $codeCarte]);

        if (!$carte) {
            $this->addFlash('error', 'Carte non trouvée');
            return $this->redirectToRoute('app_scan_carte');
        }

        if ($carte->getSolde() <= 0) {
            $request->getSession()->clear();
            return $this->render('carte_prepayee/scan.html.twig', [
                'espace' => $espace,
                'solde_insuffisant' => true
            ]);
        }

        $request->getSession()->set('carte_prepayee_id', $carte->getId());
        $request->getSession()->set('carte_prepayee_solde', $carte->getSolde());

        return $this->redirectToRoute('app_categorie');
    }

    #[Route('/confirmation-carte', name: 'app_confirmation_carte')]
    public function confirmationCarte(Request $request, BorneContext $borneContext): Response
    {
        $espace = $borneContext->getEspace();
        
        if (!$request->getSession()->has('carte_prepayee_id')) {
            return $this->redirectToRoute('app_scan_carte');
        }

        return $this->render('carte_prepayee/confirmation.html.twig', [
            'espace' => $espace,
            'nom' => $request->getSession()->get('carte_prepayee_nom'),
            'prenom' => $request->getSession()->get('carte_prepayee_prenom'),
            'solde' => $request->getSession()->get('carte_prepayee_solde')
        ]);
    }

    #[Route('/valider-carte', name: 'app_valider_carte', methods: ['POST'])]
public function validerCarte(Request $request): Response
{
    if (!$request->getSession()->has('carte_prepayee_id')) {
        return $this->redirectToRoute('app_scan_carte');
    }

    $solde = $request->getSession()->get('carte_prepayee_solde');

    if ($solde <= 0) {
        $this->addFlash('error', 'Solde insuffisant pour valider.');
        return $this->redirectToRoute('app_scan_carte');
    }

    $this->addFlash('success', sprintf(
        'Bienvenue %s %s ! Votre solde est de %.2f €',
        $request->getSession()->get('carte_prepayee_prenom'),
        $request->getSession()->get('carte_prepayee_nom'),
        $solde
    ));

    return $this->redirectToRoute('app_accueil');
}
}