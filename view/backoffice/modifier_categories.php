<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../frontoffice/connexion.php');
    exit;
}

require_once '../../controller/CategorieController.php';
$controller = new CategorieController();

$id = (int)($_GET['id'] ?? 0);
$categorie = $controller->getCategorie($id);
if (!$categorie) {
    header('Location: admin_categories.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if ($nom && $desc) {
        $categorie->setNomCategorie($nom);
        $categorie->setDescription($desc);
        $controller->modifierCategorie($categorie);
        header('Location: admin_categories.php?success=1');
        exit;
    } else {
        $error = "Le nom et la description sont obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier Catégorie – NextGen Admin</title>
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
  </style>
</head>
<body class="h-full" x-data="{ sidebarOpen: true }">

  <!-- SIDEBAR -->
  <aside :class="sidebarOpen ? 'sidebar-open' : 'sidebar-closed'" 
         class="glass fixed inset-y-0 left-0 z-40 flex flex-col transition-all duration-300 overflow-hidden">
    <div class="p-5 text-center border-b border-purple-500/30">
      <h1 class="text-3xl font-black bg-gradient-to-r from-cyan-400 to-purple-600 bg-clip-text text-transparent">NEXTGEN</h1>
      <p x-show="sidebarOpen" class="text-purple-300 text-sm font-bold mt-1">ADMIN PANEL</p>
    </div>
    <nav class="flex-1 px-3 py-6 space-y-2">
      <?php $current = basename($_SERVER['PHP_SELF']); ?>
      <a href="admin_jeux.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= str_contains($current,'jeux')?'bg-gradient-to-r from-purple-600 to-pink-600 text-white':'hover:bg-white/10 text-purple-300' ?>">
        <i class="fas fa-gamepad w-8 text-xl"></i><span x-show="sidebarOpen">Jeux</span>
      </a>
      <a href="admin_users.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= str_contains($current,'users')?'bg-gradient-to-r from-purple-600 to-pink-600 text-white':'hover:bg-white/10 text-purple-300' ?>">
        <i class="fas fa-users w-8 text-xl"></i><span x-show="sidebarOpen">Utilisateurs</span>
      </a>
      <a href="admin_categories.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold">
        <i class="fas fa-tags w-8 text-xl"></i><span x-show="sidebarOpen">Catégories</span>
      </a>
      <a href="admin_historique.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= $current==='admin_historique.php'?'bg-gradient-to-r from-purple-600 to-pink-600 text-white':'hover:bg-white/10 text-purple-300' ?>">
        <i class="fas fa-history w-8 text-xl"></i><span x-show="sidebarOpen">Historique</span>
      </a>
      <a href="admin_reclamations.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition hover:bg-white/10 text-purple-300">
        <i class="fas fa-exclamation-triangle w-8 text-xl"></i><span x-show="sidebarOpen">Réclamations</span>
      </a>
    </nav>
    <div class="p-4 border-t border-purple-500/30">
      <button @click="sidebarOpen = !sidebarOpen" class="w-full py-4 hover:bg-white/5 rounded-lg"><i class="fas fa-chevron-left mx-auto text-xl text-purple-300" :class="{'rotate-180': !sidebarOpen}"></i></button>
      <a href="../frontoffice/index.php" class="flex items-center justify-center gap-3 px-4 py-4 mt-3 rounded-xl bg-gradient-to-r from-cyan-500 to-teal-600 text-white font-bold hover:scale-105 transition">
        <i class="fas fa-home"></i><span x-show="sidebarOpen">Retour accueil</span>
      </a>
    </div>
  </aside>

  <main class="min-h-screen transition-all duration-300" :class="sidebarOpen ? 'lg:ml-64 ml-20' : 'ml-20'">
    <div class="max-w-3xl mx-auto px-6 py-10">
      <h1 class="text-center text-5xl font-black bg-gradient-to-r from-cyan-400 via-purple-500 to-pink-500 bg-clip-text text-transparent neon-text mb-12">
        MODIFIER CATÉGORIE #<?= $categorie->getIdCategorie() ?>
      </h1>

      <?php if ($error): ?>
        <div class="glass p-6 rounded-2xl border border-red-500/50 mb-8 text-red-300 text-center font-bold">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="glass rounded-2xl p-10">
        <form method="POST" class="space-y-8">
          <div>
            <label class="block text-purple-300 font-bold mb-2">Nom de la catégorie</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($categorie->getNomCategorie()) ?>" required class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg">
          </div>
          <div>
            <label class="block text-purple-300 font-bold mb-2">Description</label>
            <textarea name="description" rows="6" required class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg"><?= htmlspecialchars($categorie->getDescription() ?? '') ?></textarea>
          </div>
          <div class="flex justify-center gap-6 pt-6">
            <button type="submit" class="btn-neon text-white font-bold py-4 px-12 rounded-xl text-xl hover:scale-105 transition">Sauvegarder</button>
            <a href="admin_categories.php" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-4 px-12 rounded-xl text-xl transition">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
  <script>
// Disable ALL HTML5 form validation (no red popup, no browser validation)
document.addEventListener('DOMContentLoaded', function () {
    // Remove "required", "pattern", etc. from all inputs/selects/textareas inside forms
    document.querySelectorAll('form').forEach(form => {
        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.removeAttribute('required');
            field.removeAttribute('pattern');
            field.removeAttribute('min');
            field.removeAttribute('max');
            field.removeAttribute('step');
            // Also disable browser's built-in validation
            field.setAttribute('novalidate', 'novalidate');
        });
        // Final safety: force the form to never use browser validation
        form.setAttribute('novalidate', 'novalidate');
    });
});
</script>
</body>
</html>