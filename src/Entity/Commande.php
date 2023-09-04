<?php

namespace App\Entity;

use App\Core\Constantes;
use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\Table(name:'commandes')]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:'idCommande')]
    private ?int $idCommande = null;

    #[ORM\Column(name:'dateCommande', type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $dateCommande = null;

    #[ORM\Column(name:'dateLivraison', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateLivraison = null;

    #[ORM\Column(name:'tauxTPS')]
    private ?float $tauxTPS = null;

    #[ORM\Column(name:'tauxTVQ')]
    private ?float $tauxTVQ = null;

    #[ORM\Column(name:'fraisLivraison')]
    private ?float $fraisLivraison = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\Column(name:'stripeIntent', length: 255)]
    private ?string $stripeIntent = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: Achat::class, orphanRemoval: true)]
    private Collection $achats;

    // TODO: Demander au prof si il Faut-il mettre le casacade[persist] (pas supposer mais demander pr etre sur)
    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(name:'idClient', referencedColumnName:'idUtilisateur', nullable: false)]
    private ?Utilisateur $client = null;

    // Mettre le user et le payment intent requis dans le constructeur
    public function __construct(Utilisateur $utilisateur, string $intent)
    {
        $this->client = $utilisateur;
        $this->stripeIntent = $intent;
        $this->dateCommande = new \DateTime(); // Retourne la date actuel
        $this->etat = Constantes::LISTE_ETAT[0]; // A changer lors du TP Final pour un enum probablement
        $this->tauxTPS = Constantes::TPS;
        $this->tauxTVQ = Constantes::TVQ;
        $this->fraisLivraison = Constantes::FRAIS_LIVRAISON;
        $this->achats = new ArrayCollection();

        // foreach($panier->getItems() as $achat)
        // {
        //     $this->achats->add($achat);
        //     $achat->setCommande($this);
        // }
        
        //dd("test");
        
    }

    // Retourne le nombre d'items dans une commande
    public function getNbItems(){
        $q = 0;
        foreach($this->achats as $achat){
            $q += $achat->getQuantite();
        }
        return $q;
    }

    public function getIdCommande(): ?int
    {
        return $this->idCommande;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(): self
    {
        $this->dateLivraison = new \DateTime();

        return $this;
    }

    public function getTauxTPS(): ?float
    {
        return $this->tauxTPS;
    }

    public function getTauxTVQ(): ?float
    {
        return $this->tauxTVQ;
    }

    public function getFraisLivraison(): ?float
    {
        return $this->fraisLivraison;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getStripeIntent(): ?string
    {
        return $this->stripeIntent;
    }

    /**
     * @return Collection<int, Achat>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Achat $item): self
    {
        if (!$this->achats->contains($item)) {
            $this->achats->add($item);
            $item->setCommande($this);
        }

        return $this;
    }

    public function removeItem(Achat $item): self
    {
        if ($this->achats->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getCommande() === $this) {
                $item->setCommande(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Achat>
     */
    public function getAchats(): Collection
    {
        return $this->achats;
    }

    public function addAchat(Achat $achat): self
    {
        if (!$this->achats->contains($achat)) {
            $this->achats->add($achat);
            $achat->setCommande($this);
        }

        return $this;
    }

    public function removeAchat(Achat $achat): self
    {
        if ($this->achats->removeElement($achat)) {
            // set the owning side to null (unless already changed)
            if ($achat->getCommande() === $this) {
                $achat->setCommande(null);
            }
        }

        return $this;
    }

    public function getClient(): ?Utilisateur
    {
        return $this->client;
    }

    // Fonction qui permet de calculer soit le sous-total, soit la TPS ou bien la TVQ, 
    // dépendamment de qui fait appel à la fonction
    private function calculerPrix($taxes = null) {
        $total = 0;

        foreach($this->achats as $achat) // Boucle sur tous les achats et additione le total
        {
            $total += $achat->calculerPrix(); // la fonction dans achat calcule le prix multiplié par la quantité
        }
        
        // À l'aide d'un if ternaire, on retourne le sous-total (si la fonction est appelé sans paramètre)
        // autrement on retournes les taxes, qui est le résultat du sous-total multiplié par la constantes 
        // représentant la TPS ou la TVQ
        return $taxes != null ? $total * $taxes : $total;
    }

    private function calculerTotalCommande(){
        $prixTotal = $this->getSousTotalCommande();
        $prixTotal += $this->getTPS();
        $prixTotal += $this->getTVQ();
        $prixTotal += $this->getFraisLivraison();

        return $prixTotal;
    }

    // Récupérer le sous-total
    public function getSousTotalCommande() {

        $sousTotal = $this->calculerPrix();

        return $sousTotal;
    }

    // Récupérer le total de la commande (sous-total + TPS + TVQ + frais de livraisons) = Total
    public function getTotalCommande() {
        return $this->calculerTotalCommande();
    }

    // Récupérer la TPS avec le taux stocker lors de la commande
    public function getTPS() {

        $tps = $this->calculerPrix($this->tauxTPS);

        return $tps;
    }

    // Récupérer la TVQ avec le taux stocker lors de la commande
    public function getTVQ() {
        
        $tvq = $this->calculerPrix($this->tauxTVQ);

        return $tvq;
    }
}
