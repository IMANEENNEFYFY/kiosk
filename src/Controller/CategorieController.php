<?php
// src/Controller/CategorieController.php
namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Service\BorneContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class CategorieController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/categories', name: 'app_categories')]
    #[Route('/categories/{id}', name: 'app_categories_show')]
    public function index(
        Request $request,
        CategorieRepository $categorieRepository,
        BorneContext $borneContext,
        ?int $id = null
    ): Response {
        $espace = $borneContext->getEspace();
        
        // Vérifier si l'espace est un restaurant universitaire et si une carte est nécessaire
        if ($espace->getIdentifiant() === 'resto_universitaire' && !$request->getSession()->has('carte_prepayee_id')) {
            return $this->redirectToRoute('app_scan_carte');
        }

        $categories = $categorieRepository->findBy(['espace' => $espace]);
        $selectedCategory = null;

        if ($id) {
            // Charger la catégorie avec ses produits
            $dql = "SELECT c, p FROM App\Entity\Categorie c 
                    LEFT JOIN c.produits p 
                    WHERE c.id = :id AND c.espace = :espace";
            
            $query = $this->entityManager->createQuery($dql)
                ->setParameter('id', $id)
                ->setParameter('espace', $espace);
            
            try {
                $selectedCategory = $query->getSingleResult();
                $this->logger->info('Catégorie chargée avec succès', [
                    'id' => $id,
                    'nombre_produits' => count($selectedCategory->getProduits())
                ]);
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors du chargement de la catégorie', [
                    'id' => $id,
                    'erreur' => $e->getMessage()
                ]);
                return $this->redirectToRoute('app_categories');
            }
        }

        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'espace' => $espace
        ]);
    }
}