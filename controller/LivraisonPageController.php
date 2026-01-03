<?php
/**
 * LivraisonPageController - Contrôleur avancé adapté pour le projet ami
 * Fournit les variables nécessaires pour livraison_view.php avec MapLibre, Voice Control, etc.
 */

require_once __DIR__ . '/../config/paths.php';
require_once CONFIG_PATH . '/config.php';
require_once SERVICES_PATH . '/TrackingService.php';
require_once MODELS_PATH . '/Livraison.php';
require_once MODELS_PATH . '/Trajet.php';

class LivraisonPageController {
    private PDO $db;
    private TrackingService $trackingService;
    private $deliveryModes = [
        'standard' => ['label' => 'Standard (3-5j)', 'price' => 0],
        'express' => ['label' => 'Express (24h)', 'price' => 9.99],
        'super_fast' => ['label' => 'Super Fast (4h)', 'price' => 19.99],
    ];

    public function __construct() {
        $this->db = config::getConnexion();
        $this->trackingService = new TrackingService();
    }

    public function afficherPage() {
        session_start();
        
        if (!isset($_SESSION['user']['id'])) {
            header('Location: frontoffice/connexion.php');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $message = '';
        $messageType = '';

        // Traiter les actions POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            [$message, $messageType] = $this->gererAction($userId, $_POST);
        }

        // Récupérer les données
        $commandes = $this->fetchCommandes($userId);
        $livraisons = $this->fetchLivraisons($userId);
        $jeux = $this->fetchAllJeux();
        $profil = $this->findUserProfile($userId);
        $origin = $this->trackingService->getOrigin();

        // Enrichir les livraisons avec les trajets
        foreach ($livraisons as &$livraison) {
            if (in_array($livraison['statut'], ['en_transit', 'livree']) && $livraison['position_lat']) {
                $trajet = $this->synchroniserTrajet($livraison);
                if ($trajet) $livraison['trajet'] = $trajet;
            }
        }

        $idsCommandesLivrees = array_column($livraisons, 'id_jeu');

        // Variables pour la vue
        $data = [
            'idUtilisateur' => $userId,
            'commandes' => $commandes,
            'jeux' => $jeux,
            'livraisons' => $livraisons,
            'idsCommandesLivrees' => $idsCommandesLivrees,
            'message' => $message,
            'messageType' => $messageType,
            'deliveryModes' => $this->deliveryModes,
            'profil' => $profil,
            'origin' => $origin,
        ];

        extract($data);
        require_once __DIR__ . '/../view/livraison_gaming.php';
    }

    private function gererAction(int $userId, array $post): array {
        switch ($post['action']) {
            case 'creer_livraison':
                return $this->creerLivraison($userId, $post);
            case 'creer_commande':
                return $this->creerCommande($userId, $post);
            case 'supprimer_livraison':
                return $this->supprimerLivraison($post);
            default:
                return ['Action inconnue', 'error'];
        }
    }

    private function creerLivraison(int $userId, array $post): array {
        $idJeu = (int)($post['id_commande'] ?? $post['id_jeu'] ?? 0);
        $adresse = trim($post['adresse_complete'] ?? '');
        $notes = trim($post['notes_client'] ?? '');
        $mode = $post['mode_livraison'] ?? 'standard';
        $lat = isset($post['position_lat']) ? (float)$post['position_lat'] : null;
        $lng = isset($post['position_lng']) ? (float)$post['position_lng'] : null;

        if (!$idJeu || !$lat || !$lng) {
            return ['Sélectionne un jeu et une destination sur la carte', 'error'];
        }

        $prix = $this->deliveryModes[$mode]['price'] ?? 0;
        $dateLiv = (new DateTime())->modify($mode === 'express' ? '+1 day' : ($mode === 'super_fast' ? '+4 hours' : '+4 days'))->format('Y-m-d');

        $stmt = $this->db->prepare("
            INSERT INTO livraisons (id_user, id_jeu, adresse_complete, position_lat, position_lng, 
                                    mode_paiement, prix_livraison, statut, date_commande)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'commandee', NOW())
        ");
        $ok = $stmt->execute([$userId, $idJeu, $adresse ?: "Lat: $lat, Lng: $lng", $lat, $lng, $mode, $prix]);

        return $ok ? ['Livraison créée avec succès! 🚚', 'success'] : ['Erreur création', 'error'];
    }

