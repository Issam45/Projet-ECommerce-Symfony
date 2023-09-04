<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Table(name:'produits')]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column('idProduit')]
    private ?int $idProduit = null;

    #[ORM\Column(length: 90)]
    #[Assert\Length(min:2, minMessage:"* Le nom du produit doit contenir {{ limit }} caractères minimum")]
    private ?string $nom = null;

    #[ORM\Column]
    #[Assert\Range(min: 0.25, minMessage:"* Le prix minimum doit être d'au moins {{ limit }}$" )]
    #[Assert\Range(max: 2000.00, maxMessage:"* Le prix maximum ne doit pas excèder {{ limit }}$" )]
    private ?float $prix = null;

    #[ORM\Column('quantiteEnStock')]
    #[Assert\Range(min: 0.25, minMessage:"* Le prix minimum doit être d'au moins {{ limit }}" )]
    private ?int $quantiteEnStock = null;

    #[ORM\Column(length: 2000)]
    #[Assert\Length(min:2, minMessage:"* Votre description doit contenir {{ limit }} caractères minimum")]
    private ?string $description = null;

    #[ORM\Column(('imagePath'),length: 255)]
    private ?string $imagePath = null;

    #[ORM\ManyToOne(targetEntity:Categorie::class, inversedBy:"produits", cascade:["persist"])]
    #[ORM\JoinColumn(name:'idCategorie', referencedColumnName:'idCategorie')]
    private ?Categorie $produitCategorie;

    public function getIdProduit(): ?int
    {
        return $this->idProduit;
    }


    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getQuantiteEnStock(): ?int
    {
        return $this->quantiteEnStock;
    }

    public function setQuantiteEnStock(int $q): self
    {
        $this->quantiteEnStock = $q;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imgPath): self
    {
        $this->imagePath = $imgPath;

        return $this;
    }

    public function getProduitCategorie(): ?Categorie
    {
        return $this->produitCategorie;
    }

    public function setProduitCategorie(Categorie $cat): self
    {
        //dd($cat);
        $this->produitCategorie = $cat;

        return $this;
    }

    private function diminuerQuantite(int $quantite)
    {
        $this->quantiteEnStock = $this->quantiteEnStock - $quantite;
    }

    public function getNbProduitVenduEpuise($q){
        return $this->calculerNbProduitEpuiseVendu($q) <= 0 ? 0 : $this->calculerNbProduitEpuiseVendu($q);
    }

    private function calculerNbProduitEpuiseVendu($q)
    {
        return $this->quantiteEnStock <= 0 ? $q : $q - $this->quantiteEnStock;
    }

    // Faire une vente
    public function vendu($q) {
        
        //dd($this->quantite);    
        //dd($this->quantiteEnStock);
        
        // Lorsque la quantité est égal à zéro la variable est null,
        // donc forcé le zéro.
        // TODO: verifier s'il n'y a pas une meileure façon
        if($this->quantiteEnStock == null)
        {
            //dd("test");
            $this->quantiteEnStock = 0;
        }
        
        // Si la quantité vendu est plus grande que la quantité en inventaire
        // Alors il y épuisement dans l'inventaire
        if($q > $this->quantiteEnStock)
        {
            // Après les vérifications il faut diminuer l'inventaire
            $this->diminuerQuantite($q);
            return true;
        }
        else
        {
            // Après les vérifications il faut diminuer l'inventaire
            $this->diminuerQuantite($q);
            return false;
        }
    }
}