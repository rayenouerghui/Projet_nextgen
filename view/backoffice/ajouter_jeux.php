<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../frontoffice/connexion.php');
    exit;
}

require_once '../../controller/jeuController.php';
require_once '../../controller/CategorieController.php';

$jeuController = new JeuController();
$categorieController = new CategorieController();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $prix = $_POST['prix'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $id_categorie = (int)($_POST['id_categorie'] ?? 0);
    $src_img = '';
    $video_src = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $allowed = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $file['size'] <= 5*1024*1024) {
            $filename = 'jeu_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], '../../resources/' . $filename)) {
                $src_img = $filename;
            }
        }
    }

    if (isset($_FILES['video_src']) && $_FILES['video_src']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['video_src'];
        if (pathinfo($file['name'], PATHINFO_EXTENSION) === 'mp4' && $file['size'] <= 100*1024*1024) {
            $filename = 'video_' . time() . '_' . uniqid() . '.mp4';
            move_uploaded_file($file['tmp_name'], '../../resources/' . $filename);
            $video_src = $filename;
        }
    }

    if ($titre && is_numeric($prix) && $prix > 0 && $description && $id_categorie && $src_img) {
        $jeu = new Jeu($titre, (float)$prix, $src_img, $id_categorie, null, $description);
        $jeu->setVideoSrc($video_src ?: null);
        $jeuController->ajouterJeu($jeu);
        header('Location: admin_jeux.php?success=1');
        exit;
    } else {
        $error = "Tous les champs obligatoires doivent être remplis correctement.";
    }
}

$categories = $categorieController->listeCategories();
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ajouter un jeu – NextGen Admin</title>
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

  <aside :class="sidebarOpen ? 'sidebar-open' : 'sidebar-closed'" class="glass fixed inset-y-0 left-0 z-40 flex flex-col transition-all duration-300 overflow-hidden">
    <div class="p-5 text-center border-b border-purple-500/30">
      <h1 class="text-3xl font-black bg-gradient-to-r from-cyan-400 to-purple-600 bg-clip-text text-transparent">NEXTGEN</h1>
      <p x-show="sidebarOpen" class="text-purple-300 text-sm font-bold mt-1">ADMIN PANEL</p>
    </div>
    <nav class="flex-1 px-3 py-6 space-y-2">
      <?php $current = basename($_SERVER['PHP_SELF']); ?>
      <a href="admin_jeux.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold">
        <i class="fas fa-gamepad w-8 text-xl"></i><span x-show="sidebarOpen">Jeux</span>
      </a>
      <a href="admin_users.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= str_contains($current,'users')?'bg-gradient-to-r from-purple-600 to-pink-600 text-white':'hover:bg-white/10 text-purple-300' ?>">
        <i class="fas fa-users w-8 text-xl"></i><span x-show="sidebarOpen">Utilisateurs</span>
      </a>
      <a href="admin_categories.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= str_contains($current,'categor')?'bg-gradient-to-r from-purple-600 to-pink-600 text-white':'hover:bg-white/10 text-purple-300' ?>">
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
    <div class="max-w-5xl mx-auto px-6 py-10">
      <h1 class="text-center text-5xl font-black bg-gradient-to-r from-cyan-400 via-purple-500 to-pink-500 bg-clip-text text-transparent neon-text mb-12">
        AJOUTER UN JEU
      </h1>

      <?php if ($error): ?>
        <div class="glass p-6 rounded-2xl border border-red-500/50 mb-8 text-red-300 text-center font-bold">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="glass rounded-2xl p-10">
        <form method="POST" enctype="multipart/form-data" class="space-y-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
              <label class="block text-purple-300 font-bold mb-2">Titre du jeu</label>
              <input type="text" name="titre" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg">
            </div>
            <div>
              <label class="block text-purple-300 font-bold mb-2">Prix (TND)</label>
              <input type="number" step="0.01" name="prix" value="<?= htmlspecialchars($_POST['prix'] ?? '') ?>" required class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg">
            </div>
          </div>

          <div>
            <label class="block text-purple-300 font-bold mb-2">Description</label>
            <textarea name="description" rows="6" required class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
              <label class="block text-purple-300 font-bold mb-2">Image principale (obligatoire)</label>
              <input type="file" name="image" accept="image/*" required class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 text-white file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:bg-gradient-to-r file:from-purple-600 file:to-pink-600 file:text-white">
            </div>
            <div>
              <label class="block text-purple-300 font-bold mb-2">Vidéo hover (MP4 - optionnel)</label>
              <input type="file" name="video_src" accept="video/mp4" class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 text-white file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:bg-gradient-to-r file:from-purple-600 file:to-pink-600 file:text-white">
            </div>
          </div>

          <div>
            <label class="block text-purple-300 font-bold mb-2">Catégorie</label>
            <select name="id_categorie" required class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 text-white text-lg">
              <option value="">Choisir...</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat->getIdCategorie() ?>"><?= htmlspecialchars($cat->getNomCategorie()) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="flex justify-center gap-6 pt-8">
            <button type="submit" class="btn-neon text-white font-bold py-4 px-12 rounded-xl text-xl hover:scale-105 transition">Ajouter le jeu</button>
            <a href="admin_jeux.php" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-4 px-12 rounded-xl text-xl transition">Annuler</a>
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