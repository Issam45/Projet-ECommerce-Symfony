<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Commande;
use App\Entity\Categorie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandesController extends AbstractController
{
    private $em = null;
    private $categories;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
        $this->categories = $this->em->getRepository(Categorie::class)->findAll();
    }

    #[Route('/commandes', name: 'app_commandes')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $clientConnecte = $this->getUser();

        return $this->render('commandes/commandes.html.twig', [
            'client' => $clientConnecte, 
            'categories' => $this->categories
        ]);
    }

    // Détails d'une commande
    #[Route('/commandes/{idCommande}', name: 'app_detailsCommande')]
    public function detailsCommandes($idCommande): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $commande = $this->recupererCommande($idCommande);

        // Si la commande n'existe pas
        if($commande == null)
        {
            return $this->redirectToRoute('app_commandes');
        }

        $clientConnecte = $this->getUser();

        if($commande->getClient() != $clientConnecte)
        {
            return $this->redirectToRoute('app_commandes');
        }


        return $this->render('commandes/details.commande.html.twig', [
            'client' => $clientConnecte,
            'commande' => $commande,
            'categories' => $this->categories
        ]);
    }

    private function recupererCommande($id) {

        return $this->em->getRepository(Commande::class)->find($id);
        
    }
}
