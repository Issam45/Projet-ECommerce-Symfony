<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Core\Notification;
use App\Core\NotificationCouleur;
use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    private $categories;

    public function __construct()
    {
        $this->categories = $this->em->getRepository(Categorie::class)->findAll();
    }

    #[Route('/inscription', name: 'app_inscription')]
    public function register(Request $request, 
                            UserPasswordHasherInterface $userPasswordHasher,
                            Security $security,
                            EntityManagerInterface $entityManager): Response
    {
        // Si l'utilisateur est déjà connecté, il se fera rediriger vers son profile
        if($this->getUser())
        {
            return $this->redirectToRoute('app_profil');
        }

        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            $security->login($user);

            $this->addFlash('profil', new Notification('success', 'Votre profil a été créé avec succès!', NotificationCouleur::INFO));

            return $this->redirectToRoute('app_profil');
        }
        else if ($form->isSubmitted() && !$form->isValid()) { // Si le form est soumis, mais n'est pas valide
            $this->addFlash('inscription', new Notification('echec', 
            'Le formulaire d\'inscription contient des erreurs. 
             Veuillez vérifier les cases en surbrillance rouge et les corriger avant de soumettre à nouveau votre formulaire..',
             NotificationCouleur::DANGER));
        }

        return $this->render('registration/inscription.html.twig', [
            'registrationForm' => $form->createView(),
            'categories' => $this->categories
        ]);
    }
}
