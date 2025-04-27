<?php
// src/Service/TicketGenerator.php

namespace App\Service;

use Knp\Snappy\Pdf;
use Twig\Environment;
use App\Entity\Commande;
use Picqer\Barcode\BarcodeGeneratorSVG;

class TicketGenerator
{
    private $pdf;
    private $twig;

    public function __construct(Pdf $pdf, Environment $twig)
    {
        $this->pdf = $pdf;
        $this->twig = $twig;
    }

    public function generate(Commande $commande): string
    {
        // Générer le code-barres
        $generator = new BarcodeGeneratorSVG();
        $barcode = $generator->getBarcode(
            (string)$commande->getId(),
            $generator::TYPE_CODE_128,
            2,
            50
        );

        $html = $this->twig->render('commande/ticket.html.twig', [
            'commande' => $commande,
            'barcode_svg' => $barcode
        ]);

        $filename = 'ticket_'.$commande->getId().'.pdf';
        $pdfPath = $this->getKernelRootDir().'/../public/tickets/'.$filename;
        
        $this->pdf->generateFromHtml($html, $pdfPath);

        return '/tickets/'.$filename;
    }

    private function getKernelRootDir(): string
    {
        return dirname(__DIR__, 2);
    }
}