<?php
namespace App\Controller;

use App\Entity\Commande;
use App\Entity\ElementCommande;
use App\Service\TicketGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Picqer\Barcode\BarcodeGeneratorSVG;
use App\Entity\CartePrepayee;
use App\Repository\CartePrepayeeRepository;
use App\Repository\ProduitRepository;

class CommandeController extends AbstractController
{
    #[Route('/valider-commande', name: 'valider_commande', methods: ['POST'])]
    public function validerCommande(
        Request $request,
        EntityManagerInterface $em,
        ProduitRepository $produitRepository,
        CartePrepayeeRepository $carteRepo,
        TicketGenerator $ticketGenerator
    ): RedirectResponse {
        $panier = $request->getSession()->get('panier', []);
        
        if (empty($panier)) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_panier');
        }

        // Vérifier si une carte prépayée est utilisée
        $carteId = $request->getSession()->get('carte_prepayee_id');
        if ($carteId) {
            $carte = $carteRepo->find($carteId);
            if (!$carte) {
                $this->addFlash('error', 'Carte prépayée non trouvée');
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
                $this->addFlash('error', 'Solde insuffisant sur votre carte');
                return $this->redirectToRoute('app_panier');
            }

            // Mettre à jour le solde
            $carte->setSolde($carte->getSolde() - $total);
            $em->persist($carte);
        }

        // Création de la commande
        $commande = new Commande();
        $commande->setDate(new \DateTime())
                ->setStatut('en_attente');
        
        $total = 0;

        foreach ($panier as $id => $quantite) {
            $produit = $produitRepository->find($id);
            if ($produit) {
                $element = new ElementCommande();
                $element->setProduit($produit)
                       ->setQuantite($quantite)
                       ->setPrixUnitaire($produit->getPrix());

                // Persister l'ElementCommande avant de l'ajouter à la commande
                $em->persist($element);
                $commande->addElement($element);
                $total += $produit->getPrix() * $quantite;
            }
        }

        $commande->setTotal($total);
        $em->persist($commande);
        $em->flush();

        // Génération du ticket PDF
        try {
            $ticketPath = $ticketGenerator->generate($commande);
            $commande->setTicketPath($ticketPath);
            $em->flush();
        } catch (\Exception $e) {
            $this->addFlash('warning', 'Le ticket n\'a pas pu être généré: '.$e->getMessage());
        }

        // Vider le panier
        $request->getSession()->remove('panier');

        // Mettre à jour le solde dans la session
        if ($carteId) {
            $request->getSession()->set('carte_prepayee_solde', $carte->getSolde());
        }

        return $this->redirectToRoute('commande_validee', ['id' => $commande->getId()]);
    }

    #[Route('/commande-validee/{id}', name: 'commande_validee')]
    public function commandeValidee(Commande $commande): Response
    {
        $generator = new BarcodeGeneratorSVG();
        $barcode = $generator->getBarcode(
            (string)$commande->getId(),
            $generator::TYPE_CODE_128,
            2,
            50
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