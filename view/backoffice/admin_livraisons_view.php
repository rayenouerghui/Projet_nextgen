<?php
/**
 * Admin Livraisons View - Gaming Style Dashboard
 * Identical design to admin_jeux.php, admin_users.php, etc.
 * Note: Session already started in admin_livraisons.php entry point
 */

$iconMap = [
    'commandee' => 'fa-file-alt',
    'preparée' => 'fa-box',
    'emballee' => 'fa-box',
    'en_transit' => 'fa-truck',
    'en_route' => 'fa-truck',
    'livree' => 'fa-check-circle',
    'annulée' => 'fa-times-circle'
];
$colorMap = [
    'commandee' => 'gray',
    'preparée' => 'cyan',
    'emballee' => 'cyan',
    'en_transit' => 'purple',
    'en_route' => 'purple',
    'livree' => 'green',
    'annulée' => 'red'
];
?>
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des Livraisons – NextGen Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Exo+2:wght@500;700&display=swap" rel="stylesheet">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); font-family: 'Exo 2', sans-serif; color: #e0e7ff; }
    .glass { background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border: 1px solid rgba(139,92,246,0.3); box-shadow: 0 8px 32px rgba(0,0,0,0.4); }
    .neon-text { text-shadow: 0 0 30px #8b5cf6; }
    .btn-neon { background: linear-gradient(45deg, #8b5cf6, #ec4899); box-shadow: 0 0 30px rgba(139,92,246,0.6); }
    .btn-neon:hover { transform: translateY(-3px); box-shadow: 0 0 40px rgba(139,92,246,0.9); }
    .sidebar-closed { width: 5rem !important; }
    .sidebar-open { width: 16rem; }
    @keyframes truck-drive { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(5px); } }
    .animate-truck { animation: truck-drive 0.5s ease-in-out infinite; }
  </style>
</head>
<body class="h-full" x-data="{ sidebarOpen: true, showCreateModal: false }">

  <?php if (!empty($message)): ?>
  <script>
    Swal.fire({
      icon: '<?= $messageType === 'success' ? 'success' : ($messageType === 'warning' ? 'warning' : 'error') ?>',
      title: '<?= $messageType === 'success' ? 'Succès!' : 'Attention' ?>',
      text: '<?= addslashes($message) ?>',
      background: '#1f2937',
      color: '#f3f4f6',
      confirmButtonColor: '#8b5cf6'
    });
  </script>
  <?php endif; ?>

  <!-- SIDEBAR -->
  <aside :class="sidebarOpen ? 'sidebar-open' : 'sidebar-closed'" 
         class="glass fixed inset-y-0 left-0 z-40 flex flex-col transition-all duration-300 overflow-hidden">
    <div class="p-5 text-center border-b border-purple-500/30">
      <h1 class="text-3xl font-black bg-gradient-to-r from-cyan-400 to-purple-600 bg-clip-text text-transparent">NEXTGEN</h1>
      <p x-show="sidebarOpen" class="text-purple-300 text-sm font-bold mt-1">ADMIN PANEL</p>
    </div>

    <nav class="flex-1 px-3 py-6 space-y-2">
      <?php $current = 'admin_livraisons.php'; ?>
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
<a href="admin_livraisons.php" class="flex items-center space-x-4 px-4 py-3 rounded-lg bg-gradient-to-r from-orange-600 to-amber-600 text-white font-bold">
        <i class="fas fa-truck w-8 text-xl"></i>
        <span x-show="sidebarOpen">Livraisons</span>
        <?php if ($totalLivraisons > 0): ?>
          <span x-show="sidebarOpen" class="ml-auto bg-white/20 px-2 py-1 rounded-full text-xs"><?= $totalLivraisons ?></span>
        <?php endif; ?>
      </a>
    </nav>

    <div class="p-4 border-t border-purple-500/30">
      <button @click="sidebarOpen = !sidebarOpen" class="w-full py-4 hover:bg-white/5 transition rounded-lg">
        <i class="fas fa-chevron-left mx-auto text-xl text-purple-300" :class="{'rotate-180': !sidebarOpen}"></i>
      </button>
      <a href="../frontoffice/index.php" class="flex items-center justify-center gap-3 px-4 py-4 mt-3 rounded-xl bg-gradient-to-r from-cyan-500 to-teal-600 text-white font-bold hover:scale-105 transition">
        <i class="fas fa-home"></i><span x-show="sidebarOpen">Retour accueil</span>
      </a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="min-h-screen transition-all duration-300" :class="sidebarOpen ? 'lg:ml-64 ml-20' : 'ml-20'">
    <div class="max-w-7xl mx-auto px-6 py-10">

      <div class="text-center mb-6">
        <div class="flex items-center justify-center gap-4 mb-4">
          <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="animate-truck">
            <rect x="8" y="24" width="40" height="24" rx="4" fill="url(#truckGradient)"/>
            <rect x="12" y="28" width="20" height="16" rx="2" fill="rgba(255,255,255,0.2)"/>
            <circle cx="18" cy="48" r="4" fill="#1a1a2e"/>
            <circle cx="18" cy="48" r="2" fill="#667eea"/>
            <circle cx="46" cy="48" r="4" fill="#1a1a2e"/>
            <circle cx="46" cy="48" r="2" fill="#667eea"/>
            <path d="M48 24 L56 20 L56 32 L48 36 Z" fill="url(#truckGradient)"/>
            <defs>
              <linearGradient id="truckGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
              </linearGradient>
            </defs>
          </svg>
          <h1 class="text-5xl font-black bg-gradient-to-r from-cyan-400 via-purple-500 to-pink-500 bg-clip-text text-transparent neon-text">
            GESTION DES LIVRAISONS
          </h1>
        </div>
      </div>

      <!-- CREATE BUTTON -->
      <div class="text-center mb-8">
        <button @click="showCreateModal = true" class="btn-neon text-white px-8 py-4 rounded-xl font-bold text-lg transition transform hover:scale-105 flex items-center justify-center gap-3 mx-auto">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
          </svg>
          <span>Nouvelle Livraison</span>
        </button>
      </div>

      <!-- STATS CARDS -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-10">
        <?php 
        $svgIcons = [
            'commandee' => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><rect x="6" y="8" width="20" height="18" rx="2" stroke="currentColor" stroke-width="2" fill="none"/><path d="M6 12H26" stroke="currentColor" stroke-width="2"/><circle cx="11" cy="17" r="1.5" fill="currentColor"/><circle cx="16" cy="17" r="1.5" fill="currentColor"/></svg>',
            'preparée' => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><rect x="8" y="6" width="16" height="20" rx="2" stroke="currentColor" stroke-width="2" fill="none"/><path d="M12 10H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 16H20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 22H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
            'en_route' => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><rect x="6" y="14" width="18" height="10" rx="2" fill="currentColor" opacity="0.2"/><rect x="8" y="16" width="14" height="6" rx="1" fill="currentColor"/><circle cx="10" cy="26" r="3" fill="#1a1a2e" stroke="currentColor" stroke-width="1.5"/><circle cx="22" cy="26" r="3" fill="#1a1a2e" stroke="currentColor" stroke-width="1.5"/><circle cx="10" cy="26" r="1.5" fill="currentColor"/><circle cx="22" cy="26" r="1.5" fill="currentColor"/><path d="M24 14 L28 10 L28 18 L24 22 Z" fill="currentColor"/></svg>',
            'livree' => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="12" stroke="currentColor" stroke-width="2.5" fill="none"/><path d="M10 16 L14 20 L22 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'annulée' => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none"><circle cx="16" cy="16" r="12" stroke="currentColor" stroke-width="2.5" fill="none"/><path d="M12 12 L20 20 M20 12 L12 20" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>'
        ];
        foreach ($stats as $statut => $count): 
            $color = $colorMap[$statut] ?? 'gray';
            $svgIcon = $svgIcons[$statut] ?? $svgIcons['commandee'];
        ?>
        <div class="glass rounded-2xl p-5 text-center hover:scale-105 transition cursor-pointer group">
          <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-<?= $color ?>-500/20 flex items-center justify-center group-hover:bg-<?= $color ?>-500/30 transition">
            <div class="text-<?= $color ?>-400">
              <?= $svgIcon ?>
            </div>
          </div>
          <h3 class="text-3xl font-black text-white mb-1"><?= $count ?></h3>
          <p class="text-xs text-<?= $color ?>-300 font-bold uppercase tracking-wider"><?= strtoupper($statut) ?></p>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- TABLE -->
      <div class="glass rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gradient-to-r from-purple-800 to-pink-800">
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">ID</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Client</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Jeu</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Adresse</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Statut</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-cyan-300 uppercase">Trajet</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-cyan-300 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-purple-500/20">
              <?php if (empty($livraisons)): ?>
                <tr>
                  <td colspan="7" class="text-center py-16 text-gray-400 text-lg">
                    <div class="flex flex-col items-center">
                      <svg width="80" height="80" viewBox="0 0 80 80" fill="none" class="mb-4 opacity-50">
                        <rect x="20" y="24" width="40" height="32" rx="4" stroke="currentColor" stroke-width="2" fill="none"/>
                        <path d="M20 32 L60 32" stroke="currentColor" stroke-width="2"/>
                        <path d="M28 40 L52 40" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M28 48 L48 48" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="16" cy="56" r="4" fill="currentColor" opacity="0.3"/>
                        <circle cx="64" cy="56" r="4" fill="currentColor" opacity="0.3"/>
                      </svg>
                      <p>Aucune livraison. Cliquez sur "Nouvelle Livraison" pour commencer.</p>
                    </div>
                  </td>
                </tr>
              <?php else: foreach ($livraisons as $l): ?>
                <tr class="hover:bg-white/5 transition">
                  <td class="px-6 py-5 text-purple-300 font-medium">#<?= $l['id_livraison'] ?></td>
                  <td class="px-6 py-5">
                    <div class="font-semibold text-white"><?= htmlspecialchars($l['prenom_utilisateur'] . ' ' . $l['nom_utilisateur']) ?></div>
                    <div class="text-xs text-purple-300"><?= $l['numero_commande'] ?></div>
                  </td>
                  <td class="px-6 py-5 text-cyan-300"><?= htmlspecialchars($l['nom_jeu'] ?? '—') ?></td>
                  <td class="px-6 py-5 text-gray-300 text-sm max-w-xs truncate"><?= htmlspecialchars(substr($l['adresse_complete'] ?? '', 0, 35)) ?>...</td>
                  <td class="px-6 py-5">
                    <?php 
                    $statusColors = ['commandee'=>'bg-gray-500','preparée'=>'bg-cyan-500','en_route'=>'bg-purple-500','livree'=>'bg-green-500','annulée'=>'bg-red-500'];
                    $bgColor = $statusColors[$l['statut']] ?? 'bg-gray-500';
                    ?>
                    <span class="px-3 py-1 rounded-full text-xs font-bold text-white <?= $bgColor ?>"><?= ucfirst($l['statut']) ?></span>
                  </td>
                  <td class="px-6 py-5">
                    <?php if (!empty($l['trajet'])): ?>
                      <span class="text-green-400 text-xs flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                          <circle cx="7" cy="7" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
                          <path d="M7 2 L7 0 M7 14 L7 12 M2 7 L0 7 M14 7 L12 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                          <circle cx="7" cy="7" r="1" fill="currentColor"/>
                        </svg>
                        <span>GPS Actif</span>
                      </span>
                    <?php else: ?>
                      <span class="text-gray-500 text-xs flex items-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                          <circle cx="7" cy="7" r="5" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.5"/>
                        </svg>
                        <span>Non initialisé</span>
                      </span>
                    <?php endif; ?>
                  </td>
                  <td class="px-6 py-5">
                    <div class="flex items-center justify-center gap-2 flex-wrap">
                      
                      <!-- Status Dropdown -->
                      <form method="post" class="inline">
                        <input type="hidden" name="action" value="update_statut">
                        <input type="hidden" name="id_livraison" value="<?= $l['id_livraison'] ?>">
                        <select name="statut" onchange="this.form.submit()" class="bg-black/40 border border-purple-500/50 text-white text-xs rounded-lg px-2 py-1">
                          <?php foreach ($statuts as $s): ?>
                            <option value="<?= $s ?>" <?= $l['statut'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </form>
                      
                      <?php 
                      // Make status comparison case-insensitive
                      $statusLower = strtolower(trim($l['statut']));
                      $isConfirmable = in_array($statusLower, ['commandee', 'commandée', 'preparee', 'preparée', 'préparée', 'emballee', 'emballée']);
                      $isTracking = in_array($statusLower, ['en_route', 'en route', 'en_transit', 'enroute', 'livree', 'livrée']);
                      ?>
                      
                      <!-- CONFIRMER BUTTON - Confirms and starts real-time delivery -->
                      <?php if ($isConfirmable): ?>
                        <button onclick="confirmLivraison(<?= $l['id_livraison'] ?>, '<?= addslashes($l['prenom_utilisateur'] . ' ' . $l['nom_utilisateur']) ?>')" 
                                class="bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition shadow-lg shadow-green-500/30 flex items-center gap-2">
                          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5" fill="none"/>
                            <path d="M5 8 L7 10 L11 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                          <span>Confirmer</span>
                        </button>
                        <form id="confirmForm_<?= $l['id_livraison'] ?>" method="post" class="hidden">
                          <input type="hidden" name="action" value="confirm_livraison">
                          <input type="hidden" name="id_livraison" value="<?= $l['id_livraison'] ?>">
                        </form>
                      <?php endif; ?>
                      
                      <!-- SUIVI LIVE BUTTON - Opens fullscreen tracking page -->
                      <?php if ($isTracking): ?>
                        <button onclick="openTrackingFullscreen(<?= $l['id_livraison'] ?>)" 
                                class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition shadow-lg shadow-blue-500/50 flex items-center gap-2">
                          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="8" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
                            <path d="M8 2 L8 0 M8 16 L8 14 M2 8 L0 8 M16 8 L14 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <circle cx="8" cy="8" r="1" fill="currentColor"/>
                          </svg>
                          <span>Suivi Live</span>
                        </button>
                      <?php endif; ?>

                      <!-- AVANCER POSITION (simulation) -->
                      <?php if ($statusLower === 'en_route' && !empty($l['trajet'])): ?>
                        <form method="post" class="inline">
                          <input type="hidden" name="action" value="refresh_trajet">
                          <input type="hidden" name="id_livraison" value="<?= $l['id_livraison'] ?>">
                          <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-black px-3 py-2 rounded-lg text-sm font-bold transition flex items-center justify-center" title="Simuler avancement">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                              <path d="M3 4 L11 8 L3 12 Z" fill="currentColor"/>
                              <path d="M11 4 L13 4 L13 12 L11 12 Z" fill="currentColor"/>
                            </svg>
                          </button>
                        </form>
                      <?php endif; ?>
                      
                      <!-- DELETE -->
                      <button onclick="deleteLivraison(<?= $l['id_livraison'] ?>)" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded-lg text-xs font-bold transition flex items-center justify-center" title="Supprimer">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                          <path d="M3 3.5 L11 3.5 M5.5 3.5 L5.5 2.5 C5.5 2.2 5.7 2 6 2 L8 2 C8.3 2 8.5 2.2 8.5 2.5 L8.5 3.5 M5.5 6 L5.5 10.5 M8.5 6 L8.5 10.5 M2.5 3.5 L2.5 11.5 C2.5 12 2.7 12.5 3 12.5 L11 12.5 C11.3 12.5 11.5 12 11.5 11.5 L11.5 3.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                        </svg>
                      </button>
                      <form id="deleteForm_<?= $l['id_livraison'] ?>" method="post" class="hidden">
                        <input type="hidden" name="action" value="delete_livraison">
                        <input type="hidden" name="id_livraison" value="<?= $l['id_livraison'] ?>">
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="mt-8 text-center text-purple-300">
        Total: <span class="font-bold text-2xl text-cyan-400"><?= $totalLivraisons ?></span> livraison(s)
      </div>
    </div>
  </main>

  <!-- CREATE MODAL -->
  <div x-show="showCreateModal" x-cloak class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
    <div class="glass rounded-2xl p-8 w-full max-w-lg" @click.away="showCreateModal = false">
      <h2 class="text-2xl font-bold text-white mb-6"><i class="fas fa-plus-circle mr-2 text-cyan-400"></i>Nouvelle Livraison</h2>
      
      <form method="post" id="createForm">
        <input type="hidden" name="action" value="create_livraison">
        
        <div class="mb-4">
          <label class="block text-purple-300 mb-2">Commande *</label>
          <select name="id_commande" required class="w-full bg-black/40 border border-purple-500/50 text-white rounded-lg px-4 py-3">
            <option value="">-- Sélectionner --</option>
            <?php foreach ($commandes as $cmd): ?>
              <option value="<?= $cmd['id_commande'] ?>"><?= $cmd['numero_commande'] ?> - <?= htmlspecialchars($cmd['nom_utilisateur']) ?> (<?= htmlspecialchars($cmd['nom_jeu']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="mb-4">
          <label class="block text-purple-300 mb-2">Adresse *</label>
          <input type="text" name="adresse_complete" required class="w-full bg-black/40 border border-purple-500/50 text-white rounded-lg px-4 py-3" placeholder="123 Avenue Bourguiba">
        </div>
        
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-purple-300 mb-2">Ville *</label>
            <input type="text" name="ville" required class="w-full bg-black/40 border border-purple-500/50 text-white rounded-lg px-4 py-3" placeholder="Tunis">
          </div>
          <div>
            <label class="block text-purple-300 mb-2">Code Postal</label>
            <input type="text" name="code_postal" class="w-full bg-black/40 border border-purple-500/50 text-white rounded-lg px-4 py-3" placeholder="1000">
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
          <div>
            <label class="block text-purple-300 mb-2">Latitude (optionnel)</label>
            <input type="text" name="position_lat" class="w-full bg-black/40 border border-purple-500/50 text-white rounded-lg px-4 py-3" placeholder="36.8065">
          </div>
          <div>
            <label class="block text-purple-300 mb-2">Longitude (optionnel)</label>
            <input type="text" name="position_lng" class="w-full bg-black/40 border border-purple-500/50 text-white rounded-lg px-4 py-3" placeholder="10.1815">
          </div>
        </div>
        
        <div class="flex gap-4">
          <button type="submit" class="flex-1 btn-neon text-white px-6 py-3 rounded-xl font-bold transition">
            <i class="fas fa-check mr-2"></i> Créer
          </button>
          <button type="button" @click="showCreateModal = false" class="px-6 py-3 bg-gray-600 text-white rounded-xl font-bold hover:bg-gray-700 transition">
            Annuler
          </button>
        </div>
      </form>
    </div>
  </div>

<script>
// 🚀 Confirm delivery with SweetAlert2
function confirmLivraison(id, clientName) {
    Swal.fire({
        title: '🚚 Lancer la livraison ?',
        html: `<div class="text-left">
                 <p class="text-lg mb-2">Client: <strong>${clientName}</strong></p>
                 <p class="text-sm text-gray-400">Cette action va:</p>
                 <ul class="text-sm text-gray-300 mt-2 list-disc list-inside">
                   <li>Changer le statut à "En route"</li>
                   <li>Créer le trajet GPS avec route OSRM</li>
                   <li>Activer le suivi en temps réel</li>
                 </ul>
               </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="display:inline-block;vertical-align:middle;margin-right:4px;"><path d="M8 2 L8 0 M8 16 L8 14 M2 8 L0 8 M16 8 L14 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="8" cy="8" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/></svg> Oui, lancer!',
        cancelButtonText: 'Annuler',
        background: '#1f2937',
        color: '#f3f4f6'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: '🎉 Livraison lancée!',
                html: '<div class="py-4"><svg width="80" height="80" viewBox="0 0 80 80" fill="none" class="mx-auto animate-bounce"><rect x="10" y="30" width="50" height="30" rx="5" fill="#10b981" opacity="0.2"/><rect x="14" y="34" width="25" height="22" rx="2" fill="#10b981"/><circle cx="18" cy="60" r="5" fill="#1a1a2e"/><circle cx="18" cy="60" r="2.5" fill="#10b981"/><circle cx="58" cy="60" r="5" fill="#1a1a2e"/><circle cx="58" cy="60" r="2.5" fill="#10b981"/><path d="M60 30 L70 25 L70 40 L60 45 Z" fill="#10b981"/></svg></div><p>Le livreur est en route!</p>',
                timer: 2000,
                showConfirmButton: false,
                background: '#1f2937',
                color: '#f3f4f6'
            }).then(() => {
                document.getElementById('confirmForm_' + id).submit();
            });
        }
    });
}

// 📡 Open GPS tracking in FULLSCREEN
function openTrackingFullscreen(id) {
    Swal.fire({
        title: '📡 Connexion au suivi GPS...',
        html: `<div class="py-6 flex flex-col items-center">
                 <svg width="80" height="80" viewBox="0 0 80 80" fill="none" class="mb-4 animate-pulse">
                   <circle cx="40" cy="40" r="15" stroke="#60a5fa" stroke-width="3" fill="none"/>
                   <path d="M40 10 L40 5 M40 75 L40 70 M10 40 L5 40 M75 40 L70 40" stroke="#60a5fa" stroke-width="3" stroke-linecap="round"/>
                   <circle cx="40" cy="40" r="5" fill="#60a5fa"/>
                   <path d="M25 25 L20 20 M55 25 L60 20 M25 55 L20 60 M55 55 L60 60" stroke="#60a5fa" stroke-width="2" stroke-linecap="round"/>
                 </svg>
                 <div class="w-full bg-gray-700 rounded-full h-3 mt-4">
                   <div class="bg-blue-500 h-3 rounded-full animate-pulse" style="width: 60%"></div>
                 </div>
                 <div class="text-sm text-gray-400 mt-3">Ouverture du suivi en temps réel...</div>
               </div>`,
        timer: 2000,
        showConfirmButton: false,
        background: '#1f2937',
        color: '#f3f4f6',
        didClose: () => {
            // Open in new fullscreen window
            const url = '../tracking.php?id_livraison=' + id;
            const fullscreenWindow = window.open(url, '_blank', 'fullscreen=yes,menubar=no,toolbar=no,location=no,status=no');
            
            // Try to make it fullscreen
            if (fullscreenWindow) {
                fullscreenWindow.moveTo(0, 0);
                fullscreenWindow.resizeTo(screen.width, screen.height);
            }
        }
    });
}

// 🗑️ Delete with confirmation
function deleteLivraison(id) {
    Swal.fire({
        title: '⚠️ Supprimer cette livraison?',
        text: 'Cette action est irréversible!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler',
        background: '#1f2937',
        color: '#f3f4f6'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteForm_' + id).submit();
        }
    });
}
</script>

</body>
</html>
