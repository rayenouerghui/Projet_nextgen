<?php
session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: connexion.php');
    exit;
}

require_once '../../controller/ReclamationController.php';
require_once '../../controller/TraitementController.php';

$reclamationController = new ReclamationController();
$traitementController = new TraitementController();

$userId = (int)$_SESSION['user']['id'];
$reclamations = $reclamationController->readByUserId($userId);
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes Réclamations – NextGen</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Exo+2:wght@500;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: url('../../resources/gamer-ezgif.com-added-text.gif') no-repeat center center fixed;
      background-size: cover;
      font-family: 'Exo 2', sans-serif;
      color: #e0e7ff;
      min-height: 100vh;
      margin: 0;
      padding: 0;
      position: relative;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background: rgba(10, 8, 35, 0.75);
      backdrop-filter: blur(4px);
      z-index: 1;
    }

    .content {
      position: relative;
      z-index: 2;
    }

    .glass {
      background: rgba(15,12,41,0.92);
      backdrop-filter: blur(16px);
      border-radius: 32px;
      padding: 40px 50px;
      border: 1px solid rgba(139,92,246,0.4);
      box-shadow: 0 20px 60px rgba(0,0,0,0.6);
    }

    .neon-text {
      text-shadow: 0 0 40px #8b5cf6;
    }

    .btn-neon {
      background: linear-gradient(45deg, #8b5cf6, #ec4899);
      box-shadow: 0 0 30px rgba(139,92,246,0.6);
      color: white;
      font-weight: bold;
      border-radius: 16px;
      padding: 14px 32px;
      transition: all 0.4s;
    }
    .btn-neon:hover {
      transform: translateY(-5px);
      box-shadow: 0 0 50px rgba(139,92,246,0.9);
    }

    .back-btn {
      position: fixed;
      top: 30px;
      left: 30px;
      z-index: 1000;
      background: rgba(255,255,255,0.15);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(139,92,246,0.6);
      width: 60px; height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.8rem;
      transition: all 0.4s;
      box-shadow: 0 8px 25px rgba(0,0,0,0.4);
    }
    .back-btn:hover {
      background: rgba(139,92,246,0.4);
      transform: scale(1.2);
      box-shadow: 0 0 35px rgba(139,92,246,0.8);
    }

    .card {
      background: rgba(255,255,255,0.08);
      border-radius: 20px;
      padding: 24px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      transition: transform 0.3s;
    }
    .card:hover {
      transform: translateY(-8px);
    }

    .statut-badge {
      padding: 0.5rem 1rem;
      border-radius: 9999px;
      font-weight: 600;
      font-size: 0.9rem;
    }
    .statut-en-attente { background: #fef3c7; color: #92400e; }
    .statut-en-traitement { background: #dbeafe; color: #1e40af; }
    .statut-resolue { background: #d1fae5; color: #065f46; }
    .statut-fermee { background: #e5e7eb; color: #374151; }

    .modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.9);
      backdrop-filter: blur(12px);
      z-index: 999;
      align-items: center;
      justify-content: center;
    }
    .modal-inner {
      background: rgba(15,12,41,0.95);
      border: 1px solid rgba(139,92,246,0.5);
      border-radius: 32px;
      padding: 40px;
      max-width: 900px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      position: relative;
    }
    .close {
      position: absolute;
      top: 20px;
      right: 30px;
      font-size: 2.5rem;
      color: #8b5cf6;
      cursor: pointer;
      transition: 0.3s;
    }
    .close:hover { color: #ec4899; transform: rotate(90deg); }

    @media (max-width: 768px) {
      .glass { padding: 30px 25px; margin: 100px 15px 40px; }
      .back-btn { top: 20px; left: 20px; width: 50px; height: 50px; font-size: 1.5rem; }
    }
  </style>
</head>
<body>
  <div class="content">

    <!-- Back Button -->
    <a href="index.php" class="back-btn" title="Retour">
      <i class="fas fa-arrow-left"></i>
    </a>

    <div class="max-w-5xl mx-auto mt-32 px-6">
      <h1 class="text-5xl font-black text-center mb-4 neon-text">Mes Réclamations</h1>
      <p class="text-center text-purple-200 mb-12 text-lg">Consultez l'historique de vos réclamations et les réponses de l'équipe.</p>

      <?php if (empty($reclamations) || isset($reclamations['error'])): ?>
        <div class="glass text-center py-16">
          <i class="fas fa-inbox text-6xl text-purple-400 mb-6"></i>
          <p class="text-2xl">Aucune réclamation trouvée.</p>
          <a href="reclamation.php" class="btn-neon inline-block mt-8 px-10 py-4 text-xl">Faire une nouvelle réclamation</a>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <?php foreach ($reclamations as $rec): ?>
            <div class="card">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <div class="text-2xl font-bold text-cyan-400">#<?= $rec['idReclamation'] ?> — <?= htmlspecialchars($rec['type'] ?? '—') ?></div>
                  <div class="text-purple-300 mt-1"><?= htmlspecialchars($rec['produitConcerne'] ?? ($rec['jeu_titre'] ?? 'Aucun jeu')) ?></div>
                </div>
                <div class="text-right">
                  <div class="text-sm text-purple-200"><?= date('d/m/Y à H:i', strtotime($rec['dateReclamation'])) ?></div>
                  <?php 
                    $statut = $rec['statut'] ?? 'En attente';
                    $cls = match($statut) {
                      'En attente' => 'statut-en-attente',
                      'En traitement' => 'statut-en-traitement',
                      'Résolue' => 'statut-resolue',
                      'Fermée' => 'statut-fermee',
                      default => 'statut-en-attente'
                    };
                  ?>
                  <span class="statut-badge <?= $cls ?> mt-3 inline-block"><?= $statut ?></span>
                </div>
              </div>

              <div class="text-white mb-6 line-clamp-4">
                <?= nl2br(htmlspecialchars($rec['description'] ?? '')) ?>
              </div>

              <div class="text-center">
                <button onclick="openModal(<?= (int)$rec['idReclamation'] ?>)" class="btn-neon px-8 py-3 rounded-xl text-lg">
                  Voir les détails & réponses
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- MODAL -->
  <div id="modal" class="modal">
    <div class="modal-inner">
      <span class="close" onclick="closeModal()">&times;</span>
      <div id="modalContent" class="text-center py-12 text-2xl text-purple-300">Chargement...</div>
    </div>
  </div>

  <script>
    function openModal(id) {
      fetch('mes_reclamations.php?modal=' + id)
        .then(r => r.text())
        .then(html => {
          document.getElementById('modalContent').innerHTML = html;
          document.getElementById('modal').style.display = 'flex';
        });
    }
    function closeModal() {
      document.getElementById('modal').style.display = 'none';
    }
    window.onclick = function(e) {
      if (e.target == document.getElementById('modal')) closeModal();
    }
  </script>

<?php
// === MODAL CONTENT ONLY ===
if (isset($_GET['modal'])) {
    $id = (int)$_GET['modal'];
    $reclamation = $reclamationController->readById($id);
    if (isset($reclamation['error']) || ((int)$reclamation['id_user'] !== $userId)) {
        echo '<div class="text-center py-12"><p class="text-3xl text-red-400">Accès refusé ou réclamation introuvable.</p></div>';
        exit;
    }

    $traitements = $traitementController->readByReclamationId($id);
    $statut = $reclamation['statut'] ?? 'En attente';
    $cls = match($statut) {
        'En attente' => 'statut-en-attente',
        'En traitement' => 'statut-en-traitement',
        'Résolue' => 'statut-resolue',
        'Fermée' => 'statut-fermee',
        default => 'statut-en-attente'
    };
    ?>
    <div class="space-y-8">
      <h2 class="text-4xl font-black text-cyan-400 text-center neon-text">Réclamation #<?= $reclamation['idReclamation'] ?></h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="glass p-6 rounded-2xl">
          <strong class="text-purple-300 block mb-3 text-lg">Informations</strong>
          <p><strong>Type :</strong> <?= htmlspecialchars($reclamation['type'] ?? '—') ?></p>
          <p><strong>Produit :</strong> <?= htmlspecialchars($reclamation['jeu_titre'] ?? $reclamation['produitConcerne'] ?? 'Aucun') ?></p>
          <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($reclamation['dateReclamation'])) ?></p>
          <p class="mt-4"><strong>Statut :</strong> <span class="statut-badge <?= $cls ?>"><?= $statut ?></span></p>
        </div>

        <div class="glass p-6 rounded-2xl">
          <strong class="text-purple-300 block mb-3 text-lg">Description complète</strong>
          <div class="text-white"><?= nl2br(htmlspecialchars($reclamation['description'] ?? '')) ?></div>
        </div>
      </div>

      <div>
        <h3 class="text-3xl font-bold text-cyan-400 mb-6 text-center">Réponses de l'équipe (<?= count($traitements) ?>)</h3>
        <?php if (empty($traitements)): ?>
          <div class="text-center py-12 glass rounded-2xl">
            <i class="fas fa-comment-slash text-5xl text-purple-400 mb-4"></i>
            <p class="text-xl">Aucune réponse pour le moment.</p>
          </div>
        <?php else: ?>
          <?php foreach ($traitements as $t): ?>
            <div class="glass p-6 rounded-2xl mb-6 border-l-4 border-cyan-400">
              <div class="flex justify-between mb-4">
                <div>
                  <strong class="text-purple-300"><?= htmlspecialchars($t['auteur_nom'] ?? 'Admin') ?> <?= htmlspecialchars($t['auteur_prenom'] ?? '') ?></strong>
                  <span class="text-cyan-300 text-sm block"><?= htmlspecialchars($t['auteur_email'] ?? '') ?> • <?= date('d/m/Y à H:i', strtotime($t['dateReclamation'])) ?></span>
                </div>
              </div>
              <div class="text-white"><?= nl2br(htmlspecialchars($t['contenu'] ?? '')) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php
    exit;
}
?>

</body>
</html>