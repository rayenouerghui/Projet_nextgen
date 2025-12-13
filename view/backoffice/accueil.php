<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../frontoffice/connexion.php');
    exit;
}

require_once '../../controller/jeuController.php';
require_once '../../controller/userController.php';
require_once '../../controller/CategorieController.php';
require_once '../../controller/LivraisonController.php';

$jeuController = new JeuController();
$userController = new userController();
$catController = new CategorieController();
$livController = new LivraisonController();

$totalJeux = count($jeuController->afficherJeux());
$totalUsers = count($userController->getAllUsers());
$totalCategories = count($catController->listeCategories());
$totalLivraisons = count($livController->getAllLivraisons());
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de bord – NextGen Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="h-full bg-gray-50 font-sans" x-data="{ sidebarOpen: true }">

<div class="flex min-h-screen">

  <!-- FIXED SIDEBAR -->
  <aside :class="sidebarOpen ? 'w-64' : 'w-20'" 
         class="fixed inset-y-0 left-0 bg-white shadow-xl transition-all duration-300 flex flex-col z-50 overflow-y-auto">
    
    <div class="p-6 border-b">
      <div class="flex items-center space-x-4">
        <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center text-white text-xl font-bold shadow-lg">NG</div>
        <h2 :class="{ 'hidden': !sidebarOpen }" class="text-2xl font-bold text-gray-800">NextGen Admin</h2>
      </div>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-2">
      <?php $current = basename($_SERVER['PHP_SELF']); ?>

      <a href="Accueil.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?= $current==='Accueil.php' ? 'bg-teal-50 text-teal-600 font-medium' : 'hover:bg-teal-50 text-gray-700 hover:text-teal-600' ?>">
        <i class="fas fa-tachometer-alt text-xl"></i>
        <span :class="{ 'hidden': !sidebarOpen }">Accueil</span>
      </a>
      <a href="admin_jeux.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?= $current==='admin_jeux.php' ? 'bg-teal-50 text-teal-600 font-medium' : 'hover:bg-teal-50 text-gray-700 hover:text-teal-600' ?>">
        <i class="fas fa-gamepad text-xl"></i>
        <span :class="{ 'hidden': !sidebarOpen }">Jeux</span>
      </a>
      <a href="admin_users.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?= $current==='admin_users.php' ? 'bg-teal-50 text-teal-600 font-medium' : 'hover:bg-teal-50 text-gray-700 hover:text-teal-600' ?>">
        <i class="fas fa-users text-xl"></i>
        <span :class="{ 'hidden': !sidebarOpen }">Utilisateurs</span>
      </a>
      <a href="admin_categories.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?= $current==='admin_categories.php' ? 'bg-teal-50 text-teal-600 font-medium' : 'hover:bg-teal-50 text-gray-700 hover:text-teal-600' ?>">
        <i class="fas fa-tags text-xl"></i>
        <span :class="{ 'hidden': !sidebarOpen }">Catégories</span>
      </a>
      <a href="admin_livraisons.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition <?= $current==='admin_livraisons.php' ? 'bg-teal-50 text-teal-600 font-medium' : 'hover:bg-teal-50 text-gray-700 hover:text-teal-600' ?>">
        <i class="fas fa-truck text-xl"></i>
        <span :class="{ 'hidden': !sidebarOpen }">Livraisons</span>
      </a>
    </nav>

    <div class="p-4 border-t space-y-3" :class="{ 'hidden': !sidebarOpen }">
      <a href="../frontoffice/catalogue.php" class="block text-center py-2 px-4 bg-gray-100 rounded-lg hover:bg-gray-200 text-sm">Voir le Catalogue</a>
      <a href="../frontoffice/index.php" class="block text-center py-2 px-4 bg-gray-100 rounded-lg hover:bg-gray-200 text-sm">Voir le Site</a>
      <button onclick="location.href='logout.php'" class="w-full py-2 px-4 bg-red-500 text-white rounded-lg hover:bg-red-600 font-medium">Déconnexion</button>
    </div>
  </aside>

  <!-- MAIN CONTENT – DYNAMIC LEFT MARGIN -->
  <div class="flex-1 transition-all duration-300" :class="sidebarOpen ? 'ml-64' : 'ml-20'">

    <!-- Header -->
    <header class="bg-white shadow-sm border-b sticky top-0 z-40">
      <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <button @click="sidebarOpen = !sidebarOpen" class="text-gray-600 hover:text-teal-600">
            <i class="fas fa-bars text-2xl"></i>
          </button>
          <h1 class="text-2xl font-bold text-gray-800">Tableau de bord Admin</h1>
        </div>
        <div class="flex items-center space-x-5">
          <div class="relative">
            <input type="text" placeholder="Recherche..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
          </div>
          <img src="https://randomuser.me/api/portraits/women/44.jpg" class="w-10 h-10 rounded-full ring-2 ring-teal-500 object-cover" alt="Admin">
        </div>
      </div>
    </header>

    <!-- Dashboard Content -->
    <main class="p-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white rounded-xl shadow-sm p-6 flex items-center space-x-5">
          <div class="w-14 h-14 bg-teal-100 rounded-full flex items-center justify-center">
            <i class="fas fa-gamepad text-2xl text-teal-600"></i>
          </div>
          <div>
            <h3 class="text-3xl font-bold"><?= $totalJeux ?></h3>
            <p class="text-gray-500">Jeux disponibles</p>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 flex items-center space-x-5">
          <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
            <i class="fas fa-users text-2xl text-green-600"></i>
          </div>
          <div>
            <h3 class="text-3xl font-bold"><?= $totalUsers ?></h3>
            <p class="text-gray-500">Utilisateurs</p>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 flex items-center space-x-5">
          <div class="w-14 h-14 bg-purple-100 rounded-full flex items-center justify-center">
            <i class="fas fa-tags text-2xl text-purple-600"></i>
          </div>
          <div>
            <h3 class="text-3xl font-bold"><?= $totalCategories ?></h3>
            <p class="text-gray-500">Catégories</p>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-6 flex items-center space-x-5">
          <div class="w-14 h-14 bg-orange-100 rounded-full flex items-center justify-center">
            <i class="fas fa-truck text-2xl text-orange-600"></i>
          </div>
          <div>
            <h3 class="text-3xl font-bold"><?= $totalLivraisons ?></h3>
            <p class="text-gray-500">Livraisons</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white p-6 rounded-xl shadow-sm">
          <h3 class="text-lg font-semibold mb-6">Répartition des Jeux par Catégorie</h3>
          <canvas id="pieChart" height="300"></canvas>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm">
          <h3 class="text-lg font-semibold mb-6">Activité Récente</h3>
          <canvas id="barChart" height="300"></canvas>
        </div>
      </div>
    </main>
  </div>
</div>

<script>
  new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: {
      labels: ['Action', 'Aventure', 'RPG', 'Sport', 'Simulation'],
      datasets: [{
        data: [35, 25, 20, 12, 8],
        backgroundColor: ['#8b5cf6', '#ec4899', '#10b981', '#f59e0b', '#3b82f6'],
        borderWidth: 0
      }]
    },
    options: {
      cutout: '70%',
      plugins: { legend: { position: 'bottom' } }
    }
  });

  new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
      labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
      datasets: [{
        data: [2, 4, 1, 3, 5, 2, 3],
        backgroundColor: '#14b8a6',
        borderRadius: 6
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
</script>
</body>
</html>