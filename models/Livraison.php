<?php

/**
 * Modèle Livraison - Gestion des livraisons
 * Statuts disponibles: commandee, preparée, en_route, livree, annulée
 */
class Livraison
{
    // Constantes pour les statuts
    public const STATUT_COMMANDEE = 'commandee';
    public const STATUT_PREPAREE = 'preparée';
    public const STATUT_EN_ROUTE = 'en_route';
    public const STATUT_LIVREE = 'livree';
    public const STATUT_ANNULEE = 'annulée';
    
    // Constantes pour les modes de livraison
    public const MODE_STANDARD = 'standard';
    public const MODE_EXPRESS = 'express';
    public const MODE_SUPER_FAST = 'super_fast';
    
    private ?int $id_livraison = null;
    private int $id_user;
    private int $id_jeu;
    private string $adresse_complete;
    private float $position_lat;
    private float $position_lng;
    private string $mode_paiement;
    private float $prix_livraison = 8.000;
    private string $statut = self::STATUT_COMMANDEE;
    private string $date_commande;

   
    public $nom_jeu;
    public $src_img;
    public $prenom_user;
    public $nom_user;

    public function __construct(
        int $id_user,
        int $id_jeu,
        string $adresse_complete,
        float $position_lat,
        float $position_lng,
        string $mode_paiement,
        float $prix_livraison = 8.000,
        string $statut = 'commandee',
        ?int $id_livraison = null,
        ?string $date_commande = null
    ) {
        $this->id_livraison = $id_livraison;
        $this->id_user = $id_user;
        $this->id_jeu = $id_jeu;
        $this->adresse_complete = $adresse_complete;
        $this->position_lat = $position_lat;
        $this->position_lng = $position_lng;
        $this->mode_paiement = $mode_paiement;
        $this->prix_livraison = $prix_livraison;
        $this->statut = $statut;
        $this->date_commande = $date_commande ?? date('Y-m-d H:i:s');
    }

    
    public function getIdLivraison(): ?int { return $this->id_livraison; }
    public function getIdUser(): int { return $this->id_user; }
    public function getIdJeu(): int { return $this->id_jeu; }
    public function getAdresseComplete(): string { return $this->adresse_complete; }
    public function getPositionLat(): float { return $this->position_lat; }
    public function getPositionLng(): float { return $this->position_lng; }
    public function getModePaiement(): string { return $this->mode_paiement; }
    public function getPrixLivraison(): float { return $this->prix_livraison; }
    public function getStatut(): string { return $this->statut; }
    public function getDateCommande(): string { return $this->date_commande; }

   
    public function setStatut(string $statut): void
    {
        $allowed = [
            self::STATUT_COMMANDEE,
            self::STATUT_PREPAREE,
            self::STATUT_EN_ROUTE,
            self::STATUT_LIVREE,
            self::STATUT_ANNULEE
        ];
        if (in_array($statut, $allowed, true)) {
            $this->statut = $statut;
        } else {
            error_log("Tentative de définir un statut invalide: $statut");
        }
    }
    
    /**
     * Vérifie si la livraison est terminée
     */
    public function isDelivered(): bool
    {
        return $this->statut === self::STATUT_LIVREE;
    }
    
    /**
     * Vérifie si la livraison est annulée
     */
    public function isCancelled(): bool
    {
        return $this->statut === self::STATUT_ANNULEE;
    }
    
    /**
     * Vérifie si la livraison est en cours
     */
    public function isInTransit(): bool
    {
        return in_array($this->statut, [
            self::STATUT_PREPAREE,
            self::STATUT_EN_ROUTE
        ], true);
    }
    
    /**
     * Retourne tous les statuts valides
     */
    public static function getValidStatuts(): array
    {
        return [
            self::STATUT_COMMANDEE,
            self::STATUT_PREPAREE,
            self::STATUT_EN_ROUTE,
            self::STATUT_LIVREE,
            self::STATUT_ANNULEE
        ];
    }
}