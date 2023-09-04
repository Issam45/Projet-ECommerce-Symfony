<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Panier;

use App\Entity\Categorie;
use App\Core\Notification;
use App\Core\NotificationCouleur;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class PanierController extends AbstractController
{
    private $em = null;
    private $panier;
    private $prixTotal;
    private $categories;

    public function __construct(EntityManagerInterface $em)
    {
        $this->categories = $em->getRepository(Categorie::class)->findAll();
    }

    #[Route('/panier', name: 'app_panier')]
    public function index(Request $request): Response
    {
        $this->initSession($request);
        $session = $request->getSession();

        if(count($this->panier->getItems()) == 0)
         $this->addFlash('panier', new Notification('success', 'Le panier est vide', NotificationCouleur::INFO));

        return $this->render('panier/panier.html.twig', [
            'panier' => $this->panier,
            'categories' => $this->categories
        ]);
    }

    #[Route('/panier/ajout/{idProduit}', name: 'panier_ajout')]
    public function ajoutPanier($idProduit, Request $request, ManagerRegistry $doctrine): Response
    {
        // S'assurer que le panier est initialiser dans la session
        $this->initSession($request);

        //Retrouver le produit
        $this->em = $doctrine->getManager();
        $produit = $this->em->getRepository(Produit::class)->find($idProduit);

        if($produit)
        {
            $this->panier->ajouterAchat($produit);
            $this->addFlash('panier', new Notification('success', 'Produit ajoutée avec succès', NotificationCouleur::SUCCESS));
        }

        return $this->redirectToRoute('app_panier');

    }

    #[Route('/panier/update', name: 'panier_update', methods:['POST'])]
    public function updatePanier(Request $request)
    {
        // Valider que le panier est dans la session
        $this->initSession($request);

        // Récupérer ce qu'il y a dans le post de l'URL
        $post = $request->request->all();

        // Récupérer l'action dans l'URL
        $action = $request->get('action');

        // Verifier l'action dans l'URL
        if($action == "rafraichir")
        {
            // Appel de la fonction update pour mettre à jour le panier
            $this->panier->update($post);

            // Afficher notification
            $this->addFlash('panier', new Notification('success', 'Produits sauvegardés', NotificationCouleur::INFO));

        } else if($action == "vider") {
            // Récupérer la session
            $session = $request->getSession();
            // Retirer la variable de session panier
            $session->remove('panier');
        }

        // Rediriger vers panier
        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/supprimer/{index}', name: 'achat_supprimer')]
    public function supprimerPanier($index ,Request $request) : Response
    {
        // Valider que le panier est dans la session
        $this->initSession($request);

        // Afficher notification
        $this->addFlash('panier', new Notification('success', 'Produit supprimé', NotificationCouleur::SUCCESS));

        // Appel de la fonction supprimer pour supprrimer un produit
        $this->panier->supprimerAchat($index);

        // Rediriger vers panier
        return $this->redirectToRoute('app_panier');
    }

    private function initSession(Request $request) {

        // Récupérer la session
        $session = $request->getSession();

        //S'assure que le panier existe sinon il fait un new Panier
        $this->panier = $session->get('panier', new Panier());
        $session->set('panier', $this->panier);
    }
}
