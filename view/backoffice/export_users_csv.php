<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../frontoffice/connexion.php');
    exit;
}

require_once '../../config/config.php';
$pdo = Config::getConnexion();

require_once '../../controller/userController.php';
$userController = new userController();
$users = $userController->getAllUsers();

$filename = "utilisateurs_nextgen_" . date('d-m-Y') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo "\xEF\xBB\xBF"; // UTF-8 BOM

// En-tête en anglais + virgule = Excel ouvre direct en colonnes
echo "ID,First Name,Last Name,Email,Phone,Role,Credits,Registration Date\n";

foreach ($users as $u) {
    $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = ?");
    $stmt->execute([$u->getId()]);
    $row = $stmt->fetch();
    $date = ($row && $row['created_at']) ? date('d/m/Y H:i', strtotime($row['created_at'])) : 'Non définie';

    echo '"' . $u->getId() . '",';
    echo '"' . $u->getPrenom() . '",';
    echo '"' . $u->getNom() . '",';
    echo '"' . $u->getEmail() . '",';
    echo '"' . ($u->getTelephone() ?: '') . '",';
    echo '"' . ucfirst($u->getRole()) . '",';
    echo '"' . ($u->getCredits() ?? '0') . '",';
    echo '"' . $date . '"' . "\n";
}

exit;