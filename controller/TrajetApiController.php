<?php

require_once __DIR__ . '/../config/paths.php';
require_once CONFIG_PATH . '/config.php';
require_once SERVICES_PATH . '/TrackingService.php';

class TrajetApiController {
    private PDO $db;
    private TrackingService $tracking;

    public function __construct() {
        $this->db = config::getConnexion();
        $this->tracking = new TrackingService();
    }

    /**
     * Récupère les données de suivi pour une livraison
     * @param int $idLivraison ID de la livraison
     * @return array Données de suivi ou erreur
     */
    public function getTrackingData(int $idLivraison): array {
        // Validation de l'ID
        if ($idLivraison <= 0) {
            http_response_code(400);
            error_log("TrajetApiController: ID invalide reçu: $idLivraison");
            return ['error' => 'ID de livraison invalide'];
        }
        
        $livraison = $this->findLivraison($idLivraison);
        
        if (!$livraison) {
            http_response_code(404);
            error_log("TrajetApiController: Livraison introuvable: $idLivraison");
            return ['error' => 'Livraison introuvable'];
        }

        $destOk = isset($livraison['position_lat'], $livraison['position_lng'])
            && $livraison['position_lat'] !== null && $livraison['position_lng'] !== null
            && $livraison['position_lat'] >= -90 && $livraison['position_lat'] <= 90
            && $livraison['position_lng'] >= -180 && $livraison['position_lng'] <= 180;
        
        if (!$destOk) {
            // Generate random destination near Tunis for demo
            $livraison['position_lat'] = 36.8 + (rand(-50, 50) / 1000);
            $livraison['position_lng'] = 10.18 + (rand(-50, 50) / 1000);
            
            // Update in DB
            $stmt = $this->db->prepare("UPDATE livraisons SET position_lat = ?, position_lng = ? WHERE id_livraison = ?");
            $stmt->execute([$livraison['position_lat'], $livraison['position_lng'], $idLivraison]);
        }

        $trajet = $this->findOrCreateTrajet($idLivraison);
        
        $liveData = $this->tracking->simulateProgress($livraison, $trajet);

        if ($liveData && $trajet) {
            $this->updateTrajet($trajet['id_trajet'], $liveData);
            
            if (isset($liveData['has_arrived']) && $liveData['has_arrived'] === true) {
                $statut = strtolower($livraison['statut'] ?? '');
                if ($statut !== 'livrée' && $statut !== 'livree') {
                    $this->markAsDelivered($livraison['id_livraison']);
                    $livraison['statut'] = 'livree';
                    error_log("✅ Delivery #{$livraison['id_livraison']} automatically marked as 'livree' (arrived at destination)");
                }
            }
            
            $trajet = $this->findTrajet($idLivraison);
        }

        $origin = $liveData ? $liveData['origin'] : $this->tracking->getOrigin();
        $destination = [
            'lat' => (float)$livraison['position_lat'],
            'lng' => (float)$livraison['position_lng'],
        ];
        
        // Get route first
        $route = [];
        if ($liveData && isset($liveData['route'])) {
            $route = $liveData['route'];
        } elseif ($trajet && !empty($trajet['route_json'])) {
            $route = json_decode($trajet['route_json'], true) ?: [];
        }
        
        // Build current position from route[current_index] or fallback
        $currentIndex = (int)($trajet['current_index'] ?? 0);
        $current = ['lat' => 0.0, 'lng' => 0.0];
        
        if (!empty($route) && isset($route[$currentIndex])) {
            $point = $route[$currentIndex];
            
            // Handle both formats: array [lng, lat] or object {lat, lng}
            if (is_array($point) && isset($point[0], $point[1])) {
                // GeoJSON format: [longitude, latitude]
                $current['lng'] = (float)$point[0];
                $current['lat'] = (float)$point[1];
            } elseif (isset($point['lat'], $point['lng'])) {
                // Object format: {lat, lng}
                $current['lat'] = (float)$point['lat'];
                $current['lng'] = (float)$point['lng'];
            }
        } elseif ($liveData && isset($liveData['latitude'], $liveData['longitude'])) {
            $current['lat'] = (float)$liveData['latitude'];
            $current['lng'] = (float)$liveData['longitude'];
        } elseif ($trajet && isset($trajet['position_lat'], $trajet['position_lng']) 
                  && $trajet['position_lat'] != 0 && $trajet['position_lng'] != 0) {
            $current['lat'] = (float)$trajet['position_lat'];
            $current['lng'] = (float)$trajet['position_lng'];
        } else {
            // Last fallback to origin
            $current = $origin;
        }
        
        $progress = $this->tracking->progressData($origin, $destination, $current);
        
        // Only call reverseGeocode if we have valid coordinates
        $loc = null;
        if ($current['lat'] !== 0.0 && $current['lng'] !== 0.0) {
            $loc = $this->tracking->reverseGeocode($current['lat'], $current['lng']);
        }

        return [
            'livraison' => [
                'id' => (int)$livraison['id_livraison'],
                'statut' => $livraison['statut'],
            ],
            'trajet' => [
                'id' => (int)($trajet['id_trajet'] ?? 0),
                'statut_realtime' => $liveData ? $liveData['statut'] : ($trajet['statut_realtime'] ?? 'en_route'),
                'position_lat' => $current['lat'],
                'position_lng' => $current['lng'],
                'current_index' => $currentIndex,
            ],
            'origin' => $origin,
            'destination' => $destination,
            'route' => $route,
            'progress' => $progress,
            'location' => $loc,
        ];
    }

    private function findLivraison(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM livraisons WHERE id_livraison = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function findTrajet(int $idLivraison) {
        $stmt = $this->db->prepare("SELECT * FROM trajets WHERE id_livraison = :id");
        $stmt->execute([':id' => $idLivraison]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function findOrCreateTrajet(int $idLivraison) {
        $trajet = $this->findTrajet($idLivraison);
        
        if (!$trajet) {
            $origin = $this->tracking->getOrigin();
            
            // Check if extended columns exist by trying a simple insert first
            try {
                $sql = "INSERT INTO trajets (id_livraison, position_lat, position_lng, date_update) 
                        VALUES (?, ?, ?, NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $idLivraison,
                    $origin['lat'],
                    $origin['lng']
                ]);
            } catch (PDOException $e) {
                error_log("Error creating trajet: " . $e->getMessage());
                return null;
            }
            
            $trajet = $this->findTrajet($idLivraison);
        }
        
        return $trajet;
    }

    private function updateTrajet(int $id, array $liveData): void {
        // Try to update with extended columns, fall back to basic columns if they don't exist
        try {
            $updateSql = "UPDATE trajets 
                          SET position_lat = :lat, 
                              position_lng = :lng,
                              date_update = NOW()
                          WHERE id_trajet = :id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->execute([
                ':lat' => $liveData['latitude'],
                ':lng' => $liveData['longitude'],
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error updating trajet: " . $e->getMessage());
        }
    }

    private function markAsDelivered(int $idLivraison): void {
        $completeSql = "UPDATE livraisons SET statut = 'livree' WHERE id_livraison = :id";
        $completeStmt = $this->db->prepare($completeSql);
        $completeStmt->execute([':id' => $idLivraison]);
    }
}
