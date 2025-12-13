<?php
/**
 * Session Management Helper
 * Include this file when you need session functionality
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if current user is admin
 */
function isAdmin(): bool {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

/**
 * Require admin access or redirect
 */
function requireAdmin(): void {
    if (!isAdmin()) {
        require_once __DIR__ . '/paths.php';
        redirect('/view/frontoffice/connexion.php');
    }
}

/**
 * Get current logged-in user
 */
function getCurrentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

/**
 * Require login or redirect
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        require_once __DIR__ . '/paths.php';
        redirect('/view/frontoffice/connexion.php');
    }
}
