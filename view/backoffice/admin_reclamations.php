<?php
session_start();
require_once '../../controller/ReclamationController.php';
require_once '../../controller/TraitementController.php';

$reclamationController = new ReclamationController();
$traitementController = new TraitementController();

if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $result = $reclamationController->delete($id);
    header('Location: admin_reclamations.php?' . ($result['success'] ? 'success=delete' : 'error=delete'));
    exit;
}

$reclamations = $reclamationController->readAll();
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des Réclamations – NextGen Admin</title>
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
    .statut-badge { padding: 0.5rem 1rem; border-radius: 9999px; font-weight: 600; font-size: 0.875rem; }
    .statut-en-attente { background: #fef3c7; color: #92400e; }
    .statut-en-traitement { background: #dbeafe; color: #1e40af; }
    .statut-resolue { background: #d1fae5; color: #065f46; }
    .statut-fermee { background: #e5e7eb; color: #374151; }
    .traitement-count { background: #4f46e5; color: white; padding: 0.4rem 0.8rem; border-radius: 9999px; font-size: 0.8rem; font-weight: bold; }
    table { font-size: 0.875rem; }
    th, td { padding: 0.75rem 0.5rem !important; }
    .max-w-[150px] { max-width: 150px; }
    .max-w-[120px] { max-width: 120px; }
    .max-w-[200px] { max-width: 200px; }
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
      <a href="admin_livraisons.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg transition hover:bg-white/10 text-orange-300">
        <i class="fas fa-truck w-8 text-xl"></i><span x-show="sidebarOpen">Livraisons</span>
      </a>
    </nav>
    <div class="p-4 border-t border-purple-500/30">
      <button @click="sidebarOpen = !sidebarOpen" class="w-full py-4 hover:bg-white/5 rounded-lg"><i class="fas fa-chevron-left mx-auto text-xl text-purple-300" :class="{'rotate-180': !sidebarOpen}"></i></button>
      <a href="../frontoffice/index.php" class="flex items-center justify-center gap-3 px-4 py-4 mt-3 rounded-xl bg-gradient-to-r from-cyan-500 to-teal-600 text-white font-bold hover:scale-105 transition">
        <i class="fas fa-home"></i><span x-show="sidebarOpen">Retour accueil</span>
      </a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="min-h-screen transition-all duration-300" :class="sidebarOpen ? 'ml-64' : 'ml-20'">
    <div class="p-8 max-w-7xl mx-auto">
      <h1 class="text-5xl font-black neon-text text-center mb-12">Gestion des Réclamations</h1>

      <?php if (isset($_GET['success'])): ?>
        <div class="glass p-6 rounded-2xl mb-8 text-center text-green-300 font-bold text-xl">Opération réussie !</div>
      <?php endif; ?>

      <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gradient-to-r from-purple-800 to-pink-800">
              <tr>
                <th>ID</th><th>Utilisateur</th><th>Type</th><th>Produit</th><th>Description</th><th>Date</th><th>Traitements</th><th>Statut</th><th>Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-purple-500/20 text-sm">
              <?php foreach ($reclamations as $r): 
                $count = @count($traitementController->readByReclamationId($r['idReclamation'])) ?: 0;
                $s = $r['statut'] ?? 'En attente';
                $cls = match($s) { 'En attente' => 'statut-en-attente', 'En traitement' => 'statut-en-traitement', 'Résolue' => 'statut-resolue', 'Fermée' => 'statut-fermee', default => 'statut-en-attente' };
              ?>
                <tr class="hover:bg-white/5">
                  <td class="px-4 py-3 text-purple-300 font-bold">#<?= $r['idReclamation'] ?></td>
                  <td class="px-4 py-3 max-w-[150px]">
                    <div class="truncate"><?= htmlspecialchars($r['user_nom']??'') ?> <?= htmlspecialchars($r['user_prenom']??'') ?></div>
                    <div class="text-xs text-cyan-300 truncate"><?= htmlspecialchars($r['user_email']??'') ?></div>
                  </td>
                  <td class="px-4 py-3 text-yellow-300"><?= htmlspecialchars($r['type']??'—') ?></td>
                  <td class="px-4 py-3 max-w-[120px] truncate"><?= htmlspecialchars($r['jeu_titre'] ?? $r['produitConcerne'] ?? '—') ?></td>
                  <td class="px-4 py-3 max-w-[200px] truncate"><?= htmlspecialchars($r['description']??'') ?></td>
                  <td class="px-4 py-3 text-xs"><?= date('d/m/Y H:i', strtotime($r['dateReclamation'])) ?></td>
                  <td class="px-4 py-3 text-center"><span class="traitement-count"><?= $count ?></span></td>
                  <td class="px-4 py-3"><span class="statut-badge <?= $cls ?>"><?= $s ?></span></td>
                  <td class="px-4 py-3 text-center">
                    <a href="traiter_reclamation.php?id=<?= $r['idReclamation'] ?>" class="text-cyan-400 hover:text-cyan-200 font-medium">Traiter</a>
                    <form method="POST" class="inline ml-4" onsubmit="return confirm('Supprimer ?')">
                      <input type="hidden" name="id" value="<?= $r['idReclamation'] ?>">
                      <button type="submit" name="delete" class="text-red-400 hover:text-red-300 font-medium">Supprimer</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</body>
</html>