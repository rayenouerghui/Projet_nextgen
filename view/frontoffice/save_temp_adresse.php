<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['temp_lat'] = $_POST['lat'] ?? '';
    $_SESSION['temp_lng'] = $_POST['lng'] ?? '';
    $_SESSION['temp_adresse'] = $_POST['adresse'] ?? '';
}