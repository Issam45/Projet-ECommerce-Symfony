<?php

namespace App\Entity;

use App\Repository\AchatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AchatRepository::class)]
#[ORM\Table(name:'achats')]
class Achat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:'idAchat')]
    private ?int $idAchat = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\ManyToOne(inversedBy: 'achats', cascade:["persist"])]
    #[ORM\JoinColumn(name:'idCommande', referencedColumnName:'idCommande', nullable: false)]
    private ?Commande $commande = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name:'idProduit', referencedColumnName:'idProduit', nullable: false)]
    private ?Produit $produit = null;

    // récupérer produit et par la suite stocké aussi son prix dans une variable
    public function __construct(Produit $produit) {
        $this->produit = $produit;
        $this->prix = $produit->getPrix();
        $this->quantite = 1;
    }

    // je pourrais retirer le produit laisser juste la quantite a update puisque le produit ne changera pas
    public function update($quantite) {
        $this->quantite = $quantite;
    }

    // Prix du produit multiplié par la quantité
    public function calculerPrix() {
        return $this->prix * $this->quantite;
    }

    public function getIdAchat(): ?int
    {
        return $this->idAchat;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

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

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;

        return $this;
    }

    // Récupérer le produit
    public function getProduit() {
        return $this->produit;
    }

    // Affecter le produit
    public function setProduit(Produit $produit) {
         $this->produit = $produit;
    }

    public function majQuantiteEnStock()
    {
        $this->produit->diminuerQuantite($this->quantite);
    }
}
