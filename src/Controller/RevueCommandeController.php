<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Core\Notification;
use App\Core\NotificationCouleur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RevueCommandeController extends AbstractController
{
    private $categories;

    public function __construct()
    {
        $this->categories = $this->em->getRepository(Categorie::class)->findAll();
    }

    #[Route('/revue', name: 'app_revue_commande')]
    public function index(Request $request): Response
    {
        // Récupérer la session, ensuite le panier
        $session = $request->getSession();
        $panier = $session->get('panier');

        $user = $this->getUser();

        // Si aucun utilisateur n'est connecté alors rediriger vers la connexion
        if($user == null)
        {
            return $this->redirectToRoute('app_connexion');
        }

        if($session->get('panier') != null)
        {
            // Rediriger vers le panier si le panier est vide
            if(count($panier->getItems()) == 0 )
            {
                $this->addFlash('commande', new Notification('success', 'Votre panier doit contenir au moins 1 produit avant de passer à la caisse!', NotificationCouleur::DANGER));
                return $this->redirectToRoute('app_panier');
            }
        }
        else{
            return $this->redirectToRoute('app_panier');
        }


        return $this->render('revue_commande/revueCommande.html.twig', [
            'categories' => $this->categories
        ]);
    }
}
