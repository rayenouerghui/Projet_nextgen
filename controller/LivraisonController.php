<?php

require_once __DIR__ . '/../config/paths.php';
require_once CONFIG_PATH . '/config.php';
require_once MODELS_PATH . '/Livraison.php';

class LivraisonController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    // Crée une livraison
    public function createLivraison(Livraison $livraison): bool
    {
        try {
            $sql = "INSERT INTO livraisons 
                    (id_user, id_jeu, adresse_complete, position_lat, position_lng, mode_paiement, prix_livraison, statut) 
                    VALUES 
                    (:id_user, :id_jeu, :adresse, :lat, :lng, :mode, :prix, :statut)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_user' => $livraison->getIdUser(),
                ':id_jeu'  => $livraison->getIdJeu(),
                ':adresse' => $livraison->getAdresseComplete(),
                ':lat'     => $livraison->getPositionLat(),
                ':lng'     => $livraison->getPositionLng(),
                ':mode'    => $livraison->getModePaiement(),
                ':prix'    => $livraison->getPrixLivraison(),
                ':statut'  => $livraison->getStatut()
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Erreur création livraison : " . $e->getMessage());
            return false;
        }
    }

    // Récupère une livraison par ID (avec le nom du jeu)
    public function getLivraisonById(int $id): ?Livraison
    {
        $sql = "SELECT l.*, j.titre AS nom_jeu, j.src_img 
                FROM livraisons l 
                JOIN jeu j ON l.id_jeu = j.id_jeu 
                WHERE l.id_livraison = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        $livraison = new Livraison(
            $data['id_user'],
            $data['id_jeu'],
            $data['adresse_complete'],
            $data['position_lat'],
            $data['position_lng'],
            $data['mode_paiement'],
            $data['prix_livraison'],
            $data['statut'],
            $data['id_livraison'],
            $data['date_commande']
        );

        
        $livraison->nom_jeu = $data['nom_jeu'] ?? 'Jeu inconnu';
        $livraison->src_img = $data['src_img'] ?? 'default.jpg';

        return $livraison;
    }

    
    public function getLivraisonEnCours(int $userId): ?Livraison
    {
        $sql = "SELECT l.*, j.titre AS nom_jeu, j.src_img 
                FROM livraisons l 
                JOIN jeu j ON l.id_jeu = j.id_jeu 
                WHERE l.id_user = :id AND l.statut != 'livree' 
                ORDER BY l.id_livraison DESC LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        $livraison = new Livraison(
            $data['id_user'],
            $data['id_jeu'],
            $data['adresse_complete'],
            $data['position_lat'],
            $data['position_lng'],
            $data['mode_paiement'],
            $data['prix_livraison'],
            $data['statut'],
            $data['id_livraison'],
            $data['date_commande']
        );
        $livraison->nom_jeu = $data['nom_jeu'];
        $livraison->src_img = $data['src_img'];

        return $livraison;
    }

    
    public function getAllLivraisons(): array
    {
        $sql = "SELECT l.*, u.prenom, u.nom, j.titre AS jeu_titre,
                       CONCAT(u.prenom, ' ', u.nom) AS user_name
                FROM livraisons l 
                LEFT JOIN users u ON l.id_user = u.id 
                LEFT JOIN jeu j ON l.id_jeu = j.id_jeu 
                ORDER BY l.date_commande DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    
    public function updateStatut(int $id, string $statut): bool
    {
        $sql = "UPDATE livraisons SET statut = :statut WHERE id_livraison = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':statut' => $statut, ':id' => $id]);
    }

    
    public function deleteLivraison(int $id): bool
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare("DELETE FROM trajets WHERE id_livraison = ?")->execute([$id]);
            $this->pdo->prepare("DELETE FROM livraisons WHERE id_livraison = ?")->execute([$id]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getPdo() { return $this->pdo; }
        public function updatePositionLivreur(int $id_livraison, float $lat, float $lng): bool
    {
        try {
            $sql = "INSERT INTO trajets (id_livraison, position_lat, position_lng) 
                    VALUES (:id, :lat, :lng)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id_livraison,
                ':lat' => $lat,
                ':lng' => $lng
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Erreur update position livreur : " . $e->getMessage());
            return false;
        }
    }
}
