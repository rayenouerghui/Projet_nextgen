<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../frontoffice/connexion.php');
    exit;
}

require_once '../../controller/jeuController.php';
$jeuController = new JeuController();

// SUPPRESSION
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $jeuController->supprimerJeu($id);
        header('Location: admin_jeux.php?success=delete');
    } catch (Exception $e) {
        header('Location: admin_jeux.php?error=delete');
    }
    exit;
}

// RECHERCHE
$search = trim($_GET['search'] ?? '');
$jeux = $jeuController->afficherJeux();

if ($search !== '') {
    $searchLower = strtolower($search);
    $jeux = array_filter($jeux, function($j) use ($searchLower) {
        return strpos(strtolower($j->getTitre()), $searchLower) !== false ||
               strpos(strtolower($j->nom_categorie ?? ''), $searchLower) !== false ||
               strpos((string)$j->getIdJeu(), $search) !== false ||
               strpos((string)$j->getPrix(), $search) !== false;
    });
}

$success = isset($_GET['success']);
$error   = isset($_GET['error']);
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des Jeux – NextGen Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Exo+2:wght@500;700&display=swap" rel="stylesheet">
  <style>
    body { background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); font-family: 'Exo 2', sans-serif; color: #e0e7ff; }
    .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(139,92,246,0.3); box-shadow: 0 8px 32px rgba(0,0,0,0.4); }
    .neon-text { text-shadow: 0 0 30px #8b5cf6; }
    .btn-neon { background: linear-gradient(45deg, #8b5cf6, #ec4899); box-shadow: 0 0 30px rgba(139,92,246,0.6); }
    .btn-neon:hover { transform: translateY(-3px); box-shadow: 0 0 0 40px rgba(139,92,246,0.9); }
    .sidebar-closed { width: 5rem !important; }
    .sidebar-open { width: 16rem; }
  </style>
</head>
<body class="h-full" x-data="{ sidebarOpen: true }">

  <!-- SIDEBAR FIXE + COLLAPSIBLE -->
  <aside :class="sidebarOpen ? 'sidebar-open' : 'sidebar-closed'" 
         class="glass fixed inset-y-0 left-0 z-40 flex flex-col transition-all duration-300 overflow-hidden">
    <div class="p-5 text-center border-b border-purple-500/30">
      <h1 class="text-3xl font-black bg-gradient-to-r from-cyan-400 to-purple-600 bg-clip-text text-transparent">NEXTGEN</h1>
      <p x-show="sidebarOpen" class="text-purple-300 text-sm font-bold mt-1">ADMIN PANEL</p>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-2">
      <?php $current = basename($_SERVER['PHP_SELF']); ?>
      <a href="admin_jeux.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold">
        <i class="fas fa-gamepad w-8 text-xl"></i>
        <span x-show="sidebarOpen">Jeux</span>
      </a>
      <a href="admin_users.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= $current==='admin_users.php'?'bg-gradient-to-r from-purple-600 to-pink-600 text-white':'hover:bg-white/10 text-purple-300' ?>">
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

  <!-- CONTENU PRINCIPAL -->
  <main class="min-h-screen transition-all duration-300" :class="sidebarOpen ? 'lg:ml-64 ml-20' : 'ml-20'">
    <div class="max-w-7xl mx-auto px-6 py-10">

      <h1 class="text-center text-5xl font-black bg-gradient-to-r from-cyan-400 via-purple-500 to-pink-500 bg-clip-text text-transparent neon-text mb-12">
        GESTION DES JEUX
      </h1>

      <!-- BARRE RECHERCHE + BOUTON AJOUT -->
      <div class="flex flex-col sm:flex-row gap-6 mb-10 glass p-6 rounded-2xl">
        <form method="GET" class="flex-1">
          <div class="relative">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-purple-400"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                   placeholder="Rechercher un jeu (titre, catégorie, prix, ID)..." 
                   class="w-full pl-12 pr-4 py-3 bg-black/40 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
          </div>
        </form>

        <a href="ajouter_jeux.php" class="btn-neon text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition text-center">
          + Ajouter un jeu
        </a>
      </div>

      <!-- MESSAGES SUCCÈS / ERREUR -->
      <?php if ($success): ?>
      <div id="successToast" class="fixed top-6 left-1/2 -translate-x-1/2 z-50">
        <div class="px-10 py-5 bg-green-900/90 border-4 border-green-400 rounded-2xl text-green-300 text-2xl font-bold shadow-2xl">
          Action confirmer !
        </div>
      </div>
      <?php endif; ?>

      <?php if ($error): ?>
      <div id="errorToast" class="fixed top-6 left-1/2 -translate-x-1/2 z-50">
        <div class="px-10 py-5 bg-red-900/90 border-4 border-red-400 rounded-2xl text-red-300 text-2xl font-bold shadow-2xl">
          Erreur lors de la suppression.
        </div>
      </div>
      <?php endif; ?>

      <?php if ($success || $error): ?>
      <script>
        setTimeout(() => {
          document.getElementById('successToast')?.remove();
          document.getElementById('errorToast')?.remove();
          history.replaceState(null, null, 'admin_jeux.php');
        }, 3000);
      </script>
      <?php endif; ?>

      <!-- TABLEAU DES JEUX -->
      <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gradient-to-r from-purple-800 to-pink-800">
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">ID</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Image</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Titre</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Catégorie</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Prix</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-cyan-300 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-purple-500/20">
              <?php if (empty($jeux)): ?>
                <tr>
                  <td colspan="6" class="text-center py-16 text-gray-400 text-lg">Aucun jeu trouvé.</td>
                </tr>
              <?php else: foreach ($jeux as $jeu): ?>
                <tr class="hover:bg-white/5 transition">
                  <td class="px-6 py-5 text-purple-300 font-medium">#<?= $jeu->getIdJeu() ?></td>
                  <td class="px-6 py-5">
                    <?php if ($jeu->getSrcImg()): ?>
                      <img src="../../resources/<?= htmlspecialchars($jeu->getSrcImg()) ?>" 
                           class="w-16 h-16 object-cover rounded-lg ring-2 ring-purple-500" 
                           alt="<?= htmlspecialchars($jeu->getTitre()) ?>">
                    <?php else: ?>
                      <div class="w-16 h-16 bg-gray-700 rounded-lg flex items-center justify-center text-gray-500">
                        <i class="fas fa-image text-2xl"></i>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-5 font-semibold text-white"><?= htmlspecialchars($jeu->getTitre()) ?></td>
                  <td class="px-6 py-5 text-cyan-300"><?= htmlspecialchars($jeu->nom_categorie ?? '—') ?></td>
                  <td class="px-6 py-5 text-yellow-400 font-bold"><?= number_format($jeu->getPrix(), 2) ?> TND</td>
                  <td class="px-6 py-5 text-center space-x-6">
                    <a href="modifier_jeux.php?id=<?= $jeu->getIdJeu() ?>" class="text-cyan-400 hover:text-cyan-200 font-medium">Modifier</a>
                    <a href="?delete=<?= $jeu->getIdJeu() ?>" 
                       onclick="return confirm('Supprimer « <?= addslashes(htmlspecialchars($jeu->getTitre())) ?> » ?')" 
                       class="text-red-400 hover:text-red-300 font-medium">Supprimer</a>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="mt-8 text-center text-purple-300">
        Total : <span class="font-bold text-2xl text-cyan-400"><?= count($jeux) ?></span> jeu(x)
      </div>
    </div>
  </main>

</body>
</html>