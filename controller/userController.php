<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/user.php';

class userController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Config::getConnexion();
    }

    public function addUser(User $user): bool
    {
        try {
            $sql = "INSERT INTO users (nom, prenom, email, telephone, mdp, role, photo_profil, credits, statut) 
                    VALUES (:nom, :prenom, :email, :telephone, :mdp, :role, 'default.jpg', 0.00, :statut)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nom'       => $user->getNom(),
                ':prenom'    => $user->getPrenom(),
                ':email'     => $user->getEmail(),
                ':telephone' => $user->getTelephone(),
                ':mdp'       => $user->getMdp(),
                ':role'      => $user->getRole(),
                ':statut'    => $user->getStatut()
            ]);

            $user->setId($this->pdo->lastInsertId());
            return true;
        } catch (PDOException $e) {
            error_log("Add user error: " . $e->getMessage());
            return false;
        }
    }

    public function updateUser(User $user): bool
    {
        try {
            if (empty($user->getMdp())) {
                $sql = "UPDATE users 
                        SET nom = :nom, prenom = :prenom, email = :email, 
                            telephone = :telephone, role = :role,
                            photo_profil = :photo_profil, credits = :credits, statut = :statut
                        WHERE id = :id";
                $params = [
                    ':nom'          => $user->getNom(),
                    ':prenom'       => $user->getPrenom(),
                    ':email'        => $user->getEmail(),
                    ':telephone'    => $user->getTelephone(),
                    ':role'         => $user->getRole(),
                    ':photo_profil' => $user->getPhotoProfil() ?? 'default.jpg',
                    ':credits'      => $user->getCredits(),
                    ':statut'       => $user->getStatut(),
                    ':id'           => $user->getId()
                ];
            } else {
                $sql = "UPDATE users 
                        SET nom = :nom, prenom = :prenom, email = :email, 
                            telephone = :telephone, mdp = :mdp, role = :role,
                            photo_profil = :photo_profil, credits = :credits, statut = :statut
                        WHERE id = :id";
                $params = [
                    ':nom'          => $user->getNom(),
                    ':prenom'       => $user->getPrenom(),
                    ':email'        => $user->getEmail(),
                    ':telephone'    => $user->getTelephone(),
                    ':mdp'          => $user->getMdp(),
                    ':role'         => $user->getRole(),
                    ':photo_profil' => $user->getPhotoProfil() ?? 'default.jpg',
                    ':credits'      => $user->getCredits(),
                    ':statut'       => $user->getStatut(),
                    ':id'           => $user->getId()
                ];
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return true;
        } catch (PDOException $e) {
            error_log("Update user error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteUser(int $id): bool
    {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            error_log("Delete user error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $data = $stmt->fetch();

        if (!$data) return null;

        return new User(
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['telephone'],
            $data['mdp'],
            $data['role'],
            (int)$data['id'],
            $data['photo_profil'] ?? 'default.jpg',
            (float)($data['credits'] ?? 0.00),
            $data['statut'] ?? 'actif',
            $data['last_login']
        );
    }

    public function getUserById(int $id): ?User
    {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();

        if (!$data) return null;

        return new User(
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['telephone'],
            $data['mdp'],
            $data['role'],
            (int)$data['id'],
            $data['photo_profil'] ?? 'default.jpg',
            (float)($data['credits'] ?? 0.00),
            $data['statut'] ?? 'actif',
            $data['last_login']
        );
    }

    public function getAllUsers(): array
    {
        $sql = "SELECT * FROM users ORDER BY id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $users = [];
        foreach ($results as $row) {
            $users[] = new User(
                $row['nom'],
                $row['prenom'],
                $row['email'],
                $row['telephone'],
                $row['mdp'],
                $row['role'],
                (int)$row['id'],
                $row['photo_profil'] ?? 'default.jpg',
                (float)($row['credits'] ?? 0.00),
                $row['statut'] ?? 'actif',
                $row['last_login']
            );
        }
        return $users;
    }

    public function updateUserPhoto(int $userId, string $photoName): bool
    {
        try {
            $sql = "UPDATE users SET photo_profil = :photo WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':photo' => $photoName,
                ':id'    => $userId
            ]);

            if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $userId) {
                $_SESSION['user']['photo_profil'] = $photoName;
            }
            return true;
        } catch (Exception $e) {
            error_log("Erreur update photo: " . $e->getMessage());
            return false;
        }
    }

    public function getCurrentProfilePicture(int $userId): string
    {
        $sql = "SELECT photo_profil FROM users WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['photo_profil'] ?? 'default.jpg';
    }
}