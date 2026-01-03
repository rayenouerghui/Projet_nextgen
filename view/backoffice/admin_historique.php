<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../frontoffice/connexion.php');
    exit;
}

require_once '../../config/config.php';
require_once '../../controller/HistoriqueController.php';

$pdo = Config::getConnexion();
$histCtrl = new HistoriqueController();

// === SUPPRESSION ===
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM historique WHERE id_historique = ?")->execute([(int)$_GET['delete']]);
    header('Location: admin_historique.php?success=1');
    exit;
}

// === AJOUT ===
$error_add = null;
$addFormData = [];
if (isset($_GET['action']) && $_GET['action'] === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = (int)$_POST['id_user'];
    $type_action = $_POST['type_action'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $date_action = $_POST['date_action'] ?? '';

    if (empty($id_user) || empty($type_action) || empty($description) || empty($date_action)) {
        $error_add = "Tous les champs sont obligatoires.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $check->execute([$id_user]);
        if (!$check->fetch()) {
            $error_add = "Utilisateur avec l'ID $id_user inexistant.";
        } else {
            $h = new Historique($id_user, $type_action, $description, null, $date_action);
            $histCtrl->addHistorique($h);
            header('Location: admin_historique.php?success=1');
            exit;
        }
    }
    $addFormData = ['id_user' => $id_user, 'type_action' => $type_action, 'description' => $description, 'date_action' => $date_action];
}

// === MODIFICATION ===
if (isset($_POST['update_historique'])) {
    $id   = (int)$_POST['id_historique'];
    $type = $_POST['type_action'] ?? '';
    $desc = trim($_POST['description'] ?? '');
    $date = $_POST['date_action'] ?? '';

    if ($type && $desc && $date) {
        $sql = "UPDATE historique SET type_action=?, description=?, date_action=? WHERE id_historique=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$type, $desc, $date, $id]);
    }
    header('Location: admin_historique.php?success=1');
    exit;
}

// === CHARGEMENT MODIF ===
$editEntry = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT h.*, u.prenom, u.nom FROM historique h LEFT JOIN users u ON h.id_user = u.id WHERE h.id_historique = ?");
    $stmt->execute([$edit_id]);
    $editEntry = $stmt->fetch(PDO::FETCH_ASSOC);
}

// === LISTE + RECHERCHE ===
$search = $_GET['search'] ?? '';
if ($search !== '') {
    $search = '%' . $search . '%';
    $stmt = $pdo->prepare("SELECT h.*, u.prenom, u.nom FROM historique h LEFT JOIN users u ON h.id_user = u.id WHERE u.prenom LIKE ? OR u.nom LIKE ? OR h.description LIKE ? OR h.type_action LIKE ? ORDER BY h.date_action DESC");
    $stmt->execute([$search, $search, $search, $search]);
    $allHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $allHistory = $pdo->query("SELECT h.*, u.prenom, u.nom FROM historique h LEFT JOIN users u ON h.id_user = u.id ORDER BY h.date_action DESC")->fetchAll(PDO::FETCH_ASSOC);
}

$success = isset($_GET['success']);
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Gestion Historique – NextGen Admin</title>
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
   .error-msg { display: block; margin-top: 0.5rem; }
 </style>
</head>
<body class="h-full" x-data="{ sidebarOpen: true }">

