<?php

class Categorie
{
    private $id_categorie;
    private $nom_categorie;
    private $description;

    public function __construct($nom_categorie, $description = null, $id_categorie = null)
    {
        $this->id_categorie = $id_categorie;
        $this->nom_categorie = $nom_categorie;
        $this->description = $description;
    }

    
    public function getIdCategorie() { return $this->id_categorie; }
    public function getNomCategorie() { return $this->nom_categorie; }
    public function getDescription() { return $this->description; }

    
    public function setNomCategorie($nom) { $this->nom_categorie = $nom; }
    public function setDescription($desc) { $this->description = $desc; }
}