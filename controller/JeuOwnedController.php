<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/JeuOwned.php';

class JeuOwnedController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    public function isOwned(int $userId, int $jeuId): bool
    {
        $sql = "SELECT 1 FROM jeux_owned WHERE id = :user_id AND id_jeu = :jeu_id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':jeu_id' => $jeuId]);
        return $stmt->fetch() !== false;
    }

    public function getScore(int $userId, int $jeuId): int
    {
        $sql = "SELECT score FROM jeux_owned WHERE id = :user_id AND id_jeu = :jeu_id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId, ':jeu_id' => $jeuId]);
        $row = $stmt->fetch();
        return $row ? (int)$row['score'] : 0;
    }

    public function buyGame(int $userId, int $jeuId, float $price): bool
{
    try {
        $this->pdo->beginTransaction();

        // Deduct credits
        $stmt = $this->pdo->prepare("UPDATE users SET credits = credits - ? WHERE id = ? AND credits >= ?");
        $stmt->execute([$price, $userId, $price]);
        if ($stmt->rowCount() === 0) {
            $this->pdo->rollBack();
            return false;
        }

        // INSERT — WILL WORK 100%
        $stmt = $this->pdo->prepare("INSERT INTO jeux_owned (id, id_jeu, score) VALUES (?, ?, 0)");
        $stmt->execute([$userId, $jeuId]);

        $this->pdo->commit();
        return true;

    } catch (Exception $e) {
        $this->pdo->rollBack();
        error_log("BUY FAILED: " . $e->getMessage());
        return false;
    }
}

    public function updateScore(int $userId, int $jeuId, int $newScore): bool
    {
        $sql = "UPDATE jeux_owned SET score = :score WHERE id = :user_id AND id_jeu = :jeu_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':score'    => $newScore,
            ':user_id'  => $userId,
            ':jeu_id'   => $jeuId
        ]);
    }
}