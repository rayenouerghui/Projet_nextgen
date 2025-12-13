<?php

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/jeu.php';
require_once dirname(__DIR__) . '/models/categorie.php';
require_once dirname(__DIR__) . '/controller/JeuOwnedController.php'; // ONLY THIS LINE ADDED

class JeuController
{
    public function ajouterJeu($jeu)
    {
        $sql = "INSERT INTO jeu (titre, prix, src_img, video_src, description, id_categorie) 
                VALUES (:titre, :prix, :src_img, :video_src, :description, :id_categorie)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':titre', $jeu->getTitre());
            $query->bindValue(':prix', $jeu->getPrix());
            $query->bindValue(':src_img', $jeu->getSrcImg());
            $query->bindValue(':video_src', $jeu->getVideoSrc());
            $query->bindValue(':description', $jeu->getDescription());
            $query->bindValue(':id_categorie', $jeu->getIdCategorie());
            $query->execute();
        } catch (Exception $e) {
            error_log('Erreur ajouterJeu: ' . $e->getMessage());
            throw $e;
        }
    }

    public function afficherJeux()
    {
        $sql = "SELECT j.*, c.nom_categorie 
                FROM jeu j 
                LEFT JOIN categorie c ON j.id_categorie = c.id_categorie 
                ORDER BY j.id_jeu DESC";
        $db = config::getConnexion();
        try {
            $query = $db->query($sql);
            $results = [];
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $jeu = new Jeu(
                    $row['titre'],
                    $row['prix'],
                    $row['src_img'],
                    $row['id_categorie'],
                    $row['id_jeu'],           // FIXED POSITION
                    $row['description'],
                    $row['video_src']   
                );
                $jeu->nom_categorie = $row['nom_categorie'] ?? null;

                // OWNERSHIP CHECK
                $jeu->isOwned = false;
                $jeu->userScore = 0;
                if (isset($_SESSION['user']['id'])) {
                    $ownedCtrl = new JeuOwnedController();
                    if ($ownedCtrl->isOwned($_SESSION['user']['id'], $jeu->getIdJeu())) {
                        $jeu->isOwned = true;
                        $jeu->userScore = $ownedCtrl->getScore($_SESSION['user']['id'], $jeu->getIdJeu());
                    }
                }

                $results[] = $jeu;
            }
            return $results;
        } catch (Exception $e) {
            error_log('Erreur afficherJeux: ' . $e->getMessage());
            return [];
        }
    }

    public function getJeu($id_jeu)
    {
        $sql = "SELECT j.*, c.nom_categorie 
                FROM jeu j 
                LEFT JOIN categorie c ON j.id_categorie = c.id_categorie 
                WHERE j.id_jeu = :id_jeu";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':id_jeu', $id_jeu, PDO::PARAM_INT);
            $query->execute();
            $row = $query->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            $jeu = new Jeu(
                $row['titre'],
                $row['prix'],
                $row['src_img'],
                $row['id_categorie'],
                $row['id_jeu'],           // FIXED POSITION
                $row['description'],
                $row['video_src']   
            );
            $jeu->nom_categorie = $row['nom_categorie'] ?? null;

            // OWNERSHIP CHECK
            $jeu->isOwned = false;
            $jeu->userScore = 0;
            if (isset($_SESSION['user']['id'])) {
                $ownedCtrl = new JeuOwnedController();
                if ($ownedCtrl->isOwned($_SESSION['user']['id'], $jeu->getIdJeu())) {
                    $jeu->isOwned = true;
                    $jeu->userScore = $ownedCtrl->getScore($_SESSION['user']['id'], $jeu->getIdJeu());
                }
            }

            return $jeu;
        } catch (Exception $e) {
            error_log('Erreur getJeu: ' . $e->getMessage());
            return null;
        }
    }

    public function modifierJeu($jeu)
    {
        $sql = "UPDATE jeu 
                SET titre = :titre, prix = :prix, src_img = :src_img, video_src = :video_src, description = :description, id_categorie = :id_categorie 
                WHERE id_jeu = :id_jeu";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':titre', $jeu->getTitre());
            $query->bindValue(':prix', $jeu->getPrix());
            $query->bindValue(':src_img', $jeu->getSrcImg());
            $query->bindValue(':video_src', $jeu->getVideoSrc());
            $query->bindValue(':description', $jeu->getDescription());
            $query->bindValue(':id_categorie', $jeu->getIdCategorie());
            $query->bindValue(':id_jeu', $jeu->getIdJeu());
            $query->execute();
        } catch (Exception $e) {
            error_log('Erreur modifierJeu: ' . $e->getMessage());
            throw $e;
        }
    }
        public function supprimerJeu($id)
    {
        $sql = "DELETE FROM jeu WHERE id_jeu = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();
        } catch (Exception $e) {
            error_log('Erreur supprimerJeu: ' . $e->getMessage());
            throw $e;
        }
    }
}