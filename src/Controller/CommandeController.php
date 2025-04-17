<?php
namespace App\Controller;

use App\Entity\Commande;
use App\Service\PanierService;
use App\Entity\ElementCommande;
use App\Service\TicketGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Picqer\Barcode\BarcodeGeneratorSVG;

class CommandeController extends AbstractController
{
    #[Route('/valider-commande', name: 'valider_commande', methods: ['POST'])]
    public function validerCommande(
        Request $request,
        PanierService $panierService,
        EntityManagerInterface $em,
        TicketGenerator $ticketGenerator
    ): RedirectResponse {
        $panierComplet = $panierService->getPanierComplet();
        
        if (empty($panierComplet)) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('afficher_panier'); // Correction du nom de route (panier au lieu de panier)
        }

        // Création de la commande
        $commande = new Commande();
        $total = 0;

        foreach ($panierComplet as $item) {
            $element = new ElementCommande();
            $element->setProduit($item['produit'])
                   ->setQuantite($item['quantite'])
                   ->setPrixUnitaire($item['produit']->getPrix());

            $commande->addElement($element);
            $total += $item['produit']->getPrix() * $item['quantite'];
        }

        $commande->setMontantTotal($total)
                 ->setDateCreation(new \DateTime());
        
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

        $panierService->viderPanier();

        return $this->redirectToRoute('commande_validee', ['id' => $commande->getId()]);
    }

    #[Route('/commande-validee/{id}', name: 'commande_validee')]
    public function commandeValidee(Commande $commande): Response
    {
        $generator = new BarcodeGeneratorSVG(); // Utilisez SVG au lieu de PNG
        $barcode = $generator->getBarcode(
            (string)$commande->getId(),
            $generator::TYPE_CODE_128,
            2, // Épaisseur
            50 // Hauteur
        );
    
        return $this->render('commande/validee.html.twig', [
            'commande' => $commande,
            'barcode_svg' => $barcode, // Variable renommée
            'hasTicket' => !empty($commande->getTicketPath())
        ]);
    }

    #[Route('/commande/ticket/{id}', name: 'commande_ticket_download')]
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