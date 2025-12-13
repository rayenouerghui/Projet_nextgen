<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/historique.php';
require_once __DIR__ . '/JeuOwnedController.php';  

class HistoriqueController
{
    private $pdo;
    private $jeuOwnedController;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
        $this->jeuOwnedController = new JeuOwnedController();
    }

    public function addHistorique(Historique $h): bool
    {
        try {
            $sql = "INSERT INTO historique (id_user, type_action, description, date_action) 
                    VALUES (:id_user, :type_action, :description, :date_action)";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id_user'     => $h->getIdUser(),
                ':type_action' => $h->getTypeAction(),
                ':description' => $h->getDescription(),
                ':date_action' => $h->getDateAction()
            ]);
        } catch (PDOException $e) {
        error_log("Add historique error: " . $e->getMessage());
            return false;
        }
    }

    // FINAL VERSION — USES YOUR REAL JeuOwned DATA
   public function getUserFullHistory(int $userId): array
    {
        $history = [];

        // 1. On récupère TOUS les achats (les plus récents en haut)
        $stmt = $this->pdo->prepare("
            SELECT jo.date_achat, j.titre 
            FROM jeux_owned jo
            JOIN jeu j ON jo.id_jeu = j.id_jeu 
            WHERE jo.id = ?
            ORDER BY jo.date_achat DESC
        ");
        $stmt->execute([$userId]);
        $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($games as $game) {
            $h = new Historique(
                $userId,
                'purchase',
                'Achat : ' . $game['titre'],
                null,
                $game['date_achat']
            );
            $history[] = $h;
        }

        // 2. On ajoute FORCÉMENT la dernière connexion EN TOUT DERNIER
        $stmt = $this->pdo->prepare("SELECT last_login FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if ($row && $row['last_login']) {
            $h = new Historique($userId, 'login', 'Dernière connexion', null, $row['last_login']);
            $history[] = $h; // On pousse à la fin → toujours en bas
        }

        return $history;
    }
}