<!-- SIDEBAR (inchangé) -->
<aside :class="sidebarOpen ? 'sidebar-open' : 'sidebar-closed'" class="glass fixed inset-y-0 left-0 z-40 flex flex-col transition-all duration-300 overflow-hidden">
  <div class="p-5 text-center border-b border-purple-500/30">
    <h1 class="text-3xl font-black bg-gradient-to-r from-cyan-400 to-purple-600 bg-clip-text text-transparent">NEXTGEN</h1>
    <p x-show="sidebarOpen" class="text-purple-300 text-sm font-bold mt-1">ADMIN PANEL</p>
  </div>
  <nav class="flex-1 px-3 py-6 space-y-2">
    <?php $current = basename($_SERVER['PHP_SELF']); ?>
    <a href="admin_jeux.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= $current==='admin_jeux.php'?'bg-gradient-to-r from-purple-600 to-pink-600 text-white':'hover:bg-white/10 text-purple-300' ?>">
      <i class="fas fa-gamepad w-8 text-xl"></i><span x-show="sidebarOpen">Jeux</span>
    </a>
    <a href="admin_users.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= $current==='admin_users.php'?'bg-gradient-to-r from-purple-600 to-pink-600 text-white':'hover:bg-white/10 text-purple-300' ?>">
      <i class="fas fa-users w-8 text-xl"></i><span x-show="sidebarOpen">Utilisateurs</span>
    </a>
    <a href="admin_categories.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= $current==='admin_categories.php'?'bg-gradient-to-r from-purple-600 to-pink-600 text-white':'hover:bg-white/10 text-purple-300' ?>">
      <i class="fas fa-tags w-8 text-xl"></i><span x-show="sidebarOpen">Catégories</span>
    </a>
    <a href="admin_historique.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold">
      <i class="fas fa-history w-8 text-xl"></i><span x-show="sidebarOpen">Historique</span>
    </a>
<a href="admin_livraisons.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition <?= $current==='admin_livraisons.php'?'bg-gradient-to-r from-orange-600 to-amber-600 text-white':'hover:bg-white/10 text-orange-300' ?>">
        <i class="fas fa-truck w-8 text-xl"></i><span x-show="sidebarOpen">Livraisons</span>
    </a>
  </nav>
  <div class="p-4 border-t border-purple-500/30">
    <button @click="sidebarOpen = !sidebarOpen" class="w-full py-4 hover:bg-white/5 rounded-lg">
      <i class="fas fa-chevron-left mx-auto text-xl text-purple-300" :class="{'rotate-180': !sidebarOpen}"></i>
    </button>
    <a href="../frontoffice/index.php" class="flex items-center justify-center gap-3 px-4 py-4 mt-3 rounded-xl bg-gradient-to-r from-cyan-500 to-teal-600 text-white font-bold hover:scale-105 transition">
      <i class="fas fa-home"></i><span x-show="sidebarOpen">Retour accueil</span>
    </a>
  </div>
</aside>

