<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieCollection;
use App\Form\CategorieCollectionFormType;

use App\Entity\Produit;
use App\Form\ProduitFormType;

use App\Entity\Commande;

use App\Core\Notification;
use App\Core\NotificationCouleur;
use App\Core\Constantes;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_ADMIN', statusCode: 423)]
class AdminController extends AbstractController
{
    private $em;
    private $categories;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->categories = $em->getRepository(Categorie::class)->findAll();
    }

    #[Route('/admin/categories', name: 'app_admin_categories')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if(!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_connexion');
        }

        //$categories = $this->em->getRepository(Categorie::class)->findAll();
        $categoriesCollection = new CategorieCollection($this->categories);
        
        $formCategories = $this->createForm(CategorieCollectionFormType::class, $categoriesCollection);
        $formCategories->handleRequest($request);
        
        if($formCategories->isSubmitted() && $formCategories->isValid()) 
        {
            $newCollectionCategories = $formCategories->getData()->getCategories();

            foreach($newCollectionCategories as $newCategorie)
            {
                $this->em->persist($newCategorie);
            }
            $this->em->flush();

            $this->addFlash('categ', new Notification('success', 'Catégories sauvegardées', NotificationCouleur::SUCCESS));
            $this->categories = $this->em->getRepository(Categorie::class)->findAll();
        }
        else if( $formCategories->isSubmitted() && !$formCategories->isValid())
        {
            $this->addFlash('categ', new Notification('success', 'Erreur lors de la sauvegarde', NotificationCouleur::DANGER));
        }

        return $this->render('admin/categories.html.twig', [
            'formCategories' => $formCategories,
            'categories' => $this->categories
        ]);
    }

    #[Route('/admin/produits', name: 'app_admin_produits')]
    public function recupererProduits(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if(!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_connexion');
        }
    
        $produits = $this->em->getRepository(Produit::class)->findAll();

        return $this->render('admin/produits.html.twig', [
            'produits' => $produits,
            'categories' => $this->categories
        ]);
    }

    #[Route('/admin/nouveau/produit', name: 'app_admin_nouveauProduit')]
    public function nouveauProduit(Request $request, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if(!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_connexion');
        }
    
        $produit = new Produit();
        $formProduit = $this->createForm(ProduitFormType::class, $produit);
        
        $formProduit->handleRequest($request);
        
        if($formProduit->isSubmitted() && $formProduit->isValid()) 
        {
            //dd($formProduit->get('imagePath')->getData());
            $img = $formProduit->get('imagePath')->getData();
            if($img)
            {
                $originalFilename = pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . "-" . uniqid() . ".". $img->guessExtension(); 

                try {
                    $img->move(
                        $this->getParameter('image_produit_directory'),
                        $newFilename);

                    $produit->setImagePath("/Images/Produits/".$newFilename);
                    $this->em->persist($produit);
                    $this->em->flush();

                } catch(FileException $e) {
                    //TODO: Erreur
                } catch(ORMException $e) {
                    //TODO: Erreur
                }
            }
            else 
            {
                $produit->setImagePath("");
            }

            $this->em->getRepository(Produit::class)->save($produit, true);

            $this->addFlash('produit', new Notification('produit', "Le produit {$produit->getNom()} a été ajouté", NotificationCouleur::SUCCESS));

            return $this->redirectToRoute('app_admin_modifierProduit', [
                'idProduit' => $produit->getIdProduit()]);
        }

        return $this->render('admin/produit.html.twig', [
            'formProduit' => $formProduit,
            'titre' => 'Nouveau',
            'categories' => $this->categories
        ]);
    }

    #[Route('/admin/modifier/produit/{idProduit}', name: 'app_admin_modifierProduit')]
    public function modifierProduit($idProduit, Request $request, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if(!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_connexion');
        }
    
        $produit = $this->em->getRepository(Produit::class)->find($idProduit);

        $formProduit = $this->createForm(ProduitFormType::class, $produit);
        
        $formProduit->handleRequest($request);
        
        if($formProduit->isSubmitted() && $formProduit->isValid()) 
        {
            $img = $formProduit->get('imagePath')->getData();
            
            if($img)
            {
                $originalFilename = pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . "-" . uniqid() . ".". $img->guessExtension(); 

                try {
                    $img->move(
                        $this->getParameter('image_produit_directory'),
                        $newFilename);

                    $produit->setImagePath("/Images/Produits/".$newFilename);
                    $this->em->persist($produit);

                } catch(FileException $e) {
                    //TODO: Erreur
                } catch(ORMException $e) {
                    //TODO: Erreur
                }
            }
            else 
            {
                $this->em->persist($produit);
            }
            
            $this->em->flush();
            //$this->em->getRepository(Produit::class)->save($produit, true);
            $this->addFlash('produit', new Notification('produit', "Le produit {$produit->getNom()} a été modifié", NotificationCouleur::SUCCESS));
        }

        return $this->render('admin/produit.html.twig', [
            'formProduit' => $formProduit,
            'titre' => "Modification",
            'categories' => $this->categories
        ]);
    }

    #[Route('/admin/commandes', name: 'app_admin_commandes')]
    public function recupererCommandes(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if(!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_connexion');
        }

        $commandes = $this->em->getRepository(Commande::class)->findAll();

        return $this->render('admin/commandes.html.twig', [
            'commandes' => $commandes,
            'categories' => $this->categories
        ]);
    }

    // Détails d'une commande Admin
    #[Route('admin/commandes/{idCommande}', name: 'app_admin_detailsCommande', methods:['POST', 'GET'])]
    public function detailsCommandes($idCommande, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    
        $commande = $this->recupererCommande($idCommande);

        // Récupérer ce qu'il y a dans le post de l'URL
        $post = $request->request->all();

        if($post)
        {
            foreach (Constantes::LISTE_ETAT as $key => $etat) {
                if($key + 1 == $post['selectedEtat'])
                {
                    $commande->setEtat(Constantes::LISTE_ETAT[$key]);

                    if($etat == "Livrée")
                    {
                        $commande->setDateLivraison();
                    }
                }
    
             }

             $this->em->getRepository(Commande::class)->save($commande, true);

        }
    
        // Si la commande n'existe pas
        if($commande == null)
        {
            return $this->redirectToRoute('app_commandes');
        }
      
        return $this->render('admin/details.commande.html.twig', [
            'commande' => $commande,
            'listeEtat' => Constantes::LISTE_ETAT,
            'categories' => $this->categories
        ]);
    }
    
    private function recupererCommande($id) {
        return $this->em->getRepository(Commande::class)->find($id);       
    }
}
