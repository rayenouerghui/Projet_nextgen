<?php
session_start();
require_once '../../controller/ReclamationController.php';
require_once '../../controller/TraitementController.php';

$reclamationController = new ReclamationController();
$traitementController = new TraitementController();

$id = (int)($_GET['id'] ?? 0);
$reclamation = $reclamationController->readById($id);
if (isset($reclamation['error'])) {
    die("Réclamation non trouvée.");
}

$traitements = $traitementController->readByReclamationId($id);

if (isset($_POST['update_statut'])) {
    $reclamationController->updateStatut($id, $_POST['statut']);
    header("Location: traiter_reclamation.php?id=$id");
    exit;
}
if (isset($_POST['add_traitement'])) {
    require_once '../../models/Traitement.php';
    $t = new Traitement();
    $t->setIdReclamation($id)
      ->setIdUser($_SESSION['user']['id'] ?? null)
      ->setContenu(trim($_POST['contenu']))
      ->setDateReclamation(date('Y-m-d H:i:s'));
    $traitementController->create($t);
    if ($reclamation['statut'] == 'En attente') {
        $reclamationController->updateStatut($id, 'En traitement');
    }
    header("Location: traiter_reclamation.php?id=$id");
    exit;
}
if (isset($_GET['delete_traitement'])) {
    $traitementController->delete((int)$_GET['delete_traitement']);
    header("Location: traiter_reclamation.php?id=$id");
    exit;
}
if (isset($_POST['update_traitement'])) {
    require_once '../../models/Traitement.php';
    $t = new Traitement();
    $t->setIdTraitement((int)$_POST['id_traitement'])
      ->setIdReclamation($id)
      ->setIdUser($_SESSION['user']['id'] ?? null)
      ->setContenu(trim($_POST['contenu']))
      ->setDateReclamation($_POST['date_originale']);
    $traitementController->update($t);
    header("Location: traiter_reclamation.php?id=$id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Traiter Réclamation #<?= $id ?> – NextGen Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Exo+2:wght@500;700&display=swap" rel="stylesheet">
  <style>
    body { background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); font-family: 'Exo 2', sans-serif; color: #e0e7ff; }
    .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(139,92,246,0.3); box-shadow: 0 8px 32px rgba(0,0,0,0.4); }
    .neon-text { text-shadow: 0 0 30px #8b5cf6; }
    .btn-neon { background: linear-gradient(45deg, #8b5cf6, #ec4899); box-shadow: 0 0 30px rgba(139,92,246,0.6); }
    .sidebar-closed { width: 5rem !important; }
    .sidebar-open { width: 16rem; }
    .popup-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.85);
      backdrop-filter: blur(10px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }
    .popup-content {
      background: rgba(30,20,60,0.95);
      border: 2px solid #8b5cf6;
      border-radius: 3rem;
      padding: 2.5rem;
      max-width: 500px;
      width: 90%;
      box-shadow: 0 0 50px rgba(139,92,246,0.8);
      position: relative;
    }
    .btn-close {
      position: absolute;
      top: 1rem;
      right: 1.5rem;
      font-size: 1.8rem;
      color: #8b5cf6;
      cursor: pointer;
      text-shadow: 0 0 10px #8b5cf6;
    }
    .popup-btn-primary {
      background: linear-gradient(45deg, #8b5cf6, #ec4899);
      box-shadow: 0 0 25px rgba(139,92,246,0.7);
    }
    .popup-btn-secondary {
      background: linear-gradient(to right, #4b5563, #6b7280);
      box-shadow: 0 0 15px rgba(75,85,99,0.5);
    }
    .error { color: #ff6b6b; font-size: 0.875rem; margin-top: 0.5rem; display: none; }
  </style>
</head>
<body class="h-full" x-data="{ sidebarOpen: true }">

  <aside :class="sidebarOpen ? 'sidebar-open' : 'sidebar-closed'" class="glass fixed inset-y-0 left-0 z-40 flex flex-col transition-all duration-300 overflow-hidden">
    <div class="p-5 text-center border-b border-purple-500/30">
      <h1 class="text-3xl font-black bg-gradient-to-r from-cyan-400 to-purple-600 bg-clip-text text-transparent">NEXTGEN</h1>
      <p x-show="sidebarOpen" class="text-purple-300 text-sm font-bold mt-1">ADMIN PANEL</p>
    </div>
    <nav class="flex-1 px-3 py-6 space-y-2">
      <a href="admin_jeux.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition hover:bg-white/10 text-purple-300">
        <i class="fas fa-gamepad w-8 text-xl"></i><span x-show="sidebarOpen">Jeux</span>
      </a>
      <a href="admin_users.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition hover:bg-white/10 text-purple-300">
        <i class="fas fa-users w-8 text-xl"></i><span x-show="sidebarOpen">Utilisateurs</span>
      </a>
      <a href="admin_categories.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition hover:bg-white/10 text-purple-300">
        <i class="fas fa-tags w-8 text-xl"></i><span x-show="sidebarOpen">Catégories</span>
      </a>
      <a href="admin_historique.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition hover:bg-white/10 text-purple-300">
        <i class="fas fa-history w-8 text-xl"></i><span x-show="sidebarOpen">Historique</span>
      </a>
      <a href="admin_reclamations.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold">
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

  <main class="min-h-screen transition-all duration-300" :class="sidebarOpen ? 'ml-64' : 'ml-20'">
    <div class="p-8 max-w-6xl mx-auto">
      <h1 class="text-5xl font-black neon-text text-center mb-12">Traiter Réclamation #<?= $id ?></h1>
      <div class="text-center mb-8">
        <a href="admin_reclamations.php" class="inline-block bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold py-4 px-10 rounded-xl hover:shadow-2xl transition transform hover:-translate-y-1 text-xl">
            <i class="fas fa-arrow-left mr-3"></i> Retour à la liste des réclamations
        </a>
      </div>

      <div class="glass rounded-3xl p-8 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 glass p-6 rounded-2xl">
          <div>
            <strong class="text-purple-300">Utilisateur</strong>
            <p class="mt-2"><?= htmlspecialchars($reclamation['user_nom']??'') ?> <?= htmlspecialchars($reclamation['user_prenom']??'') ?><br>
            <small class="text-cyan-300"><?= htmlspecialchars($reclamation['user_email']??'') ?></small></p>
          </div>
          <div><strong class="text-purple-300">Type</strong><p class="mt-2 text-yellow-300"><?= htmlspecialchars($reclamation['type']??'—') ?></p></div>
          <div><strong class="text-purple-300">Produit</strong><p class="mt-2"><?= htmlspecialchars($reclamation['jeu_titre'] ?? $reclamation['produitConcerne'] ?? '—') ?></p></div>
          <div><strong class="text-purple-300">Date</strong><p class="mt-2"><?= date('d/m/Y H:i', strtotime($reclamation['dateReclamation'])) ?></p></div>
        </div>

        <div class="flex items-center justify-between">
          <div><strong class="text-purple-300">Statut :</strong> 
            <span class="statut-badge <?= match($reclamation['statut']??'En attente'){ 'En attente'=>'statut-en-attente','En traitement'=>'statut-en-traitement','Résolue'=>'statut-resolue','Fermée'=>'statut-fermee',default=>'statut-en-attente' } ?>">
              <?= $reclamation['statut'] ?? 'En attente' ?>
            </span>
          </div>
          <form method="POST" class="inline">
            <select name="statut" onchange="this.form.submit()" class="px-6 py-3 bg-black/40 border border-purple-500 rounded-xl text-white font-medium">
              <option value="En attente" <?= ($reclamation['statut']??'')=='En attente'?'selected':'' ?>>En attente</option>
              <option value="En traitement" <?= ($reclamation['statut']??'')=='En traitement'?'selected':'' ?>>En traitement</option>
              <option value="Résolue" <?= ($reclamation['statut']??'')=='Résolue'?'selected':'' ?>>Résolue</option>
              <option value="Fermée" <?= ($reclamation['statut']??'')=='Fermée'?'selected':'' ?>>Fermée</option>
            </select>
            <input type="hidden" name="update_statut" value="1">
          </form>
        </div>

        <div class="glass p-6 rounded-2xl">
          <strong class="text-purple-300 block mb-3">Description :</strong>
          <div class="text-white"><?= nl2br(htmlspecialchars($reclamation['description']??'')) ?></div>
        </div>

        <div>
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-3xl font-bold text-cyan-400">Traitements (<?= count($traitements) ?>)</h3>
            <div class="flex flex-col gap-4">
              <button onclick="document.getElementById('addPopup').classList.remove('hidden')" class="btn-neon px-6 py-4 rounded-xl text-lg w-full text-center">
                + Ajouter un traitement
              </button>
              <a href="admin_reclamations.php" class="bg-gradient-to-r from-gray-700 to-gray-600 hover:from-gray-600 hover:to-gray-500 text-white font-bold px-6 py-4 rounded-xl text-lg w-full text-center transition transform hover:-translate-y-1">
                <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
              </a>
            </div>
          </div>

          <?php foreach ($traitements as $t): ?>
            <div class="glass p-6 rounded-2xl mb-6 border-l-4 border-purple-500">
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="font-bold text-purple-300"><?= htmlspecialchars($t['auteur_nom']??'Admin') ?> <?= htmlspecialchars($t['auteur_prenom']??'') ?></div>
                  <div class="text-sm text-cyan-300 mb-3"><?= htmlspecialchars($t['auteur_email']??'') ?> • <?= date('d/m/Y H:i', strtotime($t['dateReclamation'])) ?></div>
                  <div class="text-white"><?= nl2br(htmlspecialchars($t['contenu']??'')) ?></div>
                </div>
                <div class="flex items-center gap-6 ml-8">
                  <button onclick="document.getElementById('editPopup<?= $t['idTraitement'] ?>').classList.remove('hidden')" class="text-cyan-400 hover:text-cyan-200 text-2xl">
                    <i class="fas fa-edit"></i>
                  </button>
                  <a href="?id=<?= $id ?>&delete_traitement=<?= $t['idTraitement'] ?>" onclick="return confirm('Supprimer ce traitement ?')" class="text-red-400 hover:text-red-200 text-2xl">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </div>
            </div>

            <div id="editPopup<?= $t['idTraitement'] ?>" class="popup-overlay hidden">
              <div class="popup-content">
                <span onclick="this.closest('.popup-overlay').classList.add('hidden')" class="btn-close">&times;</span>
                <h3 class="text-3xl font-bold text-yellow-300 mb-6 text-center neon-text">Modifier le traitement</h3>
                <form method="POST" novalidate>
                  <input type="hidden" name="id_traitement" value="<?= $t['idTraitement'] ?>">
                  <input type="hidden" name="date_originale" value="<?= $t['dateReclamation'] ?>">
                  <textarea name="contenu" rows="8" class="w-full p-4 bg-black/40 border border-purple-500 rounded-2xl text-white mb-4"><?= htmlspecialchars($t['contenu']) ?></textarea>
                  <div class="error" id="error-edit-<?= $t['idTraitement'] ?>">Ce champ est obligatoire.</div>
                  <div class="flex gap-6 mt-6">
                    <button type="submit" name="update_traitement" class="popup-btn-primary flex-1 py-4 rounded-full text-xl font-bold text-white">Modifier</button>
                    <button type="button" onclick="this.closest('.popup-overlay').classList.add('hidden')" class="popup-btn-secondary flex-1 py-4 rounded-full text-xl font-bold text-white">Annuler</button>
                  </div>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </main>

  <div id="addPopup" class="popup-overlay hidden">
    <div class="popup-content">
      <span onclick="this.closest('.popup-overlay').classList.add('hidden')" class="btn-close">&times;</span>
      <h3 class="text-3xl font-bold text-cyan-400 mb-6 text-center neon-text">Nouvelle réponse</h3>
      <form method="POST" novalidate>
        <textarea name="contenu" rows="8" class="w-full p-4 bg-black/40 border border-purple-500 rounded-2xl text-white mb-4" placeholder="Votre réponse..."></textarea>
        <div class="error" id="error-add">Ce champ est obligatoire.</div>
        <div class="flex gap-6 mt-6">
          <button type="submit" name="add_traitement" class="popup-btn-primary flex-1 py-4 rounded-full text-xl font-bold text-white">Envoyer</button>
          <button type="button" onclick="this.closest('.popup-overlay').classList.add('hidden')" class="popup-btn-secondary flex-1 py-4 rounded-full text-xl font-bold text-white">Annuler</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function validateForm(textarea, errorId) {
      const value = textarea.value.trim();
      const error = document.getElementById(errorId);
      if (value === '') {
        error.style.display = 'block';
        return false;
      } else {
        error.style.display = 'none';
        return true;
      }
    }

    document.querySelector('#addPopup form').addEventListener('submit', function(e) {
      const textarea = this.querySelector('textarea[name="contenu"]');
      if (!validateForm(textarea, 'error-add')) {
        e.preventDefault();
      }
    });

    <?php foreach ($traitements as $t): ?>
    document.querySelector('#editPopup<?= $t['idTraitement'] ?> form').addEventListener('submit', function(e) {
      const textarea = this.querySelector('textarea[name="contenu"]');
      if (!validateForm(textarea, 'error-edit-<?= $t['idTraitement'] ?>')) {
        e.preventDefault();
      }
    });
    <?php endforeach; ?>
  </script>

</body>
</html>