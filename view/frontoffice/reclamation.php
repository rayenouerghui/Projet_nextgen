<?php
session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: connexion.php');
    exit;
}

require_once '../../controller/ReclamationController.php';
require_once '../../controller/jeuController.php';

$reclamationController = new ReclamationController();
$jeuController = new JeuController();

$error = '';
$success = '';
$warning = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $id_jeu = !empty($_POST['id_jeu']) ? (int)$_POST['id_jeu'] : null;
    
    if (empty($type) || empty($description)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        $reclamation = new Reclamation();
        $reclamation->setIdUser($_SESSION['user']['id'])
                   ->setIdJeu($id_jeu)
                   ->setType($type)
                   ->setDescription($description)
                   ->setDateReclamation(date('Y-m-d H:i:s'))
                   ->setStatut('En attente');
        
        $result = $reclamationController->create($reclamation);
        
        if ($result['success']) {
            $success = 'Votre réclamation a été envoyée avec succès !';
            if (!empty($result['ai_score'])) {
                $ai_score = round($result['ai_score'] * 100);
                $success .= " (Score qualité: {$ai_score}%)";
            }
            $_POST = [];
        } else {
            $error = $result['message'] ?? 'Une erreur est survenue.';
            if (!empty($result['needs_rewrite'])) {
                $warning = 'Votre message pourrait être amélioré.';
            }
        }
    }
}

$jeux = $jeuController->afficherJeux();
require_once '../../models/Reclamation.php';
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nouvelle Réclamation – NextGen</title>
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

    select, textarea {
      background: rgba(0,0,0,0.4) !important;
      border: 1px solid #8b5cf6 !important;
      color: white !important;
    }
    select:focus, textarea:focus {
      outline: none;
      border-color: #06b6d4 !important;
      box-shadow: 0 0 15px rgba(6,182,212,0.5);
    }

    @media (max-width: 768px) {
      .glass { padding: 30px 25px; margin: 100px 15px 40px; }
      .back-btn { top: 20px; left: 20px; width: 50px; height: 50px; font-size: 1.5rem; }
    }
  </style>
</head>
<body>
  <div class="content">

    <!-- Back Button -->
    <a href="index.php" class="back-btn" title="Retour à l'accueil">
      <i class="fas fa-arrow-left"></i>
    </a>

    <div class="max-w-3xl mx-auto mt-32 px-6">
      <h1 class="text-5xl font-black text-center mb-12 neon-text">Nouvelle Réclamation</h1>

      <!-- Messages -->
      <?php if ($error): ?>
        <div class="mb-8 p-5 bg-red-900/60 border border-red-500 rounded-2xl text-center">
          <i class="fas fa-exclamation-triangle text-2xl"></i>
          <p class="mt-2 font-bold"><?= htmlspecialchars($error) ?></p>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="mb-8 p-5 bg-green-900/60 border border-green-500 rounded-2xl text-center">
          <i class="fas fa-check-circle text-3xl"></i>
          <p class="mt-2 font-bold"><?= htmlspecialchars($success) ?></p>
        </div>
      <?php endif; ?>

      <?php if ($warning): ?>
        <div class="mb-8 p-5 bg-yellow-900/60 border border-yellow-500 rounded-2xl text-center">
          <i class="fas fa-exclamation-circle text-2xl"></i>
          <p class="mt-2 font-bold"><?= htmlspecialchars($warning) ?></p>
        </div>
      <?php endif; ?>

      <!-- Form Card -->
      <div class="glass">
        <form method="POST" class="space-y-8">
          
          <!-- Type -->
          <div>
            <label class="block text-purple-300 font-bold mb-3 text-lg">Type de Réclamation <span class="text-red-400">*</span></label>
            <select name="type" class="w-full px-6 py-4 rounded-xl text-lg" required>
              <option value="">Choisir un type...</option>
              <option value="defaut_produit">Défaut du produit</option>
              <option value="livraison_retard">Livraison en retard</option>
              <option value="produit_manquant">Produit manquant</option>
              <option value="autre">Autre</option>
            </select>
          </div>

          <!-- Jeu Concerné -->
          <div>
            <label class="block text-purple-300 font-bold mb-3 text-lg">Jeu Concerné (facultatif)</label>
            <select name="id_jeu" class="w-full px-6 py-4 rounded-xl text-lg">
              <option value="">-- Aucun jeu --</option>
              <?php if ($jeux): ?>
                <?php foreach ($jeux as $jeu): ?>
                  <option value="<?= $jeu->getIdJeu() ?>">
                    <?= htmlspecialchars($jeu->getTitre()) ?> (<?= number_format($jeu->getPrix(), 2) ?> TND)
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-purple-300 font-bold mb-3 text-lg">Description Détaillée <span class="text-red-400">*</span></label>
            <textarea name="description" rows="7" class="w-full px-6 py-5 rounded-xl text-lg resize-none" 
                      placeholder="Expliquez clairement votre problème..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            <p class="text-sm text-cyan-300 mt-2">Minimum 10 caractères • Notre IA analysera la clarté et la pertinence</p>
          </div>

          <!-- Submit -->
          <div class="flex justify-center gap-6 pt-6">
            <button type="submit" class="btn-neon text-xl py-5 px-12 hover:scale-105 transition">
              <i class="fas fa-paper-plane mr-3"></i>
              Envoyer la Réclamation
            </button>
            <button type="button" onclick="resetForm()" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-5 px-12 rounded-xl text-xl transition">
              Effacer
            </button>
          </div>
        </form>

        <!-- Info IA -->
        <div class="mt-12 p-6 bg-gradient-to-r from-purple-900/50 to-pink-900/50 rounded-2xl border border-purple-500">
          <h3 class="text-xl font-bold flex items-center gap-3 mb-3">
            <i class="fas fa-robot text-cyan-400"></i>
            Analyse IA en temps réel
          </h3>
          <ul class="text-sm space-y-2 text-purple-200">
            <li>Clarté et cohérence du message</li>
            <li>Détection de langage inapproprié</li>
            <li>Pertinence de la réclamation</li>
            <li>Structure naturelle et respectueuse</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <script>
  // Désactiver la validation HTML5
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    if (form) {
      form.setAttribute('novalidate', 'novalidate');
    }
  });

  // Fonction pour vraiment effacer tout le formulaire
  function resetForm() {
    const form = document.querySelector('form');
    if (form) {
      form.reset(); // Réinitialise tous les champs

      // Force le select "type" à revenir sur la première option
      const typeSelect = form.querySelector('select[name="type"]');
      if (typeSelect) typeSelect.selectedIndex = 0;

      // Force le select "id_jeu" à revenir sur "-- Aucun jeu --"
      const jeuSelect = form.querySelector('select[name="id_jeu"]');
      if (jeuSelect) jeuSelect.selectedIndex = 0;

      // Vide complètement la textarea
      const textarea = form.querySelector('textarea[name="description"]');
      if (textarea) textarea.value = '';
    }
  }
</script>
</body>
</html>