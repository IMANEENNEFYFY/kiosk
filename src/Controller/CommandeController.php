<?php
namespace App\Controller;

use App\Entity\Commande;
use App\Entity\ElementCommande;
use App\Entity\CartePrepayee;
use App\Service\TicketGenerator;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Picqer\Barcode\BarcodeGeneratorSVG;
use App\Repository\CartePrepayeeRepository;

class CommandeController extends AbstractController
{
    #[Route('/commande/valider', name: 'commande_valider', methods: ['POST'])]
    public function validerCommande(Request $request, EntityManagerInterface $em, TicketGenerator $ticketGenerator, ProduitRepository $produitRepository): Response
    {
        $session = $request->getSession();
        $panier = $session->get('panier', []);
        
        if (empty($panier)) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_panier');
        }

        // Récupérer la carte prepayee de la session
        $carteId = $session->get('carte_prepayee_id');
        if (!$carteId) {
            $this->addFlash('error', 'Aucune carte n\'est sélectionnée');
            return $this->redirectToRoute('app_scan_carte');
        }

        $carte = $em->getRepository(CartePrepayee::class)->find($carteId);
        if (!$carte) {
            $this->addFlash('error', 'Carte non trouvée');
            return $this->redirectToRoute('app_scan_carte');
        }

        // Calculer le total de la commande
        $total = 0;
        foreach ($panier as $id => $quantite) {
            $produit = $produitRepository->find($id);
            if ($produit) {
                $total += $produit->getPrix() * $quantite;
            }
        }

        // Vérifier si le solde est suffisant
        if ($carte->getSolde() < $total) {
            return $this->redirectToRoute('solde_insuffisant');
        }

        // Créer la commande
        $commande = new Commande();
        $commande->setDate(new \DateTime());
        $commande->setTotal($total);
        $commande->setCartePrepayee($carte);
        $commande->setStatut('en_attente');

        // Ajouter les éléments de la commande
        foreach ($panier as $id => $quantite) {
            $produit = $produitRepository->find($id);
            if ($produit) {
                $element = new ElementCommande();
                $element->setProduit($produit);
                $element->setQuantite($quantite);
                $element->setPrixUnitaire($produit->getPrix());
                $element->setCommande($commande);
                
                // Persister chaque élément
                $em->persist($element);
            }
        }

        // Sauvegarder la commande
        $em->persist($commande);
        $em->flush();

        // Générer le ticket maintenant que nous avons l'ID
        $ticketPath = $ticketGenerator->generate($commande);
        $commande->setTicketPath($ticketPath);

        // Mettre à jour le solde de la carte
        $carte->setSolde($carte->getSolde() - $total);

        // Sauvegarder les modifications
        $em->flush();

        // Vider le panier
        $session->remove('panier');

        return $this->redirectToRoute('commande_validee', ['id' => $commande->getId()]);
    }

    #[Route('/commande-validee/{id}', name: 'commande_validee')]
    public function commandeValidee(Commande $commande): Response
    {
        $generator = new BarcodeGeneratorSVG();
        $barcode = $generator->getBarcode(
            (string)$commande->getId(),
            $generator::TYPE_CODE_128,
            2.0,
            50.0
        );
    
        return $this->render('commande/validee.html.twig', [
            'commande' => $commande,
            'barcode_svg' => $barcode,
            'hasTicket' => !empty($commande->getTicketPath())
        ]);
    }

    #[Route('/commande/ticket/{id}', name: 'commande_ticket_download')]
    public function downloadTicket(Commande $commande): BinaryFileResponse
    {
        if (!$commande->getTicketPath()) {
            throw $this->createNotFoundException('Ticket non disponible');
        }
    
        $filePath = $this->getParameter('kernel.project_dir').'/public'.$commande->getTicketPath();
        
        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'ticket-commande-'.$commande->getId().'.pdf'
        );
        
        return $response;
    }
}