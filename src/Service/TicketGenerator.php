<?php
// src/Service/TicketGenerator.php

namespace App\Service;

use Knp\Snappy\Pdf;
use Twig\Environment;
use App\Entity\Commande;

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
        $html = $this->twig->render('commande/ticket.html.twig', [
            'commande' => $commande
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