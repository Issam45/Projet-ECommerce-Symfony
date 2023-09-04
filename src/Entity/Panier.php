<?php

namespace App\Entity;

use App\Core\Constantes;

class Panier {
 
    // Array contenant tous les achats (produits)
    private $items = [];

	public function ajouterAchat(Produit $produit)
    {
        // Vérifie si le prduit est déjà dans le panier, si oui il incrémente la quantité de 1, sinon il ajoute le produit
        if(isset($this->items[$produit->getIdProduit()]))
        {
            $nouvelleQuantite = ($this->items[$produit->getIdProduit()]->getQuantite() + 1);
            $this->items[$produit->getIdProduit()]->update($nouvelleQuantite);
        }
        else{
            $achat = new Achat($produit);
            $this->items[$produit->getIdProduit()] = $achat;
        }
    }

    // MAJ 
    public function update($nouvQt) {
        if(count($this->items) > 0) // Le faire autrement plus tard mais fonctionne
        {
            $achatQuantites = $nouvQt["txtQuantite"];

            foreach($this->items as $key => $achat) {
                
                // intval permet la validation que le POST a bel et bien reçu un nombre, sinon retourne le dernier nombre enregistrer    
                if(intval($achatQuantites[$key]) > 0)
                {
                    $nouvelleQuantite = $achatQuantites[$key];
                    $achat->update($nouvelleQuantite);
                }
                else if(intval($achatQuantites[$key] == 0))
                {
                    $this->supprimerAchat($key);
                }
                    
            }
        }
    }

    // Suppresiom d'un achat, selon l'index reçu
    public function supprimerAchat($index) {
        // S'assurer que le produit est dans le panier avant de supprimer
        if(array_key_exists($index, $this->items))
            unset($this->items[$index]);
    }

    // Fonction qui permet de calculer soit le sous-total, soit la TPS ou bien la TVQ, 
    // dépendamment de qui fait appel à la fonction
    private function calculerPrix($taxes = null) {
        $total = 0;

        // Vérifie qu'il y a au moins 1 achat dans le panier
        if(count($this->items) > 0) 
        {
            foreach($this->items as $achat) // Boucle sur tous les achats et additione le total
            {
                $total += $achat->calculerPrix(); // la fonction dans achat calcule le prix multiplié par la quantité
            }
        }

        // À l'aide d'un if ternaire, on retourne le sous-total (si la fonction est appelé sans paramètre)
        // autrement on retournes les taxes, qui est le résultat du sous-total multiplié par la constantes 
        // représentant la TPS ou la TVQ
        return $taxes != null ? $total * $taxes : $total;
    }

    // Retourne la variable (array) items qui est privée
    public function getItems() {
        return $this->items;
    }

    // Récupérer le sous-total
    public function getSousTotalPanier() {

        $sousTotal = $this->calculerPrix();

        return $sousTotal;
    }

    // Récupérer le total du panier (sous-total + TPS + TVQ + frais de livraisons) = Total
    public function getTotalPanier() {

        $prixTotal = $this->getSousTotalPanier();
        $prixTotal += $this->getTPS();
        $prixTotal += $this->getTVQ();
        $prixTotal += $this->getFraisLivraisons();

        return $prixTotal;
    }

    // Récupérer le total du panier (sous-total + TPS + TVQ + frais de livraisons) = Total
    public function getTotalCommandeStripe() {
        return round($this->getTotalPanier(), 2)*100;
    }

    // Récupérer la TPS
    public function getTPS() {

        $tps = $this->calculerPrix(Constantes::TPS);

        return $tps;
    }

    // Récupérer la TVQ
    public function getTVQ() {
        
        $tvq = $this->calculerPrix(Constantes::TVQ);

        return $tvq;
    }

    // Récupérer les frais de livraisons
    public function getFraisLivraisons() {
        return (count($this->items) > 0) ? Constantes::FRAIS_LIVRAISON : 0;
    }
   
}