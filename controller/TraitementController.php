<?php
/**
 * Contrôleur TraitementController
 * Gère toutes les opérations CRUD pour les traitements avec jointure vers Reclamation
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Traitement.php';
require_once __DIR__ . '/../models/Reclamation.php';

class TraitementController {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    /**
     * Créer un nouveau traitement (CREATE)
     */
    public function create($traitement) {
        try {
            // Vérifier que la réclamation existe
            $sqlCheck = "SELECT idReclamation FROM reclamation WHERE idReclamation = :idReclamation";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([':idReclamation' => $traitement->getIdReclamation()]);
            $reclamationExiste = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            $stmtCheck->closeCursor(); // important avec les requêtes non bufferisées
            if (!$reclamationExiste) {
                return [
                    'success' => false,
                    'message' => 'La réclamation spécifiée n\'existe pas'
                ];
            }

            // Vérifier que l'utilisateur existe (si id_user est fourni)
            if ($traitement->getIdUser()) {
                $sqlCheckUser = "SELECT id FROM users WHERE id = :id_user";
                $stmtCheckUser = $this->pdo->prepare($sqlCheckUser);
                $stmtCheckUser->execute([':id_user' => $traitement->getIdUser()]);
                $userExiste = $stmtCheckUser->fetch(PDO::FETCH_ASSOC);
                $stmtCheckUser->closeCursor(); // important avec les requêtes non bufferisées
                if (!$userExiste) {
                    return [
                        'success' => false,
                        'message' => 'L\'utilisateur spécifié n\'existe pas'
                    ];
                }
            }

            // Utiliser id_user si fourni, sinon utiliser auteur (pour compatibilité)
            $id_user = $traitement->getIdUser() ? $traitement->getIdUser() : null;
            
            $sql = "INSERT INTO traitement (idReclamation, id_user, contenu, dateReclamation) 
                    VALUES (:idReclamation, :id_user, :contenu, :dateReclamation)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':idReclamation' => $traitement->getIdReclamation(),
                ':id_user' => $id_user,
                ':contenu' => $traitement->getContenu(),
                ':dateReclamation' => $traitement->getDateReclamation()
            ]);

            return [
                'success' => true,
                'message' => 'Traitement créé avec succès',
                'id' => $this->pdo->lastInsertId()
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lire tous les traitements avec jointures vers Reclamation et Utilisateur (READ ALL)
     */
    public function readAll() {
        try {
            $sql = "SELECT t.*, 
                           r.description as reclamation_description, r.statut as reclamation_statut, 
                           r.type as reclamation_type, r.produitConcerne,
                           u.email as auteur_email, u.nom as auteur_nom, u.prenom as auteur_prenom
                    FROM traitement t
                    INNER JOIN reclamation r ON t.idReclamation = r.idReclamation
                    LEFT JOIN users u ON t.id_user = u.id
                    ORDER BY t.dateReclamation DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [
                'error' => 'Erreur lors de la lecture : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lire un traitement par son ID avec jointures (READ ONE)
     */
    public function readById($id) {
        try {
            $sql = "SELECT t.*, 
                           r.description as reclamation_description, r.statut as reclamation_statut, 
                           r.type as reclamation_type, r.produitConcerne,
                           u.email as auteur_email, u.nom as auteur_nom, u.prenom as auteur_prenom
                    FROM traitement t
                    INNER JOIN reclamation r ON t.idReclamation = r.idReclamation
                    LEFT JOIN users u ON t.id_user = u.id
                    WHERE t.idTraitement = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result;
            } else {
                return [
                    'error' => 'Traitement non trouvé'
                ];
            }
        } catch (PDOException $e) {
            return [
                'error' => 'Erreur lors de la lecture : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lire tous les traitements d'une réclamation spécifique (JOINTURE)
     */
    public function readByReclamationId($idReclamation) {
        try {
            $sql = "SELECT t.*, 
                           r.description as reclamation_description, r.statut as reclamation_statut, 
                           r.type as reclamation_type, r.produitConcerne,
                           u.email as auteur_email, u.nom as auteur_nom, u.prenom as auteur_prenom
                    FROM traitement t
                    INNER JOIN reclamation r ON t.idReclamation = r.idReclamation
                    LEFT JOIN users u ON t.id_user = u.id
                    WHERE t.idReclamation = :idReclamation
                    ORDER BY t.dateReclamation DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':idReclamation' => $idReclamation]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [
                'error' => 'Erreur lors de la lecture : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour un traitement (UPDATE)
     */
    public function update($traitement) {
        try {
            $sql = "UPDATE traitement 
                    SET idReclamation = :idReclamation, 
                        id_user = :id_user,
                        contenu = :contenu, 
                        dateReclamation = :dateReclamation 
                    WHERE idTraitement = :idTraitement";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':idTraitement' => $traitement->getIdTraitement(),
                ':idReclamation' => $traitement->getIdReclamation(),
                ':id_user' => $traitement->getIdUser(),
                ':contenu' => $traitement->getContenu(),
                ':dateReclamation' => $traitement->getDateReclamation()
            ]);

            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Traitement mis à jour avec succès'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Aucun traitement trouvé avec cet ID'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Supprimer un traitement (DELETE)
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM traitement WHERE idTraitement = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Traitement supprimé avec succès'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Aucun traitement trouvé avec cet ID'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lire toutes les réclamations avec leurs traitements (JOINTURE COMPLÈTE)
     */
    public function readReclamationsWithTraitements() {
        try {
            $sql = "SELECT r.*, 
                           COUNT(t.idTraitement) as nombre_traitements,
                           GROUP_CONCAT(t.contenu SEPARATOR ' ||| ') as traitements_contenus
                    FROM reclamation r
                    LEFT JOIN traitement t ON r.idReclamation = t.idReclamation
                    GROUP BY r.idReclamation
                    ORDER BY r.dateReclamation DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [
                'error' => 'Erreur lors de la lecture : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lire une réclamation avec tous ses traitements (JOINTURE DÉTAILLÉE)
     */
    public function readReclamationWithTraitements($idReclamation) {
        try {
            // D'abord récupérer la réclamation
            $sqlReclamation = "SELECT * FROM reclamation WHERE idReclamation = :id";
            $stmtReclamation = $this->pdo->prepare($sqlReclamation);
            $stmtReclamation->execute([':id' => $idReclamation]);
            $reclamation = $stmtReclamation->fetch(PDO::FETCH_ASSOC);

            if (!$reclamation) {
                return [
                    'error' => 'Réclamation non trouvée'
                ];
            }

            // Ensuite récupérer tous les traitements
            $sqlTraitements = "SELECT * FROM traitement WHERE idReclamation = :id ORDER BY dateReclamation DESC";
            $stmtTraitements = $this->pdo->prepare($sqlTraitements);
            $stmtTraitements->execute([':id' => $idReclamation]);
            $traitements = $stmtTraitements->fetchAll(PDO::FETCH_ASSOC);

            return [
                'reclamation' => $reclamation,
                'traitements' => $traitements
            ];
        } catch (PDOException $e) {
            return [
                'error' => 'Erreur lors de la lecture : ' . $e->getMessage()
            ];
        }
    }
}
?>

