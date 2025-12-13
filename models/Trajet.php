<?php

/**
 * Modèle Trajet - Suivi GPS des livraisons
 */
class Trajet
{
    private ?int $id_trajet = null;
    private int $id_livraison;
    private float $position_lat;
    private float $position_lng;
    private string $date_update;
    
    /**
     * Calcule la distance entre deux points (formule Haversine)
     * @param float $lat1 Latitude point 1
     * @param float $lng1 Longitude point 1
     * @param float $lat2 Latitude point 2
     * @param float $lng2 Longitude point 2
     * @return float Distance en kilomètres
     */
    public static function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371.0; // Rayon de la Terre en km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R * $c;
    }

    public function __construct(int $id_livraison, float $position_lat, float $position_lng, ?int $id_trajet = null, ?string $date_update = null)
    {
        $this->id_trajet = $id_trajet;
        $this->id_livraison = $id_livraison;
        $this->position_lat = $position_lat;
        $this->position_lng = $position_lng;
        $this->date_update = $date_update ?? date('Y-m-d H:i:s');
    }

   
    public function getIdTrajet(): ?int { return $this->id_trajet; }
    public function getIdLivraison(): int { return $this->id_livraison; }
    public function getPositionLat(): float { return $this->position_lat; }
    public function getPositionLng(): float { return $this->position_lng; }
    public function setPosition(float $lat, float $lng): void {
        $this->position_lat = $lat;
        $this->position_lng = $lng;
    }
}