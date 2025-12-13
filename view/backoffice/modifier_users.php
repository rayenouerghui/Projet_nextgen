<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../frontoffice/connexion.php');
    exit;
}

require_once '../../controller/userController.php';
$controller = new userController();

if (!isset($_GET['id'])) {
    header('Location: admin_users.php');
    exit;
}

$user = $controller->getUserById((int)$_GET['id']);
if (!$user) die('Utilisateur non trouvé');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user->setPrenom(trim($_POST['prenom']));
    $user->setNom(trim($_POST['nom']));
    $user->setEmail(trim($_POST['email']));
    $user->setTelephone(trim($_POST['telephone']));
    $user->setRole($_POST['role']);
    $user->setStatut($_POST['statut']);

    $credits = (float)str_replace(',', '.', $_POST['credits']);
    if ($credits < 0) {
        $error = "Les crédits ne peuvent pas être négatifs.";
    } else {
        $user->setCredits($credits);
    }

    if (!empty($_POST['password'])) {
        if (strlen($_POST['password']) < 8) {
            $error = "Le mot de passe doit contenir au moins 8 caractères.";
        } else {
            $user->setMdp(password_hash($_POST['password'], PASSWORD_DEFAULT));
        }
    }

    if (!$error) {
        $controller->updateUser($user);
        header('Location: admin_users.php?success=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier Utilisateur – NextGen Admin</title>
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
    .error { @apply text-red-400 text-sm mt-2 block font-medium; }
  </style>
</head>
<body class="h-full" x-data="{ sidebarOpen: true }">

  <!-- SIDEBAR (same as others) -->
  <aside :class="sidebarOpen ? 'sidebar-open' : 'sidebar-closed'" class="glass fixed inset-y-0 left-0 z-40 flex flex-col transition-all duration-300 overflow-hidden">
    <div class="p-5 text-center border-b border-purple-500/30">
      <h1 class="text-3xl font-black bg-gradient-to-r from-cyan-400 to-purple-600 bg-clip-text text-transparent">NEXTGEN</h1>
      <p x-show="sidebarOpen" class="text-purple-300 text-sm font-bold mt-1">ADMIN PANEL</p>
    </div>
    <nav class="flex-1 px-3 py-6 space-y-2">
      <?php $current = basename($_SERVER['PHP_SELF']); ?>
      <a href="admin_jeux.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= str_contains($current,'jeux')?'bg-gradient-to-r from-purple-600 to-pink-600 text-white':'hover:bg-white/10 text-purple-300' ?>">
        <i class="fas fa-gamepad w-8 text-xl"></i><span x-show="sidebarOpen">Jeux</span>
      </a>
      <a href="admin_users.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold">
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
        MODIFIER UTILISATEUR #<?= $user->getId() ?>
      </h1>

      <?php if ($error): ?>
        <div class="glass p-6 rounded-2xl border border-red-500/50 mb-8 text-red-300 text-center font-bold">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="glass rounded-2xl p-10">
        <form id="modifyForm" method="POST" class="space-y-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
              <label class="block text-purple-300 font-bold mb-2">Prénom</label>
              <input type="text" name="prenom" value="<?= htmlspecialchars($user->getPrenom()) ?>" class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg">
              <small class="error" id="prenomError"></small>
            </div>
            <div>
              <label class="block text-purple-300 font-bold mb-2">Nom</label>
              <input type="text" name="nom" value="<?= htmlspecialchars($user->getNom()) ?>" class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg">
              <small class="error" id="nomError"></small>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
              <label class="block text-purple-300 font-bold mb-2">Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($user->getEmail()) ?>" class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg">
              <small class="error" id="emailError"></small>
            </div>
            <div>
              <label class="block text-purple-300 font-bold mb-2">Téléphone</label>
              <input type="text" name="telephone" value="<?= htmlspecialchars($user->getTelephone()) ?>" maxlength="8" class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg">
              <small class="error" id="telephoneError"></small>
            </div>
          </div>

          <div>
            <label class="block text-purple-300 font-bold mb-2">Crédits (TND)</label>
            <input type="number" step="0.01" name="credits" value="<?= number_format($user->getCredits(), 2, '.', '') ?>" class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg">
            <small class="error" id="creditsError"></small>
          </div>

          <div>
            <label class="block text-purple-300 font-bold mb-2">Nouveau mot de passe <span class="text-gray-400 text-sm">(laisser vide pour garder l'ancien)</span></label>
            <input type="password" name="password" class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg">
            <small class="error" id="passwordError"></small>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
              <label class="block text-purple-300 font-bold mb-2">Rôle</label>
              <select name="role" class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 text-white text-lg">
                <option value="user" <?= $user->getRole() === 'user' ? 'selected' : '' ?>>Utilisateur normal</option>
                <option value="admin" <?= $user->getRole() === 'admin' ? 'selected' : '' ?>>Administrateur</option>
              </select>
            </div>
            <div>
              <label class="block text-purple-300 font-bold mb-2">Statut</label>
              <select name="statut" class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 text-white text-lg">
                <option value="actif" <?= $user->getStatut() === 'actif' ? 'selected' : '' ?>>Actif</option>
                <option value="suspendu" <?= $user->getStatut() === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                <option value="banni" <?= $user->getStatut() === 'banni' ? 'selected' : '' ?>>Banni</option>
              </select>
            </div>
          </div>

          <div class="flex justify-center gap-6 pt-8">
            <button type="submit" class="btn-neon text-white font-bold py-4 px-12 rounded-xl text-xl hover:scale-105 transition">Sauvegarder</button>
            <a href="admin_users.php" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-4 px-12 rounded-xl text-xl transition">Annuler</a>
          </div>
        </form>
      </div>
    </div>
  </main>
<script>
  document.getElementById('modifyForm').addEventListener('submit', function(e) {
    let hasError = false;
    // Reset tous les messages d'erreur
    document.querySelectorAll('.error').forEach(el => el.textContent = '');

    // Prénom
    const prenom = document.querySelector('[name="prenom"]').value.trim();
    if (!prenom) {
      document.getElementById('prenomError').textContent = 'Le prénom est obligatoire';
      hasError = true;
    }

    // Nom
    const nom = document.querySelector('[name="nom"]').value.trim();
    if (!nom) {
      document.getElementById('nomError').textContent = 'Le nom est obligatoire';
      hasError = true;
    }

    // Email
    const email = document.querySelector('[name="email"]').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email || !emailRegex.test(email)) {
      document.getElementById('emailError').textContent = 'Email invalide';
      hasError = true;
    }

    // Téléphone — EXACTEMENT comme dans ajouter_users.php
    const tel = document.querySelector('[name="telephone"]').value.trim();
    if (!/^[2459]\d{7}$/.test(tel)) {
      document.getElementById('telephoneError').textContent = 'Téléphone invalide (8 chiffres, commence par 2/4/5/9)';
      hasError = true;
    }

    // Crédits
    const credits = parseFloat(document.querySelector('[name="credits"]').value.replace(',', '.'));
    if (isNaN(credits) || credits < 0) {
      document.getElementById('creditsError').textContent = 'Les crédits ne peuvent pas être négatifs';
      hasError = true;
    }

    // Mot de passe (seulement s'il est rempli)
    const pwd = document.querySelector('[name="password"]').value;
    if (pwd !== '' && pwd.length < 8) {
      document.getElementById('passwordError').textContent = 'Minimum 8 caractères';
      hasError = true;
    }

    if (hasError) {
      e.preventDefault();
    }
  });
</script>
  <!-- Disable HTML5 validation (comme partout ailleurs) -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('form').forEach(form => {
        form.querySelectorAll('input, select, textarea').forEach(field => {
          field.removeAttribute('required');
          field.removeAttribute('pattern');
          field.removeAttribute('min');
          field.removeAttribute('max');
          field.removeAttribute('step');
        });
        form.setAttribute('novalidate', 'novalidate');
      });
    });
  </script>

</body>
</html>