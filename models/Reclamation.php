<?php
/**
 * Modèle Reclamation
 * Classe représentant une réclamation avec getters et setters
 */
class Reclamation {
    // Attributs privés
    private $idReclamation;
    private $id_user; // Clé étrangère vers users
    private $id_jeu; // Clé étrangère vers jeu (optionnel)
    private $description;
    private $dateReclamation;
    private $statut;
    private $type;
    private $produitConcerne; // Nom du produit si id_jeu est NULL

    /**
     * Constructeur
     */
    public function __construct() {
        // Constructeur vide pour permettre l'instanciation sans paramètres
    }

    // Getters
    public function getIdReclamation() {
        return $this->idReclamation;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getDateReclamation() {
        return $this->dateReclamation;
    }

    public function getStatut() {
        return $this->statut;
    }

    public function getType() {
        return $this->type;
    }

    public function getProduitConcerne() {
        return $this->produitConcerne;
    }

    public function getIdUser() {
        return $this->id_user;
    }

    public function getIdJeu() {
        return $this->id_jeu;
    }

    // Setters
    public function setIdReclamation($idReclamation) {
        $this->idReclamation = $idReclamation;
        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function setDateReclamation($dateReclamation) {
        $this->dateReclamation = $dateReclamation;
        return $this;
    }

    public function setStatut($statut) {
        $this->statut = $statut;
        return $this;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function setProduitConcerne($produitConcerne) {
        $this->produitConcerne = $produitConcerne;
        return $this;
    }

    public function setIdUser($id_user) {
        $this->id_user = $id_user;
        return $this;
    }

    public function setIdJeu($id_jeu) {
        $this->id_jeu = $id_jeu;
        return $this;
    }
}
