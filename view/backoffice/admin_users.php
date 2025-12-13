<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../frontoffice/connexion.php');
    exit;
}

require_once '../../controller/userController.php';
$userController = new userController();

// SEARCH
$search = trim($_GET['search'] ?? '');
$users = $userController->getAllUsers();

if ($search !== '') {
    $users = array_filter($users, function($u) use ($search) {
        return stripos($u->getPrenom(), $search) !== false ||
        stripos($u->getNom(), $search) !== false ||
        stripos($u->getEmail(), $search) !== false ||
        stripos((string)$u->getId(), $search) !== false;
    });
}

if (isset($_GET['delete'])) {
    $userController->deleteUser((int)$_GET['delete']);
    header('Location: admin_users.php?success=1');
    exit;
}

$success = isset($_GET['success']) && !isset($_GET['action']);
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des Utilisateurs – NextGen Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Exo+2:wght@500;700&display=swap" rel="stylesheet">
  <style>
    body { background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); font-family: 'Exo 2', sans-serif; color: #e0e7ff; }
    .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(139,92,246,0.3); box-shadow: 0 8px 32px rgba(0,0,0,0.4); }
    .neon-text { text-shadow: 0 0 30px #8b5cf6; }
    .btn-neon { background: linear-gradient(45deg, #8b5cf6, #ec4899); box-shadow: 0 0 30px rgba(139,92,246,0.6); }
    .btn-neon:hover { transform: translateY(-3px); box-shadow: 0 0 40px rgba(139,92,246,0.9); }
    .sidebar-closed { width: 5rem !important; }
    .sidebar-open { width: 16rem; }
    .online-dot {
      position: absolute;
      bottom: 0;
      right: 0;
      width: 14px;
      height: 14px;
      background: #10b981;
      border: 3px solid #1e1b4b;
      border-radius: 50%;
      box-shadow: 0 0 15px #10b981;
      animation: pulse 2s infinite pulse;
    }
    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(16,185,129,0.7); }
      70% { box-shadow: 0 0 0 10px rgba(16,185,129,0); }
      100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
    }
  </style>
</head>
<body class="h-full" x-data="{ sidebarOpen: true }">

  <!-- FIXED COLLAPSIBLE SIDEBAR -->
  <aside :class="sidebarOpen ? 'sidebar-open' : 'sidebar-closed'" 
         class="glass fixed inset-y-0 left-0 z-40 flex flex-col transition-all duration-300 overflow-hidden">
    <div class="p-5 text-center border-b border-purple-500/30">
      <h1 class="text-3xl font-black bg-gradient-to-r from-cyan-400 to-purple-600 bg-clip-text text-transparent">NEXTGEN</h1>
      <p x-show="sidebarOpen" class="text-purple-300 text-sm font-bold mt-1">ADMIN PANEL</p>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-2">
      <?php $current = basename($_SERVER['PHP_SELF']); ?>
      <a href="admin_jeux.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= $current==='admin_jeux.php'?'bg-gradient-to-r from-purple-600 to-pink-600 text-white':'hover:bg-white/10 text-purple-300' ?>">
        <i class="fas fa-gamepad w-8 text-xl"></i>
        <span x-show="sidebarOpen">Jeux</span>
      </a>
      <a href="admin_users.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold">
        <i class="fas fa-users w-8 text-xl"></i>
        <span x-show="sidebarOpen">Utilisateurs</span>
      </a>
      <a href="admin_categories.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= $current==='admin_categories.php'?'bg-gradient-to-r from-purple-600 to-pink-600 text-white':'hover:bg-white/10 text-purple-300' ?>">
        <i class="fas fa-tags w-8 text-xl"></i>
        <span x-show="sidebarOpen">Catégories</span>
      </a>
      <a href="admin_historique.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= $current==='admin_historique.php'?'bg-gradient-to-r from-purple-600 to-pink-600 text-white':'hover:bg-white/10 text-purple-300' ?>">
        <i class="fas fa-history w-8 text-xl"></i>
        <span x-show="sidebarOpen">Historique</span>
      </a>
      <a href="admin_reclamations.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition hover:bg-white/10 text-purple-300">
        <i class="fas fa-exclamation-triangle w-8 text-xl"></i><span x-show="sidebarOpen">Réclamations</span>
      </a>
      <a href="admin_livraisons.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= $current==='admin_livraisons.php'?'bg-gradient-to-r from-orange-600 to-amber-600 text-white':'hover:bg-white/10 text-orange-300' ?>">
        <i class="fas fa-truck w-8 text-xl"></i><span x-show="sidebarOpen">Livraisons</span>
      </a>
    </nav>

    <div class="p-4 border-t border-purple-500/30">
      <button @click="sidebarOpen = !sidebarOpen" class="w-full py-4 hover:bg-white/5 transition rounded-lg">
        <i class="fas fa-chevron-left mx-auto text-xl text-purple-300" :class="{'rotate-180': !sidebarOpen}"></i>
      </button>
      <a href="../frontoffice/index.php" class="flex items-center justify-center gap-3 px-4 py-4 mt-3 rounded-xl bg-gradient-to-r from-cyan-500 to-teal-600 text-white font-bold hover:scale-105 transition">
        <i class="fas fa-home"></i>
        <span x-show="sidebarOpen">Retour accueil</span>
      </a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="min-h-screen transition-all duration-300" :class="sidebarOpen ? 'lg:ml-64 ml-20' : 'ml-20'">
    <div class="max-w-7xl mx-auto px-6 py-10">

      <h1 class="text-center text-5xl font-black bg-gradient-to-r from-cyan-400 via-purple-500 to-pink-500 bg-clip-text text-transparent neon-text mb-12">
        GESTION DES UTILISATEURS
      </h1>

      <!-- SEARCH + ADD BUTTON -->
      <div class="flex flex-col sm:flex-row gap-6 mb-10 glass p-6 rounded-2xl">
        <form method="GET" class="flex-1">
          <div class="relative">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-purple-400"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                   placeholder="Rechercher par nom, prénom, email ou ID..." 
                   class="w-full pl-12 pr-4 py-3 bg-black/40 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
          </div>
        </form>

        <div class="flex flex-col sm:flex-row gap-6">
        <a href="ajouter_users.php" class="btn-neon text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition text-center">
          + Ajouter un utilisateur
        </a>
        <a href="export_users_csv.php" class="btn-neon text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition flex items-center justify-center gap-3">
          <i class="fas fa-file-csv"></i> Exporter en CSV
        </a>
      </div>
      </div>

      <!-- SUCCESS TOAST -->
      <?php if ($success): ?>
      <div id="successToast" class="fixed top-6 left-1/2 -translate-x-1/2 z-50">
        <div class="px-10 py-5 bg-green-900/90 border-4 border-green-400 rounded-2xl text-green-300 text-2xl font-bold shadow-2xl">
          Action réussie !
        </div>
      </div>
      <script>
        setTimeout(() => {
          document.getElementById('successToast')?.remove();
          history.replaceState(null, null, 'admin_users.php');
        }, 3000);
      </script>
      <?php endif; ?>

      <!-- USERS TABLE -->
      <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gradient-to-r from-purple-800 to-pink-800">
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">ID</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Utilisateur</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Email</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Rôle</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Statut</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Crédits</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-cyan-300 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-purple-500/20">
              <?php foreach ($users as $u): ?>
              <tr class="hover:bg-white/5 transition">
                <td class="px-6 py-5 text-purple-300 font-medium">#<?= $u->getId() ?></td>
                <td class="px-6 py-5">
                  <div class="flex items-center gap-3">
                    <div class="relative">
                      <img src="../../resources/<?= htmlspecialchars($u->getPhotoProfil() ?: 'default.jpg') ?>" 
                           class="w-12 h-12 rounded-full object-cover ring-2 ring-purple-500">
                      <?php if ($u->isOnline()): ?>
                        <div class="online-dot"></div>
                      <?php endif; ?>
                    </div>
                    <div>
                      <div class="font-semibold text-white"><?= htmlspecialchars($u->getPrenom() . ' ' . $u->getNom()) ?></div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-5 text-cyan-300 text-sm"><?= htmlspecialchars($u->getEmail()) ?></td>
                <td class="px-6 py-5">
                  <span class="px-3 py-5 py-1 rounded-full text-xs font-bold text-white <?= $u->getRole() === 'admin' ? 'bg-gradient-to-r from-pink-600 to-purple-700' : 'bg-gradient-to-r from-cyan-500 to-blue-600' ?>">
                    <?= strtoupper($u->getRole()) ?>
                  </span>
                </td>
                <td class="px-6 py-5">
                  <span class="px-3 py-1 rounded-full text-xs font-bold text-white 
                    <?= $u->getStatut() === 'actif' ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 
                       ($u->getStatut() === 'suspendu' ? 'bg-gradient-to-r from-orange-500 to-amber-600' : 'bg-gradient-to-r from-red-600 to-rose-700') ?>">
                    <?= ucfirst($u->getStatut()) ?>
                  </span>
                </td>
                <td class="px-6 py-5 text-yellow-400 font-bold text-sm"><?= number_format($u->getCredits(), 2) ?> TND</td>
                <td class="px-6 py-5 text-center space-x-4">
                  <a href="modifier_users.php?id=<?= $u->getId() ?>" class="text-cyan-400 hover:text-cyan-200 text-sm font-medium mr-5">Modifier</a>
                  <a href="?delete=<?= $u->getId() ?>" onclick="return confirm('Supprimer cet utilisateur ?')" class="text-red-400 hover:text-red-300 text-sm font-medium">Supprimer</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php if (empty($users)): ?>
            <div class="text-center py-12 text-gray-400 text-lg">Aucun utilisateur trouvé.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

</body>
</html>