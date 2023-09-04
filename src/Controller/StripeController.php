<?php

namespace App\Controller;

use App\Core\Notification;
use App\Core\NotificationCouleur;
use App\Entity\Commande;
use App\Entity\Categorie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Stripe;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
    private $em = null;
    private $categories;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
        $this->categories = $this->em->getRepository(Categorie::class)->findAll();
    }

    #[Route('/stripe-checkout', name: 'stripe_checkout')]
    public function stripeCheckout(Request $request): Response {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Récupérer l'utilisateur
        $user = $this->getUser();

        // Récupérer la session
        $session = $request->getSession();

        // Récupérer le panier
        $panier = $session->get('panier');

        if($session->get("panier") == null)
            return $this->redirectToRoute('app_panier');

        \Stripe\Stripe::setApiKey($_ENV["STRIPE_SECRET"]);

        //https:monDomaine.test/stripe-success?session_id={CHECKOUT_SESSION_ID}
        $successURL = $this->generateUrl('stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . "?stripe_id={CHECKOUT_SESSION_ID}";

        $sessionData = [
            'line_items' => [[
                'quantity' => 1,
                'price_data' => ['unit_amount' => $panier->getTotalCommandeStripe(), 'currency' => 'CAD', 'product_data' => ['name' => 'Microtransaction CosmeticsIA' ]]
            ]],
            'customer_email' => $user->getCourriel(),
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'success_url' => $successURL,
            'cancel_url' => $this->generateUrl('stripe_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ];

        $checkoutSession = \Stripe\Checkout\Session::create($sessionData);
        return $this->redirect($checkoutSession->url, 303);
    }


    #[Route('/stripe-success', name: 'stripe_success')]
    public function stripeSuccess(Request $request) : Response {
        // Ne pas autoriser si aucun utilisateur est connécté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        // Récupérer la session
        $session = $request->getSession();

        if($session->get("panier") != null)
        {
            try {
                
                $stripe = new \Stripe\StripeClient($_ENV["STRIPE_SECRET"]);

                $stripeSessionId = $request->query->get('stripe_id');
                $sessionStripe = $stripe->checkout->sessions->retrieve($stripeSessionId);
                $paymentIntent = $sessionStripe->payment_intent;

                $panier = $session->get("panier");
                $achats = $panier->getItems();
                $commande = new Commande($user, $paymentIntent);

                // Pour accumuler des string et former un message de notification final
                $message = "";
                // Au départ à faux, change si la commande contient au moins un produit en rupture d'inventaire
                $epuise = false; 

                foreach($achats as $achat)
                {
                    // Pour envoyé en BD les achats il faut les merges avant
                    $achatMerged = $this->em->merge($achat);
                    $commande->addItem($achatMerged);
                    
                    $quantiteVenduEpuisee = $achatMerged->getProduit()->getNbProduitVenduEpuise($achat->getQuantite());
                    
                    if($achatMerged->getProduit()->vendu($achatMerged->getQuantite()))
                    {
                        $epuise = true;
                        if($quantiteVenduEpuisee > 1)
                            $message .= "Le produit <strong>{$achatMerged->getProduit()->getNom()}</strong> est présentement en rupture d'inventaire, <strong>{$quantiteVenduEpuisee}</strong> produits excèdent notre inventaire! <br>";
                        else    
                            $message .= "Le produit <strong>{$achatMerged->getProduit()->getNom()}</strong> est présentement en rupture d'inventaire, <strong>{$quantiteVenduEpuisee}</strong> produit excède notre inventaire! <br>";
                    }   
                }

                if($epuise)
                {
                    $message .= "<strong>Les produits manquants vous seront livrés dès qu'ils seront disponibles.</strong>";
                    $this->addFlash('commande', new Notification('success', $message, NotificationCouleur::WARNING));
                }

                $this->em->persist($commande);
                $this->em->flush();
                
            } catch(\Exception $e) {
                return $this->redirectToRoute('app_panier');
            }
        }
        else
        {
            // IA: Si le panier est vide, dans le cas précis lorsqu'un utilisateur passe une commande
            // et effectue le paiement une fois sur la page de détails s'il clique sur retour (sur son browser)
            // j'ai testé et remarquer que les infos du panier sont toujours la même s'il fut retirer de la session,
            // donc il y avait 2 solutions mettre un 'catch' ici et dans les autres routes stripes pour le retourner vers le panier avec une notification
            // ou bien de directement dans le controller de revue rediriger vers le panier avec une notification
            // pour être complètement sécuritaire j'ai fais les 2
            $this->addFlash('commande', new Notification('success', 'Votre paiement n\'a pas été finalisé. Veuillez réessayer pour terminer votre commande!', NotificationCouleur::DANGER));
            return $this->redirectToRoute('app_panier');
        }


        $session->remove("panier");

        // Redirection vers les détails de la commande
        return $this->redirectToRoute('app_detailsCommande', [
            'idCommande' => $commande->getIdCommande()]);
    }

    #[Route('/stripe-cancel', name: 'stripe_cancel')]
    public function stripeCancel() : Response {
        $this->addFlash('commande', new Notification('success', 'Votre paiement n\'a pas été finalisé. Veuillez réessayer pour terminer votre commande!', NotificationCouleur::DANGER));
        return $this->redirectToRoute('app_panier');
    }

}
