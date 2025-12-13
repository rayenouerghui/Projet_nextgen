<?php

/**
 * Modèle Traitement
 * Classe représentant un traitement/réponse à une réclamation avec getters et setters
 */
class Traitement {
    // Attributs privés
    private $idTraitement;
    private $idReclamation; // Clé étrangère vers Reclamation
    private $id_user; // Clé étrangère vers users (admin/support qui traite)
    private $contenu;
    private $dateReclamation; // Date du traitement
    private $auteur; // Gardé pour compatibilité, mais id_user est utilisé en base

    /**
     * Constructeur
     */
    public function __construct() {
        // Constructeur vide pour permettre l'instanciation sans paramètres
    }

    // Getters
    public function getIdTraitement() {
        return $this->idTraitement;
    }

    public function getIdReclamation() {
        return $this->idReclamation;
    }

    public function getContenu() {
        return $this->contenu;
    }

    public function getDateReclamation() {
        return $this->dateReclamation;
    }

    public function getAuteur() {
        return $this->auteur;
    }

    public function getIdUser() {
        return $this->id_user;
    }

    // Setters
    public function setIdTraitement($idTraitement) {
        $this->idTraitement = $idTraitement;
        return $this;
    }

    public function setIdReclamation($idReclamation) {
        $this->idReclamation = $idReclamation;
        return $this;
    }

    public function setContenu($contenu) {
        $this->contenu = $contenu;
        return $this;
    }

    public function setDateReclamation($dateReclamation) {
        $this->dateReclamation = $dateReclamation;
        return $this;
    }

    public function setAuteur($auteur) {
        $this->auteur = $auteur;
        return $this;
    }

    public function setIdUser($id_user) {
        $this->id_user = $id_user;
        return $this;
    }
}
