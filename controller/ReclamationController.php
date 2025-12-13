<?php
/**
 * Contrôleur ReclamationController
 * Gère toutes les opérations CRUD pour les réclamations
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Reclamation.php';

class ReclamationController {
    private $pdo;
    private $aiAnalyzerAvailable = false;
    private $pythonPath = 'python3';
    private $aiModulePath;
    
    // Mots inappropriés pour le fallback PHP
    private $motsInappropries = [
        'con', 'idiot', 'imbecile', 'imbécile', 'merde', 'pute', 'salope',
        'encule', 'enculé', 'batard', 'bâtard', 'fuck', 'shit', 'asshole'
    ];

    public function __construct() {
        $this->pdo = config::getConnexion();
        
        // Initialiser le chemin du module IA
        $this->aiModulePath = __DIR__ . '/../ai_module/analyse_reclamation.py';
        
        // Vérifier si Python est disponible
        $this->checkAIAvailability();
    }
    
    /**
     * Vérifier si le système d'IA est disponible
     */
    private function checkAIAvailability() {
        if (!file_exists($this->aiModulePath)) {
            return;
        }
        
        // Vérifier si Python est installé
        $output = shell_exec('python3 --version 2>&1');
        if ($output !== null && strpos($output, 'Python') !== false) {
            $this->aiAnalyzerAvailable = true;
        } else {
            // Essayer avec 'python' au lieu de 'python3'
            $output = shell_exec('python --version 2>&1');
            if ($output !== null && strpos($output, 'Python') !== false) {
                $this->pythonPath = 'python';
                $this->aiAnalyzerAvailable = true;
            }
        }
    }
    
    /**
     * Analyser un message avec le système d'IA local
     */
    public function analyzeMessageWithAI($message) {
        if (!$this->aiAnalyzerAvailable) {
            // Fallback vers la vérification PHP simple
            return $this->analyzeMessagePHP($message);
        }
        
        try {
            $escaped_message = escapeshellarg($message);
            $command = $this->pythonPath . ' ' . escapeshellarg($this->aiModulePath) . ' ' . $escaped_message;
            
            $output = shell_exec($command . ' 2>&1');
            
            if ($output === null) {
                return $this->analyzeMessagePHP($message);
            }
            
            $result = json_decode($output, true);
            
            if ($result === null || !is_array($result)) {
                return $this->analyzeMessagePHP($message);
            }
            
            return $result;
        } catch (Exception $e) {
            // En cas d'erreur, utiliser le fallback PHP
            return $this->analyzeMessagePHP($message);
        }
    }
    
    /**
     * Analyser un message avec les fonctions PHP simples (fallback)
     */
    private function analyzeMessagePHP($message) {
        $has_badwords = $this->contientMotsInappropries($message);
        
        if (empty(trim($message))) {
            return [
                'valid' => false,
                'reason' => 'Message vide',
                'score' => 0.0,
                'details' => []
            ];
        }
        
        if (strlen($message) < 5) {
            return [
                'valid' => false,
                'reason' => 'Message trop court (minimum 5 caractères)',
                'score' => 0.1,
                'details' => []
            ];
        }
        
        if ($has_badwords) {
            return [
                'valid' => false,
                'reason' => 'Message contenant des paroles impolis ou offensantes',
                'score' => 0.0,
                'details' => []
            ];
        }
        
        // Vérifier si le message est du bruit (trop de caractères non-valides)
        if ($this->isNonsenseMessage($message)) {
            return [
                'valid' => false,
                'reason' => 'Message détecté comme du bruit ou sans sens',
                'score' => 0.2,
                'details' => []
            ];
        }
        
        // Message valide selon les critères simples
        return [
            'valid' => true,
            'reason' => 'Message valide et approprié',
            'score' => 0.8,
            'details' => []
        ];
    }
    
    /**
     * Détecter si un message est du bruit/sans sens
     */
    private function isNonsenseMessage($message) {
        $text_lower = strtolower($message);
        
        // Compter les consonnes et voyelles
        $consonants = 0;
        $vowels = 0;
        $other = 0;
        
        for ($i = 0; $i < strlen($text_lower); $i++) {
            $char = $text_lower[$i];
            if (preg_match('/[bcdfghjklmnpqrstvwxyz]/', $char)) {
                $consonants++;
            } elseif (preg_match('/[aeiouyàâäéèêëîïôöùûüœæ]/', $char)) {
                $vowels++;
            } else {
                $other++;
            }
        }
        
        $total = $consonants + $vowels;
        
        // Si plus de 60% de consonnes (anormal), c'est du bruit
        if ($total > 5 && ($consonants / $total) > 0.60) {
            return true;
        }
        
        // Vérifier les répétitions excessives de caractères
        if (preg_match('/(.)\1{3,}/', $text_lower)) {
            return true;
        }
        
        // Vérifier les caractères spéciaux en excès (tirets, underscores, etc.)
        $special_count = $other;
        if ($total > 0 && ($special_count / ($total + $special_count)) > 0.2) {
            return true;
        }
        
        // Vérifier si c'est une suite de syllabes sans sens
        $words = preg_split('/\s+/', trim($text_lower), -1, PREG_SPLIT_NO_EMPTY);
        if (count($words) >= 1) {
            // Analyser chaque mot
            foreach ($words as $word) {
                $word_consonants = preg_match_all('/[bcdfghjklmnpqrstvwxyz]/', $word);
                $word_vowels = preg_match_all('/[aeiouyàâäéèêëîïôöùûüœæ]/', $word);
                $word_total = $word_consonants + $word_vowels;
                
                // Si un mot a plus de 70% consonnes ET plus de 3 caractères = suspect
                if ($word_total > 3 && ($word_consonants / $word_total) > 0.70) {
                    return true;
                }
            }
        }
        
        return false;
    }

    private function normaliserTexte($texte) {
        $texte = mb_strtolower($texte, 'UTF-8');
        $translit = iconv('UTF-8', 'ASCII//TRANSLIT', $texte);
        if ($translit !== false) {
            $texte = $translit;
        } else {
            $texte = strtr($texte, [
                'à' => 'a', 'â' => 'a', 'ä' => 'a',
                'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
                'î' => 'i', 'ï' => 'i',
                'ô' => 'o', 'ö' => 'o',
                'ù' => 'u', 'û' => 'u', 'ü' => 'u',
                'ç' => 'c'
            ]);
        }
        return $texte;
    }

    private function contientMotsInappropries($texte) {
        $normalise = $this->normaliserTexte($texte);
        $mots = preg_split('/[^a-z0-9]+/', $normalise);
        foreach ($mots as $mot) {
            if (!$mot) {
                continue;
            }
            if (in_array($mot, $this->motsInappropries, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Créer une nouvelle réclamation (CREATE)
     */
    public function create($reclamation) {
        try {
            $description = $reclamation->getDescription() ?? '';
            
            // Analyser le message avec le système d'IA
            $aiAnalysis = $this->analyzeMessageWithAI($description);
            
            // Si le message est rejeté par l'IA
            if ($aiAnalysis['valid'] === false) {
                return [
                    'success' => false,
                    'message' => 'Votre message a été rejeté : ' . $aiAnalysis['reason'],
                    'ai_analysis' => $aiAnalysis
                ];
            }
            
            // Si le message nécessite une réécriture (score entre 0.4 et 0.7)
            if ($aiAnalysis['valid'] === null) {
                return [
                    'success' => false,
                    'message' => $aiAnalysis['reason'],
                    'ai_analysis' => $aiAnalysis,
                    'needs_rewrite' => true
                ];
            }
            
            // Vérifier que l'utilisateur existe
            if ($reclamation->getIdUser()) {
                $sqlCheck = "SELECT id FROM users WHERE id = :id_user";
                $stmtCheck = $this->pdo->prepare($sqlCheck);
                $stmtCheck->execute([':id_user' => $reclamation->getIdUser()]);
                $userExiste = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                $stmtCheck->closeCursor(); // important avec les requêtes non bufferisées
                if (!$userExiste) {
                    return [
                        'success' => false,
                        'message' => 'L\'utilisateur spécifié n\'existe pas'
                    ];
                }
            }

            $sql = "INSERT INTO reclamation (id_user, id_jeu, description, dateReclamation, statut, type, produitConcerne) 
                    VALUES (:id_user, :id_jeu, :description, :dateReclamation, :statut, :type, :produitConcerne)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id_user' => $reclamation->getIdUser(),
                ':id_jeu' => $reclamation->getIdJeu(),
                ':description' => $reclamation->getDescription(),
                ':dateReclamation' => $reclamation->getDateReclamation(),
                ':statut' => $reclamation->getStatut(),
                ':type' => $reclamation->getType(),
                ':produitConcerne' => $reclamation->getProduitConcerne()
            ]);

            return [
                'success' => true,
                'message' => 'Réclamation créée avec succès',
                'id' => $this->pdo->lastInsertId(),
                'ai_score' => $aiAnalysis['score'] ?? null
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lire toutes les réclamations (READ ALL) avec jointures
     */
    public function readAll() {
        try {
            $sql = "SELECT r.*, 
                           u.email as user_email, u.nom as user_nom, u.prenom as user_prenom,
                           j.titre as jeu_titre, j.prix as jeu_prix
                    FROM reclamation r
                    LEFT JOIN users u ON r.id_user = u.id
                    LEFT JOIN jeu j ON r.id_jeu = j.id_jeu
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
     * Lire une réclamation par son ID (READ ONE) avec jointures
     */
    public function readById($id) {
        try {
            $sql = "SELECT r.*, 
                           u.email as user_email, u.nom as user_nom, u.prenom as user_prenom,
                           j.titre as jeu_titre, j.prix as jeu_prix
                    FROM reclamation r
                    LEFT JOIN users u ON r.id_user = u.id
                    LEFT JOIN jeu j ON r.id_jeu = j.id_jeu
                    WHERE r.idReclamation = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result;
            } else {
                return [
                    'error' => 'Réclamation non trouvée'
                ];
            }
        } catch (PDOException $e) {
            return [
                'error' => 'Erreur lors de la lecture : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lire les réclamations d'un utilisateur spécifique
     */
    public function readByUserId($userId) {
        try {
            $sql = "SELECT r.*, 
                           u.email as user_email, u.nom as user_nom, u.prenom as user_prenom,
                           j.titre as jeu_titre, j.prix as jeu_prix
                    FROM reclamation r
                    LEFT JOIN users u ON r.id_user = u.id
                    LEFT JOIN jeu j ON r.id_jeu = j.id_jeu
                    WHERE r.id_user = :id_user
                    ORDER BY r.dateReclamation DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_user' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [
                'error' => 'Erreur lors de la lecture : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour une réclamation (UPDATE)
     */
    public function update($reclamation) {
        try {
            if ($this->contientMotsInappropries($reclamation->getDescription() ?? '')) {
                return [
                    'success' => false,
                    'message' => 'Votre description contient des mots inappropriés. Merci de reformuler.'
                ];
            }
            $sql = "UPDATE reclamation 
                    SET id_user = :id_user,
                        id_jeu = :id_jeu,
                        description = :description, 
                        dateReclamation = :dateReclamation, 
                        statut = :statut, 
                        type = :type, 
                        produitConcerne = :produitConcerne 
                    WHERE idReclamation = :idReclamation";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':idReclamation' => $reclamation->getIdReclamation(),
                ':id_user' => $reclamation->getIdUser(),
                ':id_jeu' => $reclamation->getIdJeu(),
                ':description' => $reclamation->getDescription(),
                ':dateReclamation' => $reclamation->getDateReclamation(),
                ':statut' => $reclamation->getStatut(),
                ':type' => $reclamation->getType(),
                ':produitConcerne' => $reclamation->getProduitConcerne()
            ]);

            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Réclamation mise à jour avec succès'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Aucune réclamation trouvée avec cet ID'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
            ];
        }
    }


    public function delete($id) {
        try {
            // Démarrer une transaction pour garantir l'intégrité des données
            $this->pdo->beginTransaction();

            // Supprimer d'abord tous les traitements associés
            $sqlDeleteTraitements = "DELETE FROM traitement WHERE idReclamation = :id";
            $stmtDeleteTraitements = $this->pdo->prepare($sqlDeleteTraitements);
            $stmtDeleteTraitements->execute([':id' => $id]);
            $traitementsDeleted = $stmtDeleteTraitements->rowCount();

            // Ensuite supprimer la réclamation
            $sql = "DELETE FROM reclamation WHERE idReclamation = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() > 0) {
                // Valider la transaction
                $this->pdo->commit();
                
                $message = 'Réclamation supprimée avec succès';
                if ($traitementsDeleted > 0) {
                    $message .= ' (' . $traitementsDeleted . ' traitement' . ($traitementsDeleted > 1 ? 's' : '') . ' supprimé' . ($traitementsDeleted > 1 ? 's' : '') . ')';
                }
                
                return [
                    'success' => true,
                    'message' => $message
                ];
            } else {
                // Annuler la transaction si la réclamation n'existe pas
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'Aucune réclamation trouvée avec cet ID'
                ];
            }
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mettre à jour le statut d'une réclamation
     */
    public function updateStatut($id, $nouveauStatut) {
        try {
            $sql = "UPDATE reclamation SET statut = :statut WHERE idReclamation = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':statut' => $nouveauStatut
            ]);

            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Statut mis à jour avec succès'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Aucune réclamation trouvée avec cet ID'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lire les réclamations par statut
     */
    public function readByStatut($statut) {
        try {
            $sql = "SELECT * FROM reclamation WHERE statut = :statut ORDER BY dateReclamation DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':statut' => $statut]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [
                'error' => 'Erreur lors de la lecture : ' . $e->getMessage()
            ];
        }
    }
}
?>

