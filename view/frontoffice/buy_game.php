<?php
session_start();

require_once '../../controller/JeuController.php';
require_once '../../controller/JeuOwnedController.php';
require_once '../../controller/userController.php';
require_once '../../controller/HistoriqueController.php';  // NOUVEAU

header('Content-Type: application/json');

if (!isset($_SESSION['user']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Connectez-vous']);
 exit;
}

// GET THE GAME ID
$id_jeu = (int)($_POST['id_jeu'] ?? 0);

if ($id_jeu <= 0) {
 echo json_encode(['success' => false, 'message' => 'Jeu invalide']);
 exit;
}

$jeuCtrl = new JeuController();
$jeu = $jeuCtrl->getJeu($id_jeu);

if (!$jeu) {
 echo json_encode(['success' => false, 'message' => 'Jeu introuvable']);
 exit;
}

$userId = (int)$_SESSION['user']['id'];
$price  = $jeu->getPrix();

$userCtrl = new userController();
$user = $userCtrl->getUserById($userId);

if ($user->getCredits() < $price) {
 echo json_encode(['success' => false, 'message' => 'Pas assez de crédits']);
 exit;
}

$ownedCtrl = new JeuOwnedController();

if ($ownedCtrl->isOwned($userId, $id_jeu)) {
 echo json_encode(['success' => false, 'message' => 'Déjà possédé']);
 exit;
}

// REAL PURCHASE
if ($ownedCtrl->buyGame($userId, $id_jeu, $price)) {
    // RELOAD USER TO GET NEW CREDITS
    $user = $userCtrl->getUserById($userId);
    $_SESSION['user']['credits'] = $user->getCredits();

    // ADD TO HISTORIQUE — THIS IS WHAT YOU WANTED
    $histCtrl = new HistoriqueController();
    $h = new Historique(
        $userId,
        'purchase',
        'Achat : ' . $jeu->getTitre(),
        null,
        date('Y-m-d H:i:s')
    );
    $histCtrl->addHistorique($h);

    echo json_encode([
        'success' => true,
        'titre'   => $jeu->getTitre(),
        'credits' => number_format($user->getCredits(), 2)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Échec de l\'achat']);
}
exit;