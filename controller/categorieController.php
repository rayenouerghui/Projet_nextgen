<?php


require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/models/categorie.php';

class CategorieController
{
    public function listeCategories()
    {
        $sql = "SELECT * FROM categorie ORDER BY nom_categorie";
        $db = config::getConnexion();
        try {
            $query = $db->query($sql);
            $categories = [];
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $cat = new Categorie(
                    $row['nom_categorie'],
                    $row['description'] ?? null,
                    $row['id_categorie']
                );
                $categories[] = $cat;
            }
            return $categories;
        } catch (Exception $e) {
            error_log('Erreur listeCategories: ' . $e->getMessage());
            return [];
        }
    }

    
    public function ajouterCategorie($categorie)
    {
        $sql = "INSERT INTO categorie (nom_categorie, description) VALUES (:nom, :desc)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':nom' => $categorie->getNomCategorie(),
                ':desc' => $categorie->getDescription()
            ]);
            return true;
        } catch (Exception $e) {
            error_log('Erreur ajouterCategorie: ' . $e->getMessage());
            return false;
        }
    }

    
    public function modifierCategorie($categorie)
    {
        $sql = "UPDATE categorie SET nom_categorie = :nom, description = :desc WHERE id_categorie = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':nom' => $categorie->getNomCategorie(),
                ':desc' => $categorie->getDescription(),
                ':id' => $categorie->getIdCategorie()
            ]);
            return true;
        } catch (Exception $e) {
            error_log('Erreur modifierCategorie: ' . $e->getMessage());
            return false;
        }
    }

   
    public function supprimerCategorie($id)
    {
        $sql = "DELETE FROM categorie WHERE id_categorie = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return true;
        } catch (Exception $e) {
            error_log('Erreur supprimerCategorie: ' . $e->getMessage());
            return false;
        }
    }

    
    public function getCategorie($id)
    {
        $sql = "SELECT * FROM categorie WHERE id_categorie = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            $row = $query->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;
            return new Categorie(
                $row['nom_categorie'],
                $row['description'] ?? null,
                $row['id_categorie']
            );
        } catch (Exception $e) {
            error_log('Erreur getCategorie: ' . $e->getMessage());
            return null;
        }
    }
}