<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Core\Notification;
use App\Core\NotificationCouleur;
use App\Entity\Utilisateur;
use App\Form\ModificationFormType;
use App\Form\ConnexionFormType;
use App\Form\MotDePasseFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ConnexionController extends AbstractController
{
    private $categories;

    public function __construct(EntityManagerInterface $em)
    {
        $this->categories = $em->getRepository(Categorie::class)->findAll();
    }

    #[Route('/profil', name: 'app_profil')]
    public function index(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $motDePasseHasher): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $entityManager = $doctrine->getManager();

        $currentUser = $this->getUser();

        // Section pour la modification du profil
        $form = $this->createForm(ModificationFormType::class, $currentUser);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->getRepository(Utilisateur::class)->save($currentUser, true);
            $this->addFlash('profil', new Notification('success', 'Information sauvergardé', NotificationCouleur::INFO));
            return $this->redirectToRoute('app_profil');
        }
        else if($form->isSubmitted() && !$form->isValid())
        {
            $this->addFlash('profil', new Notification('echec', 'Information invalide', NotificationCouleur::WARNING));
        }

        // Section pour la modification du mot de passe
        $formMdp = $this->createForm(MotDePasseFormType::class);
        $formMdp->handleRequest($request);

        if($formMdp->isSubmitted() && $formMdp->isValid()) {

            $estIdentique = $motDePasseHasher->isPasswordValid($currentUser, $formMdp->get('motDePasseActuel')->getData());
            $nouveauMdpHash = $motDePasseHasher->hashPassword(
                $currentUser,
                $formMdp->get('nouveauMotDePasse')->getData()
            );

            if($estIdentique && $formMdp->get('motDePasseActuel')->getData() != $formMdp->get('nouveauMotDePasse')->getData()) {
                $entityManager->getRepository(Utilisateur::class)->upgradePassword($currentUser, $nouveauMdpHash);
                $this->addFlash('mdp', new Notification('success', 'Mot de passe sauvergardé', NotificationCouleur::INFO));
                return $this->redirectToRoute('app_profil');
            }
            else{
                $this->addFlash('mdp', new Notification('echec', 'Mot de passe non sauvergardé', NotificationCouleur::WARNING));
            }
        }

        return $this->render('connexion/profil.html.twig', [
            'currentUser' => $currentUser,
            'modificationForm' => $form,
            'modificationMdpForm' => $formMdp,
            'categories' => $this->categories
        ]);
    }

    #[Route('/connexion', name: 'app_connexion')]
    public function connexion(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        if($this->getUser())
        {
            return $this->redirectToRoute('app_profil');
        }
        
        // récupérer l'erreur de cconnexion s'il y en a une
        $erreur = $authenticationUtils->getLastAuthenticationError();

        $dernierCourriel = $authenticationUtils->getLastUsername();

        return $this->render('connexion/connexion.html.twig', [
            'dernier_courriel' => $dernierCourriel,
            'erreur' => $erreur,
            'categories' => $this->categories
        ]);
    }

    #[Route('/deconnexion', name:'app_deconnexion')]
    public function deconnexion() {

    }
}
