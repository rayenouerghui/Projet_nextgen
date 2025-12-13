<?php
class Jeu
{
    private ?int $id_jeu = null;
    private string $titre;
    private float $prix;
    private string $src_img;
    private ?int $id_categorie = null;
    private ?string $description = null;
    private ?string $video_src = null;
         // new

    public $nom_categorie;

    public function __construct(
        string $titre,
        float $prix,
        string $src_img,
        ?int $id_categorie = null,
        ?int $id_jeu = null,
        ?string $description = null,
        ?string $video_src = null,
       
    ) {
        $this->id_jeu = $id_jeu;
        $this->titre = $titre;
        $this->prix = $prix;
        $this->src_img = $src_img;
        $this->id_categorie = $id_categorie;
        $this->description = $description;
        $this->video_src = $video_src;
       
    }

    // --- getters ---
    public function getIdJeu(): ?int { return $this->id_jeu; }
    public function getTitre(): string { return $this->titre; }
    public function getPrix(): float { return $this->prix; }
    public function getSrcImg(): string { return $this->src_img; }
    public function getIdCategorie(): ?int { return $this->id_categorie; }
    public function getDescription(): ?string { return $this->description; }
    public function getVideoSrc(): ?string { return $this->video_src; }
   

    // --- setters ---
    public function setTitre(string $titre): void { $this->titre = $titre; }
    public function setPrix(float $prix): void { $this->prix = $prix; }
    public function setSrcImg(string $src_img): void { $this->src_img = $src_img; }
    public function setIdCategorie(?int $id_categorie): void { $this->id_categorie = $id_categorie; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function setVideoSrc(?string $video_src): void { $this->video_src = $video_src; }
   
}