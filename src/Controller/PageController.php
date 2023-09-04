<?php

namespace App\Controller;

use App\Entity\Categorie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class PageController extends AbstractController
{
    private $categories;

    public function __construct(EntityManagerInterface $em)
    {
        $this->categories = $em->getRepository(Categorie::class)->findAll();
    }

    #[Route('/contact', name: 'app_contact')]
    public function index(): Response
    {
        return $this->render('contact/contact.html.twig', [
            'categories' => $this->categories
        ]);
    }
}
