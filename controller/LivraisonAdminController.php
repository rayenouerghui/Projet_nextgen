<?php
/**
 * LivraisonAdminController - Admin Dashboard for Delivery Management
 * Adapted from original BACKUP_livraison_20251213 for friend's project
 */

require_once __DIR__ . '/../config/paths.php';
require_once CONFIG_PATH . '/config.php';

class LivraisonAdminController {
    private $pdo;
    // Match database enum values: 'commandee','emballee','en_transit','livree' + extended: 'preparée','en_route','annulée'
    private $statuts = ['commandee', 'preparée', 'emballee', 'en_transit', 'en_route', 'livree', 'annulée'];
    
    // Origin point (warehouse/store location)
    private $origin = [
        'lat' => 36.8065, // Tunis center
        'lng' => 10.1815
    ];

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    /**
     * Main method to display the admin page
     */
    public function afficherPage(): void {
        $message = null;
        $messageType = null;

        // Handle POST actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            [$message, $messageType] = $this->dispatchAction($_POST);
        }

        // Get all data for the view
        $stats = $this->countByStatut();
        $livraisons = $this->findAllWithRelations();
        $commandes = $this->findCommandesWithoutDelivery();
        $totalLivraisons = array_sum($stats);

        // Add trajet data to each livraison
        foreach ($livraisons as &$livraison) {
            $livraison['trajet'] = $this->findTrajetByLivraison((int)$livraison['id_livraison']);
        }

        $data = [
            'message' => $message,
            'messageType' => $messageType,
            'statuts' => $this->statuts,
            'livraisons' => $livraisons,
            'stats' => $stats,
            'commandes' => $commandes,
            'totalLivraisons' => $totalLivraisons,
            'origin' => $this->origin,
        ];

