<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/paths.php';
require_once CONTROLLERS_PATH . '/TrajetApiController.php';

$id = isset($_GET['id_livraison']) ? (int)$_GET['id_livraison'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'id_livraison invalide']);
    exit;
}

$controller = new TrajetApiController();

try {
    $data = $controller->getTrackingData($id);
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}