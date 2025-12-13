<?php
session_start();
if (!isset($_SESSION['user'])) exit;

require_once '../../controller/LivraisonController.php';
$controller = new LivraisonController();

$livraison = new Livraison(
    $_SESSION['user']['id'],
    $_POST['id_jeu'],
    $_SESSION['temp_adresse'],
    $_SESSION['temp_lat'],
    $_SESSION['temp_lng'],
    $_SESSION['temp_mode_paiement']
);

if ($controller->createLivraison($livraison)) {
    unset($_SESSION['temp_adresse'], $_SESSION['temp_lat'], $_SESSION['temp_lng'], $_SESSION['temp_mode_paiement']);
    header('Location: tracking.php?id_livraison=' . $controller->getLastInsertId());
} else {
    echo "Erreur lors de la commande";
}