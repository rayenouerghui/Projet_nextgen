<?php
/**
 * Point d'entrée pour la page livraison gaming
 * Redirige vers le contrôleur qui charge la vue avec toutes les fonctionnalités
 */

require_once __DIR__ . '/../controller/LivraisonPageController.php';

$controller = new LivraisonPageController();
$controller->afficherPage();