<main class="min-h-screen transition-all duration-300" :class="sidebarOpen ? 'lg:ml-64 ml-20' : 'ml-20'">
  <div class="max-w-7xl mx-auto px-6 py-10">
    <h1 class="text-center text-5xl font-black bg-gradient-to-r from-cyan-400 via-purple-500 to-pink-500 bg-clip-text text-transparent neon-text mb-12">
      GESTION DE L'HISTORIQUE
    </h1>

    <div class="flex flex-col sm:flex-row gap-6 mb-10 glass p-6 rounded-2xl">
      <form method="GET" class="flex-1">
        <div class="relative">
          <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-purple-400"></i>
          <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher..." class="w-full pl-12 pr-4 py-3 bg-black/40 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>
      </form>
      <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="btn-neon text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition">
        + Ajouter une entrée
      </button>
    </div>

    <?php if ($success): ?>
    <div class="text-center p-4 mb-6 bg-green-900/70 border border-green-500 rounded-xl text-green-300 font-bold">
      Action effectuée avec succès !
    </div>
    <?php endif; ?>

    <div class="glass rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gradient-to-r from-purple-800 to-pink-800">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">ID</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Utilisateur</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Type</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Description</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Date</th>
              <th class="px-6 py-4 text-center text-xs font-bold text-cyan-300 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-purple-500/20">
            <?php foreach ($allHistory as $h): ?>
            <tr class="hover:bg-white/5 transition">
              <td class="px-6 py-5 text-purple-300 font-medium">#<?= $h['id_historique'] ?></td>
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center text-lg font-bold text-cyan-400">
                    <?= strtoupper(substr($h['prenom']??'U', 0, 1)) ?>
                  </div>
                  <div>
                    <div class="font-semibold text-white"><?= htmlspecialchars($h['prenom']??'Inconnu') ?> <?= htmlspecialchars($h['nom']??'') ?></div>
                    <div class="text-purple-300 text-xs">ID: <?= $h['id_user'] ?></div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-5">
                <span class="px-3 py-1 rounded-full text-xs font-bold text-white <?= $h['type_action']==='login'?'bg-gray-600':($h['type_action']==='purchase'?'bg-green-600':'bg-purple-600') ?>">
                  <?= ucfirst($h['type_action'] ?: 'Autre') ?>
                </span>
              </td>
              <td class="px-6 py-5 text-gray-300 text-sm"><?= htmlspecialchars($h['description']) ?></td>
              <td class="px-6 py-5 text-yellow-400 text-sm"><?= date('d/m/Y H:i', strtotime($h['date_action'])) ?></td>
              <td class="px-6 py-5 text-center space-x-6">
                <a href="?edit=<?= $h['id_historique'] ?>" class="text-cyan-400 hover:text-cyan-200 font-medium">Modifier</a>
                <a href="?delete=<?= $h['id_historique'] ?>" onclick="return confirm('Supprimer ?')" class="text-red-400 hover:text-red-300 font-medium">Supprimer</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (empty($allHistory)): ?>
          <div class="text-center py-12 text-gray-400 text-lg">Aucune entrée trouvée.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<!-- ADD MODAL -->
<div id="addModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
  <div class="glass rounded-2xl p-8 max-w-md w-full mx-4">
    <h2 class="text-3xl font-bold text-center text-teal-400 neon-text mb-6">Nouvelle entrée</h2>
    
    <?php if ($error_add): ?>
      <div class="mb-4 p-4 bg-red-900/70 border border-red-500 rounded-xl text-red-300 text-center font-semibold">
        <?= htmlspecialchars($error_add) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="?action=add" id="addForm">
      <div class="mb-5">
        <input type="number" name="id_user" value="<?= $addFormData['id_user']??'' ?>" 
               placeholder="ID utilisateur" class="w-full px-5 py-3 bg-black/40 border border-purple-500 rounded-xl text-white">
        <small class="error-msg text-red-400 text-sm block mt-1"></small>
      </div>

      <div class="mb-5">
        <select name="type_action" class="w-full px-5 py-3 bg-black/40 border border-purple-500 rounded-xl text-white">
          <option value="">-- Choisir le type --</option>
          <option value="login"    <?= ($addFormData['type_action']??'')==='login'?'selected':'' ?>>Connexion</option>
          <option value="purchase" <?= ($addFormData['type_action']??'')==='purchase'?'selected':'' ?>>Achat</option>
          <option value="other"    <?= ($addFormData['type_action']??'')==='other'?'selected':'' ?>>Autre</option>
        </select>
        <small class="error-msg text-red-400 text-sm block mt-1"></small>
      </div>

      <div class="mb-5">
        <input type="text" name="description" value="<?= htmlspecialchars($addFormData['description']??'') ?>" 
               placeholder="Description" class="w-full px-5 py-3 bg-black/40 border border-purple-500 rounded-xl text-white">
        <small class="error-msg text-red-400 text-sm block mt-1"></small>
      </div>

      <div class="mb-6">
        <input type="datetime-local" name="date_action" value="<?= $addFormData['date_action']??'' ?>" 
               class="w-full px-5 py-3 bg-black/40 border border-purple-500 rounded-xl text-white">
        <small class="error-msg text-red-400 text-sm block mt-1"></small>
      </div>

      <div class="flex gap-4">
        <button type="submit" class="flex-1 btn-neon text-white font-bold py-3 rounded-xl">Ajouter</button>
        <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" 
                class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 rounded-xl">Annuler</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<?php if ($editEntry): ?>