        extract($data);
        require __DIR__ . '/../view/backoffice/admin_livraisons_view.php';
    }

    /**
     * Dispatch POST actions to appropriate handlers
     */
    private function dispatchAction(array $payload): array {
        switch ($payload['action']) {
            case 'create_livraison':
                return $this->createLivraison($payload);
            case 'update_statut':
                return $this->updateStatut($payload);
            case 'delete_livraison':
                return $this->deleteLivraison($payload);
            case 'confirm_livraison':
                return $this->confirmLivraison($payload);
            case 'refresh_trajet':
                return $this->refreshTrajet($payload);
            default:
                return ['Action inconnue', 'error'];
        }
    }

    /**
     * Get all livraisons with user and game relations
     */
    private function findAllWithRelations(): array {
        $sql = "
            SELECT l.*, 
                   u.prenom as prenom_utilisateur, 
                   u.nom as nom_utilisateur,
                   u.email as email_utilisateur,
                   j.titre as nom_jeu,
                   CONCAT('CMD-', l.id_livraison) as numero_commande
            FROM livraisons l
            LEFT JOIN users u ON l.id_user = u.id
            LEFT JOIN jeu j ON l.id_jeu = j.id_jeu
            ORDER BY l.date_commande DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Count livraisons by status for stats cards
     */
    private function countByStatut(): array {
        $sql = "SELECT statut, COUNT(*) as count FROM livraisons GROUP BY statut";
        $stmt = $this->pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $stats = [];
        foreach ($this->statuts as $statut) {
            $stats[$statut] = $results[$statut] ?? 0;
        }
        return $stats;
    }

    /**
     * Find purchases without delivery (for dropdown)
     */
    private function findCommandesWithoutDelivery(): array {
        $sql = "
            SELECT jo.owned_id as id_commande, 
                   CONCAT('CMD-', jo.owned_id) as numero_commande,
                   CONCAT(u.prenom, ' ', u.nom) as nom_utilisateur,
                   j.titre as nom_jeu,
                   jo.id as user_id,
                   jo.id_jeu
            FROM jeux_owned jo
            JOIN users u ON jo.id = u.id
            JOIN jeu j ON jo.id_jeu = j.id_jeu
            LEFT JOIN livraisons l ON l.id_user = jo.id AND l.id_jeu = jo.id_jeu
            WHERE l.id_livraison IS NULL
            ORDER BY jo.date_achat DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Find livraison by ID
     */
    private function findById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM livraisons WHERE id_livraison = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find trajet by livraison ID
     */
    private function findTrajetByLivraison(int $idLivraison) {
        $stmt = $this->pdo->prepare("SELECT * FROM trajets WHERE id_livraison = ?");
        $stmt->execute([$idLivraison]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new livraison
     */
    private function createLivraison(array $payload): array {
        $idCommande = (int)($payload['id_commande'] ?? 0);
        $adresse = trim($payload['adresse_complete'] ?? '');
        $ville = trim($payload['ville'] ?? '');
        $codePostal = trim($payload['code_postal'] ?? '');
        $positionLat = isset($payload['position_lat']) && $payload['position_lat'] !== '' ? (float)$payload['position_lat'] : null;
        $positionLng = isset($payload['position_lng']) && $payload['position_lng'] !== '' ? (float)$payload['position_lng'] : null;

        if ($idCommande <= 0 || empty($adresse) || empty($ville)) {
            return ['Champs obligatoires manquants', 'error'];
        }

        // Get command info
        $cmdStmt = $this->pdo->prepare("SELECT id, id_jeu FROM jeux_owned WHERE owned_id = ?");
        $cmdStmt->execute([$idCommande]);
        $cmd = $cmdStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cmd) {
            return ['Commande introuvable', 'error'];
        }

        $fullAddress = $adresse . ', ' . $ville . ' ' . $codePostal;

        $sql = "INSERT INTO livraisons (id_user, id_jeu, adresse_complete, position_lat, position_lng, statut, date_commande) 
                VALUES (?, ?, ?, ?, ?, 'preparée', NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cmd['id'], $cmd['id_jeu'], $fullAddress, $positionLat, $positionLng]);

        return ['Livraison créée avec succès!', 'success'];
    }

    /**
     * Update livraison status
     */
    private function updateStatut(array $payload): array {
        $id = (int)($payload['id_livraison'] ?? 0);
        $statut = $payload['statut'] ?? '';

        if ($id <= 0 || !in_array($statut, $this->statuts)) {
            return ['Données invalides', 'error'];
        }

        $stmt = $this->pdo->prepare("UPDATE livraisons SET statut = ? WHERE id_livraison = ?");
        $stmt->execute([$statut, $id]);

        return ['Statut mis à jour!', 'success'];
    }

    /**
     * Delete livraison and associated trajet
     */
    private function deleteLivraison(array $payload): array {
        $id = (int)($payload['id_livraison'] ?? 0);

        if ($id <= 0) {
            return ['ID invalide', 'error'];
        }

        try {
            $this->pdo->beginTransaction();

            // Delete trajet first
            $stmt = $this->pdo->prepare("DELETE FROM trajets WHERE id_livraison = ?");
            $stmt->execute([$id]);

            // Delete livraison
            $stmt = $this->pdo->prepare("DELETE FROM livraisons WHERE id_livraison = ?");
            $stmt->execute([$id]);

            $this->pdo->commit();
            return ['Livraison supprimée!', 'success'];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['Erreur lors de la suppression', 'error'];
        }
    }

    /**
     * CONFIRM LIVRAISON - Changes status to en_route and creates trajet for tracking
     */
    private function confirmLivraison(array $payload): array {
        $id = (int)($payload['id_livraison'] ?? 0);

        if ($id <= 0) {
            return ['ID invalide', 'error'];
        }

        $livraison = $this->findById($id);
        if (!$livraison) {
            return ['Livraison introuvable', 'error'];
        }

        // Check if coordinates exist
        if (empty($livraison['position_lat']) || empty($livraison['position_lng'])) {
            // Generate random coordinates near Tunis for demo
            $livraison['position_lat'] = 36.8 + (rand(-50, 50) / 1000);
            $livraison['position_lng'] = 10.18 + (rand(-50, 50) / 1000);
            
            // Update livraison with coordinates
            $stmt = $this->pdo->prepare("UPDATE livraisons SET position_lat = ?, position_lng = ? WHERE id_livraison = ?");
            $stmt->execute([$livraison['position_lat'], $livraison['position_lng'], $id]);
        }

        // Update status to en_route
        $stmt = $this->pdo->prepare("UPDATE livraisons SET statut = 'en_route' WHERE id_livraison = ?");
        $stmt->execute([$id]);

        // Create or update trajet
        $trajet = $this->findTrajetByLivraison($id);
        if (!$trajet) {
            // Get OSRM route
            $route = $this->getOSRMRoute(
                $this->origin['lat'], $this->origin['lng'],
                $livraison['position_lat'], $livraison['position_lng']
            );

            // Insert trajet with only the columns that exist in the database
            $sql = "INSERT INTO trajets (id_livraison, position_lat, position_lng, date_update) 
                    VALUES (?, ?, ?, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $id,
                $this->origin['lat'],
                $this->origin['lng']
            ]);
        } else {
            // Update existing trajet - only update columns that exist
            $stmt = $this->pdo->prepare("UPDATE trajets SET position_lat = ?, position_lng = ?, date_update = NOW() WHERE id_trajet = ?");
            $stmt->execute([
                $this->origin['lat'],
                $this->origin['lng'],
                $trajet['id_trajet']
            ]);
        }

        return ['🚚 Livraison confirmée et suivi GPS activé!', 'success'];
    }

    /**
     * Refresh trajet position (simulate movement)
     */
    private function refreshTrajet(array $payload): array {
        $id = (int)($payload['id_livraison'] ?? 0);

        if ($id <= 0) {
            return ['ID invalide', 'error'];
        }

        $trajet = $this->findTrajetByLivraison($id);
        if (!$trajet) {
            return ['Trajet non trouvé', 'error'];
        }

        // Move to next point on route
        $currentIndex = (int)($trajet['current_index'] ?? 0);
        $totalPoints = (int)($trajet['total_points'] ?? 0);
        
        if ($totalPoints > 0 && $currentIndex < $totalPoints - 1) {
            $newIndex = min($currentIndex + 5, $totalPoints - 1); // Move 5 points forward
            
            $routePoints = json_decode($trajet['route_json'], true) ?: [];
            if (isset($routePoints[$newIndex])) {
                $newLat = $routePoints[$newIndex][1];
                $newLng = $routePoints[$newIndex][0];

                $stmt = $this->pdo->prepare("UPDATE trajets SET current_index = ?, position_lat = ?, position_lng = ?, derniere_mise_a_jour = NOW() WHERE id_trajet = ?");
                $stmt->execute([$newIndex, $newLat, $newLng, $trajet['id_trajet']]);

                // Check if arrived
                if ($newIndex >= $totalPoints - 1) {
                    $this->pdo->prepare("UPDATE livraisons SET statut = 'livree' WHERE id_livraison = ?")->execute([$id]);
                    $this->pdo->prepare("UPDATE trajets SET statut_realtime = 'livree' WHERE id_trajet = ?")->execute([$trajet['id_trajet']]);
                    return ['🎉 Livraison terminée!', 'success'];
                }
            }
        }

        return ['📍 Position mise à jour!', 'success'];
    }

    /**
     * Get route from OSRM API
     */
    private function getOSRMRoute(float $startLat, float $startLng, float $endLat, float $endLng): ?array {
        $url = "https://router.project-osrm.org/route/v1/driving/{$startLng},{$startLat};{$endLng},{$endLat}?overview=full&geometries=geojson";
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'NextGen Delivery Tracker'
            ]
        ]);

        try {
            $response = @file_get_contents($url, false, $context);
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['routes'][0]['geometry']['coordinates'])) {
                    return [
                        'coordinates' => $data['routes'][0]['geometry']['coordinates'],
                        'distance' => $data['routes'][0]['distance'] ?? 0,
                        'duration' => $data['routes'][0]['duration'] ?? 0
                    ];
                }
            }
        } catch (Exception $e) {
            // Fallback: generate simple straight line
        }

        // Fallback: generate direct line
        return [
            'coordinates' => [
                [$startLng, $startLat],
                [$endLng, $endLat]
            ],
            'distance' => 0,
            'duration' => 0
        ];
    }

    /**
     * Get origin coordinates
     */
    public function getOrigin(): array {
        return $this->origin;
    }
}
