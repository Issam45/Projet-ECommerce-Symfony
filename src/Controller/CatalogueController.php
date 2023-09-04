<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Categorie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CatalogueController extends AbstractController
{
    private $em = null;

    #[Route('/', name: 'app_catalogue')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->em = $doctrine->getManager();
       
        $categorie = $request->query->get('categorie');
        $caseRecherche = $request->request->get('case_recherche'); // $_POST['case_recherche']

        $categories = $this->recupererCategories();

        $produits = $this->recupererProduits($categorie, $caseRecherche);

        $nbTotalProduits = $this->recupererProduitsTotal();

        return $this->render('catalogue/catalogue.html.twig', [
            'produits' => $produits, 'categories' => $categories, 'nbTotalProduits' => $nbTotalProduits,
            'categorieChoisi' => $categorie
        ]);
    }

    #[Route('/produits/{idProduit}', name:'produit_modal')]
    public function infoProduit($idProduit, Request $request, ManagerRegistry $doctrine): Response {
        //2 Philosophies -> JSON, HTML

        $this->em = $doctrine->getManager();

        $produit = $this->em->getRepository(Produit::class)->find($idProduit);

        return $this->render('catalogue/produit.modal.twig', ['produit' => $produit]);

    }

    private function recupererProduits($categorie, $caseRecherche) {

        return $this->em->getRepository(Produit::class)->rechercheAvecCritere($categorie, $caseRecherche);
        
    }

    private function recupererProduitsTotal() {

        return $this->em->getRepository(Produit::class)->findAll();
        
    }

    private function recupererCategories() {

        return $this->em->getRepository(Categorie::class)->findAll();
        
    }

    private function retrieveProduitsFromCategorie($categorie) {

        return $this->em->getRepository(Categorie::class)->find($categorie)->getChampions();

    }
}
