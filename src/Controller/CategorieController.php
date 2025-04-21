<?php
// src/Controller/CategorieController.php
namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Service\BorneContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorieController extends AbstractController
{
    #[Route('/categories', name: 'app_categories')]
    public function index(
        CategorieRepository $categorieRepository,
        BorneContext $borneContext
    ): Response {
        $espace = $borneContext->getEspace();
        $categories = $categorieRepository->findBy(['espace' => $espace]);

        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
            'espace' => $espace
        ]);
    }
}