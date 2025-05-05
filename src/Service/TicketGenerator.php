<?php
// src/Service/TicketGenerator.php

namespace App\Service;

use App\Entity\Commande;
use Picqer\Barcode\BarcodeGeneratorSVG;
use TCPDF;

class TicketGenerator
{
    private string $ticketDir;

    public function __construct(string $ticketDir)
    {
        $this->ticketDir = $ticketDir;
    }

    public function generate(Commande $commande): string
    {
        // Créer une instance de TCPDF
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configuration du document
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Borne Commande');
        $pdf->SetTitle('Ticket de commande #' . $commande->getId());

        // Supprimer l'en-tête et le pied de page par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Ajouter une page
        $pdf->AddPage();

        // Style pour le titre
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Ticket de commande #' . $commande->getId(), 0, 1, 'C');
        $pdf->Ln(10);

        // Style pour les informations de la commande
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Date: ' . $commande->getDate()->format('d/m/Y H:i'), 0, 1);
        $pdf->Cell(0, 10, 'Total: ' . number_format($commande->getTotal(), 2) . ' €', 0, 1);
        $pdf->Ln(10);

        // Style pour le tableau des produits
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(100, 10, 'Produit', 1);
        $pdf->Cell(30, 10, 'Quantité', 1);
        $pdf->Cell(30, 10, 'Prix', 1);
        $pdf->Cell(30, 10, 'Total', 1);
        $pdf->Ln();

        // Style pour les lignes du tableau
        $pdf->SetFont('helvetica', '', 12);
        $produitsStr = '';
        foreach ($commande->getElements() as $element) {
            $pdf->Cell(100, 10, $element->getProduit()->getNom(), 1);
            $pdf->Cell(30, 10, $element->getQuantite(), 1);
            $pdf->Cell(30, 10, number_format($element->getPrixUnitaire(), 2) . ' €', 1);
            $pdf->Cell(30, 10, number_format($element->getPrixUnitaire() * $element->getQuantite(), 2) . ' €', 1);
            $produitsStr .= $element->getProduit()->getNom() . 'x' . $element->getQuantite() . ', ';
            $pdf->Ln();
        }
        $produitsStr = rtrim($produitsStr, ', ');
   
        $barcodeData = 'CMD#' . $commande->getId()
            . '|Date:' . $commande->getDate()->format('d/m/Y H:i')
            . '|Total:' . number_format($commande->getTotal(), 2) . '€'
            . '|Statut:' . $commande->getStatut()
            . '|Carte:' . $commande->getCartePrepayee()->getCodeCarte()
            . '|Client:' . $commande->getCartePrepayee()->getNom() . ' ' . $commande->getCartePrepayee()->getPrenom()
            . '|Produits:' . $produitsStr;
        
        // Ajouter le code-barres
        $generator = new BarcodeGeneratorSVG();
        $barcode = $generator->getBarcode(
            (string)$commande->getId(),
            $generator::TYPE_CODE_128,
            2.0,
            50.0
        );
        
        // Centrer le code-barres
        $pdf->Ln(10);
        $pdf->SetX(($pdf->getPageWidth() - 100) / 2);
        $pdf->writeHTML($barcode, true, false, true, false, 'C');

        // Générer le nom du fichier
        $filename = 'ticket_' . $commande->getId() . '.pdf';
        $filepath = $this->ticketDir . '/' . $filename;

        // Sauvegarder le PDF
        $pdf->Output($filepath, 'F');

        return '/tickets/' . $filename;
    }
}