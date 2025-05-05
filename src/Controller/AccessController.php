<?php

namespace App\Controller;

use App\Entity\CartePrepayee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AccessController extends AbstractController
{
    #[Route('/solde-insuffisant', name: 'solde_insuffisant')]
    public function soldeInsuffisant(): Response
    {
        return $this->render('access/solde_insuffisant.html.twig');
    }

    public function verifierAcces(Request $request, EntityManagerInterface $em): Response
    {
        $qrCode = $request->request->get('qrCode');
        $carte = $em->getRepository(CartePrepayee::class)->findOneBy(['qrCode' => $qrCode]);

        if (!$carte) {
            return $this->json(['success' => false, 'message' => 'Carte non trouvée']);
        }

        // Vérifier le solde
        if ($carte->getSolde() <= 0) {
            return $this->redirectToRoute('solde_insuffisant');
        }

        // Si le solde est suffisant, accorder l'accès
        return $this->json(['success' => true, 'message' => 'Accès accordé']);
    }
    #[Route('/carte_sans_solde', name: 'app_carte_sans_solde')]
    public function index(): Response
    {
        return $this->render('access/carte_sans_solde.html.twig');
    }
} 