    private function creerCommande(int $userId, array $post): array {
        $idJeu = (int)($post['id_jeu'] ?? 1);
        
        $stmt = $this->db->prepare("INSERT INTO jeux_owned (id, id_jeu, date_achat) VALUES (?, ?, NOW())");
        $ok = $stmt->execute([$userId, $idJeu]);

        return $ok ? ['Achat simulé! Le jeu apparaît maintenant dans vos commandes.', 'success'] : ['Erreur', 'error'];
    }

    private function supprimerLivraison(array $post): array {
        $id = (int)($post['id_livraison'] ?? 0);
        if ($id <= 0) return ['ID invalide', 'error'];

        $this->db->prepare("DELETE FROM trajets WHERE id_livraison = ?")->execute([$id]);
        $ok = $this->db->prepare("DELETE FROM livraisons WHERE id_livraison = ?")->execute([$id]);

        return $ok ? ['Livraison annulée', 'success'] : ['Erreur', 'error'];
    }

    private function fetchCommandes(int $userId): array {
        // Jeux achetés sans livraison = commandes en attente
        $stmt = $this->db->prepare("
            SELECT 
                jo.owned_id AS id_commande,
                jo.id AS id_utilisateur,
                jo.id_jeu,
                CONCAT('CMD-', jo.owned_id) AS numero_commande,
                j.prix AS total,
                'payée' AS statut,
                jo.date_achat AS date_commande,
                j.titre AS nom_jeu,
                j.src_img AS image_jeu,
                j.prix
            FROM jeux_owned jo
            JOIN jeu j ON jo.id_jeu = j.id_jeu
            LEFT JOIN livraisons l ON l.id_user = jo.id AND l.id_jeu = jo.id_jeu
            WHERE jo.id = ? AND l.id_livraison IS NULL
            ORDER BY jo.date_achat DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function fetchLivraisons(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT l.*, 
                   CONCAT('CMD-', l.id_livraison) AS numero_commande,
                   j.titre AS nom_jeu, 
                   j.src_img AS image_jeu,
                   l.date_commande AS date_livraison
            FROM livraisons l
            JOIN jeu j ON l.id_jeu = j.id_jeu
            WHERE l.id_user = ?
            ORDER BY l.date_commande DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function fetchAllJeux(): array {
        return $this->db->query("SELECT id_jeu, titre, prix FROM jeu ORDER BY titre")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function findUserProfile(int $userId): ?array {
        $stmt = $this->db->prepare("SELECT id, nom, prenom, email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function synchroniserTrajet(array $livraison): ?array {
        $id = (int)$livraison['id_livraison'];
        
        // Chercher trajet existant
        $stmt = $this->db->prepare("SELECT * FROM trajets WHERE id_livraison = ? LIMIT 1");
        $stmt->execute([$id]);
        $trajet = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$trajet) {
            // Créer nouveau trajet with basic columns only
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO trajets (id_livraison, position_lat, position_lng, date_update)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$id, 36.8, 10.18]); // Position départ Tunis
                
                $stmt = $this->db->prepare("SELECT * FROM trajets WHERE id_livraison = ? LIMIT 1");
                $stmt->execute([$id]);
                $trajet = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Error creating trajet: " . $e->getMessage());
                return null;
            }
        }

        // Simuler progression
        $liveData = $this->trackingService->simulateProgress($livraison, $trajet);
        if ($liveData && $trajet) {
            try {
                $stmt = $this->db->prepare("
                    UPDATE trajets SET position_lat = ?, position_lng = ?, date_update = NOW()
                    WHERE id_trajet = ?
                ");
                $stmt->execute([$liveData['latitude'], $liveData['longitude'], $trajet['id_trajet']]);
                $trajet['position_lat'] = $liveData['latitude'];
                $trajet['position_lng'] = $liveData['longitude'];
            } catch (PDOException $e) {
                error_log("Error updating trajet: " . $e->getMessage());
            }
        }

        return $trajet;
    }
}
