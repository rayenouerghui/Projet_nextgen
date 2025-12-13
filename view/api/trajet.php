<?php
/**
 * API Endpoint for Real-time GPS Tracking
 * Entry point - calls TrajetApiController for data
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, log them
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config/paths.php';

try {
    require_once CONTROLLERS_PATH . '/TrajetApiController.php';
} catch (Exception $e) {
    error_log("Failed to load TrajetApiController: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur chargement contrôleur']);
    exit;
}

$id = isset($_GET['id_livraison']) ? (int)$_GET['id_livraison'] : 0;

if ($id <= 0) {
    error_log("Invalid id_livraison: $id");
    http_response_code(400);
    echo json_encode(['error' => 'id_livraison invalide']);
    exit;
}

try {
    $controller = new TrajetApiController();
    $data = $controller->getTrackingData($id);
    
    // If data contains error, it's already logged
    if (isset($data['error'])) {
        error_log("API returned error for livraison $id: " . $data['error']);
    }
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log("Exception in trajet.php for livraison $id: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