<div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50">
  <div class="glass rounded-2xl p-8 max-w-lg w-full mx-4">
    <h2 class="text-3xl font-bold text-center text-cyan-400 mb-8">Modifier l'entrée #<?= $editEntry['id_historique'] ?></h2>

    <form method="POST" id="editForm">
      <input type="hidden" name="update_historique" value="1">
      <input type="hidden" name="id_historique" value="<?= $editEntry['id_historique'] ?>">

      <div class="mb-6 text-gray-300">
        <strong>Utilisateur :</strong> #<?= $editEntry['id_user'] ?> - 
        <?= htmlspecialchars($editEntry['prenom'] ?? 'Inconnu') ?> <?= htmlspecialchars($editEntry['nom'] ?? '') ?>
      </div>

      <div class="space-y-5">
        <div>
          <select name="type_action" class="w-full px-5 py-3 bg-black/40 border border-purple-500 rounded-xl text-white">
            <option value="login"    <?= $editEntry['type_action']==='login'    ? 'selected' : '' ?>>Connexion</option>
            <option value="purchase" <?= $editEntry['type_action']==='purchase' ? 'selected' : '' ?>>Achat</option>
            <option value="other"    <?= $editEntry['type_action']==='other'      ? 'selected' : '' ?>>Autre</option>
          </select>
          <small class="error-msg text-red-400 text-sm block mt-1"></small>
        </div>

        <div>
          <input type="text" name="description" value="<?= htmlspecialchars($editEntry['description']) ?>" 
                 class="w-full px-5 py-3 bg-black/40 border border-purple-500 rounded-xl text-white">
          <small class="error-msg text-red-400 text-sm block mt-1"></small>
        </div>

        <div>
          <input type="datetime-local" name="date_action" 
                 value="<?= date('Y-m-d\TH:i', strtotime($editEntry['date_action'])) ?>" 
                 class="w-full px-5 py-3 bg-black/40 border border-purple-500 rounded-xl text-white">
          <small class="error-msg text-red-400 text-sm block mt-1"></small>
        </div>

        <div class="flex gap-4 mt-8">
          <button type="submit" class="flex-1 btn-neon py-4 rounded-xl font-bold text-xl">Enregistrer</button>
          <a href="admin_historique.php" class="flex-1 text-center bg-gray-700 hover:bg-gray-600 py-4 rounded-xl font-bold text-xl">Annuler</a>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
// DÉSACTIVE HTML5 + VALIDATION JS
document.addEventListener('DOMContentLoaded', function () {
  // Désactive la validation HTML5
  document.querySelectorAll('form').forEach(f => f.setAttribute('novalidate', 'novalidate'));

  function validateForm(form) {
    let ok = true;
    form.querySelectorAll('.error-msg').forEach(el => el.textContent = '');

    const type = form.querySelector('[name="type_action"]');
    const desc = form.querySelector('[name="description"]');
    const date = form.querySelector('[name="date_action"]');
    const idUser = form.querySelector('[name="id_user"]');

    if (type && !type.value) {
      type.nextElementSibling.textContent = 'Type obligatoire';
      ok = false;
    }
    if (desc && !desc.value.trim()) {
      desc.nextElementSibling.textContent = 'Description obligatoire';
      ok = false;
    }
    if (date && !date.value) {
      date.nextElementSibling.textContent = 'Date obligatoire';
      ok = false;
    }
    if (idUser && (!idUser.value || idUser.value <= 0)) {
      idUser.nextElementSibling.textContent = 'ID utilisateur invalide';
      ok = false;
    }

    return ok;
  }

  document.getElementById('addForm')?.addEventListener('submit', function(e) {
    if (!validateForm(this)) e.preventDefault();
  });

  document.getElementById('editForm')?.addEventListener('submit', function(e) {
    if (!validateForm(this)) e.preventDefault();
  });
});
</script>
</body>
</html>