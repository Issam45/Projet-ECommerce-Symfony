<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
#[ORM\Table(name:'categories')]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column('idCategorie')]
    private ?int $idCategorie = null;

    #[ORM\OneToMany(targetEntity:Produit::class, mappedBy:"produitCategorie", fetch:"LAZY")]
    private $produits;

    #[ORM\Column(length: 45)]
    #[Assert\Length(min:2, minMessage:"* Le nom de la catégorie doit contenir {{ limit }} caractères minimum")]
    private ?string $categorie = null;

    public function getIdCategorie(): ?int
    {
        return $this->idCategorie;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getProduit(): Collection
    {
        return $this->produits;
    }
}
