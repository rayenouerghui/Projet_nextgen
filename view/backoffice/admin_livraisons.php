<?php
/**
 * Admin Livraisons Entry Point
 */

require_once __DIR__ . '/../../config/paths.php';
require_once CONFIG_PATH . '/session.php';

requireAdmin();

require_once CONTROLLERS_PATH . '/LivraisonAdminController.php';

$controller = new LivraisonAdminController();
$controller->afficherPage();
