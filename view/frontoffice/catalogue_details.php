<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../controller/jeuController.php';
require_once '../../controller/userController.php';

$controller = new JeuController();

$id_jeu = (int)($_GET['id'] ?? 0);
$jeu = $controller->getJeu($id_jeu);

if (!$jeu) {
    header('Location: catalogue.php');
    exit;
}

// Récupération réelle des crédits depuis la BDD
$actualCredits = 0;
if (isset($_SESSION['user']['id'])) {
    $userCtrl = new userController();
    $dbUser = $userCtrl->getUserById((int)$_SESSION['user']['id']);
    if ($dbUser) {
        $actualCredits = $dbUser->getCredits();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($jeu->getTitre()) ?> – NextGen</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Rajdhani:wght@600;800&display=swap" rel="stylesheet">
  
  <style>
    /* === STYLE GÉNÉRAL (inchangé) === */
    .user-dropdown { position: relative; right:-120px; top: 0px; z-index: 1001; }
    .user-btn { background: none; border: none; display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 8px 14px; border-radius: 50px; transition: all 0.3s ease; font-family: 'Inter', sans-serif; }
    .user-btn:hover { background: rgba(79, 70, 229, 0.1); }
    .user-name { color: #4f46e5; font-weight: 600; font-size: 0.95rem; }
    .user-avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 3px solid #4f46e5; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.25); }
    .dropdown-menu { position: absolute; top: 100%; right: 0; background: white; min-width: 230px; border-radius: 14px; box-shadow: 0 12px 40px rgba(0,0,0,0.18); opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.3s ease; z-index: 1000; margin-top: 10px; }
    .user-dropdown:hover .dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
    .dropdown-menu a { display: flex; align-items: center; gap: 12px; padding: 14px 20px; color: #374151; font-size: 0.95rem; transition: all 0.2s; text-decoration: none; }
    .dropdown-menu a:hover { background: #f8f9ff; color: #4f46e5; padding-left: 26px; }
    .dropdown-menu hr { margin: 8px 0; border: none; border-top: 1px solid #e5e7eb; }
    .logout-item { color: #ef4444 !important; }
    .logout-item:hover { background: #fef2f2 !important; color: #dc2626 !important; }

    body { 
      background: url('../../resources/bg-jeux.gif') no-repeat center top fixed !important; 
      background-size: cover !important; 
      margin:0; 
      min-height: 100vh;
    }
    body::before { 
      content:''; 
      position:fixed; 
      top:0; left:0; right:0; bottom:0; 
      background:rgba(0,0,0,0.55); 
      backdrop-filter:blur(8px); 
      -webkit-backdrop-filter:blur(8px); 
      z-index:1; 
      pointer-events:none; 
    }
    header, .container, footer { position:relative; z-index:2; }

    /* === CONTENU PRINCIPAL === */
    .details-container {
      background: linear-gradient(135deg, rgba(56,28,135,0.9), rgba(59,7,100,0.8));
      border-radius:1.5rem; padding:2rem; box-shadow:0 15px 40px rgba(0,0,0,0.5); border:1px solid rgba(139,92,246,0.6);
      max-width:1000px; margin:2.5rem auto; display:grid; grid-template-columns:1fr 1fr; gap:2rem;
    }
    .game-image { width:100%; border-radius:1rem; box-shadow:0 10px 30px rgba(0,0,0,0.4); }
    .game-info h1 { font-family:'Rajdhani',sans-serif; font-weight:800; font-size:2.4rem; color:white; text-shadow:0 0 20px rgba(255,255,255,0.4); margin-bottom:0.5rem; }
    .game-category { color:#e0d4ff; font-size:1.2rem; font-weight:600; margin-bottom:1rem; }
    .game-price { color:#00ffc3; font-size:2.2rem; font-weight:900; text-shadow:0 0 25px #00ffc3; margin:1rem 0; }
    .game-description h3 { color:white; font-size:1.5rem; margin-top:1.5rem; }
    .game-description p { color:#e0d4ff; line-height:1.7; font-size:1.05rem; }

    /* Boutons principaux */
    .btn-master {
      padding: 1.1rem 3.5rem; border-radius: 60px; font-size: 1.4rem; font-weight: 900;
      text-transform: uppercase; letter-spacing: 2px; box-shadow: 0 0 40px rgba(139,92,246,0.8);
      transition: all 0.4s ease; color: white; display: inline-block; text-align: center;
      min-width: 280px; border: none; cursor: pointer; position: relative; overflow: hidden;
    }
    .btn-master::before {
      content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: 0.7s;
    }
    .btn-master:hover::before { left: 100%; }
    .btn-master:hover { transform: translateY(-8px); box-shadow: 0 0 60px rgba(139,92,246,1); }
    .btn-buy { background: linear-gradient(45deg, #8b5cf6, #ec4899); }
    .btn-owned { background: linear-gradient(45deg, #10b981, #34d399); cursor: default; }
    .btn-disabled { background: #374151; opacity: 0.7; cursor: not-allowed; }

    @media (max-width:768px) { 
      .details-container{grid-template-columns:1fr;} 
      .btn-master{min-width:auto;width:100%;} 
    }

    /* ────────────────────── NOUVEAU MODAL PRO ────────────────────── */
    .modal-backdrop {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.65);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      animation: modalPop 0.5s ease-out forwards;
    }
    .modal-card {
      background: linear-gradient(135deg, rgba(30, 27, 75, 0.95), rgba(76, 29, 149, 0.92));
      padding: 3rem 2rem;
      border-radius: 2.5rem;
      box-shadow: 0 0 100px rgba(139, 92, 246, 0.8);
      border: 2px solid rgba(139, 92, 246, 0.5);
      text-align: center;
      max-width: 560px;
      width: 90%;
      font-family: 'Rajdhani', sans-serif;
    }
    .modal-card h2 {
      font-size: 5.5rem;
      margin: 0 0 1.5rem;
      color: #8b5cf6;
      text-shadow: 0 0 40px #8b5cf6;
    }
    .modal-card .game-title {
      font-size: 2.4rem;
      color: #e0d4ff;
      margin: 1rem 0;
    }
    .modal-card .price {
      font-size: 3rem;
      color: #00ffc3;
      margin: 1.5rem 0;
      text-shadow: 0 0 30px #00ffc3;
    }
    .modal-card .credits {
      font-size: 2rem;
      color: #fbbf24;
    }
    .modal-card .credits span { color: #fff; font-weight: 900; }

    .modal-actions {
      margin-top: 3rem;
      display: flex;
      gap: 2rem;
      justify-content: center;
      flex-wrap: wrap;
    }
    .btn-confirm {
      padding: 1.4rem 4rem;
      background: #10b981;
      border: none;
      border-radius: 60px;
      color: white;
      font-size: 1.8rem;
      font-weight: 900;
      cursor: pointer;
      box-shadow: 0 0 40px rgba(16, 185, 129, 0.6);
      transition: all 0.3s;
    }
    .btn-confirm:hover {
      transform: translateY(-5px);
      box-shadow: 0 0 60px rgba(16, 185, 129, 1);
    }
    .btn-cancel {
      padding: 1.2rem 3rem;
      background: rgba(107, 114, 128, 0.8);
      border: none;
      border-radius: 60px;
      color: white;
      font-size: 1.5rem;
      cursor: pointer;
      transition: all 0.3s;
    }
    .btn-cancel:hover {
      background: #ef4444;
    }

    @keyframes modalPop {
      from { transform: scale(0.7); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.7; }
    }
  </style>
</head>
<body>

<?php if (isset($_GET['show_history']) && isset($_SESSION['user'])): 
    require_once '../../controller/HistoriqueController.php';
    $histCtrl = new HistoriqueController();
    
    // Récupère TOUT l'historique de l'utilisateur (inscription incluse)
    $history = $histCtrl->getUserFullHistory($_SESSION['user']['id']);
    
    // Tri par date décroissante (le plus récent en haut)
    usort($history, function($a, $b) {
        return strtotime($b->getDateAction()) <=> strtotime($a->getDateAction());
    });
?>
<div class="history-overlay">
  <div class="history-modal">
    <div class="history-header">
      Historique d'activité
      <button class="close-modal" onclick="history.back()">×</button>
    </div>
    <div class="history-body">
      <?php if (empty($history)): ?>
        <p class="no-history">Aucune activité enregistrée pour le moment.</p>
      <?php else: ?>
        <?php foreach ($history as $item): ?>
          <div class="history-item <?= $item->getTypeAction() ?>">
            <div class="icon">
              <?php if ($item->getTypeAction() === 'purchase'): ?>
                <i class="bi bi-bag-check-fill"></i>
              <?php elseif ($item->getTypeAction() === 'login'): ?>
                <i class="bi bi-box-arrow-in-right"></i>
              <?php else: ?>
                <i class="bi bi-person-plus-fill"></i>
              <?php endif; ?>
            </div>
            <div>
              <strong>
                <?php 
                if ($item->getTypeAction() === 'purchase') {
                    echo htmlspecialchars($item->getDescription());
                } elseif ($item->getTypeAction() === 'login') {
                    echo 'Dernière connexion';
                } else {
                    echo 'Inscription sur NextGen';
                }
                ?>
              </strong><br>
              <small><?= date('d/m/Y à H\hi', strtotime($item->getDateAction())) ?></small>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>
 <header>
    <div class="container nav">
      <div class="left">
      <a href="index.php" class="logo">
        <img src="../../resources/nextgen.png" alt="NextGen Logo"style="height: 100px; width:auto;position:relative; left:-20%;!imprtant ">
      </a>
        
        <nav class="menu" style="position:absolute;left:170px; !important">
          <a href="index.php" class="active">Accueil</a>
          <a href="catalogue.php" >Produits</a>
          <a href="../livraison.php"><i class="bi bi-truck"></i> Livraison</a>
          <a href="apropos.html">À Propos</a>
        </nav>
      </div>

      <div style="display:flex; gap:1rem; align-items:center;">

        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
          <a style="position:absolute;right:130px;color:black; !important" href="../backoffice/admin_users.php" >
            <i class="bi bi-person-gear"></i> Administration
          </a>
        <?php endif; ?>

        <?php if (isset($_SESSION['user'])): 
          $photo = !empty($_SESSION['user']['photo_profil']) 
              ? '../../resources/' . $_SESSION['user']['photo_profil'] 
              : '../../resources/default.jpg';
        ?>
          <div class="user-dropdown">
            <button class="user-btn">
              <span class="user-name">Bienvenue <?= htmlspecialchars($_SESSION['user']['prenom']) ?></span>
              <img src="<?= $photo ?>" alt="Profil" class="user-avatar">
            </button>

            <div class="dropdown-menu">
              <a href="profil.php">
                <i class="bi bi-person-circle"></i> Gérer mon profil
              </a>
              <a href="../livraison.php">
                <i class="bi bi-truck"></i> Mes livraisons
              </a>
              <a href="index.php?show_history=1">
                <i class="bi bi-clock-history"></i> Historique d'activité
              </a>
              <hr>
              <a href="../backoffice/logout.php" class="logout-item">
                <i class="bi bi-box-arrow-right"></i> Déconnexion
              </a>
            </div>
          </div>

        <?php else: ?>
          <a href="connexion.php" style="color:#4f46e5; font-weight:600;">Connexion</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

 
<div class="container">
  <div class="details-container">
    <div>
      <img src="../../resources/<?= htmlspecialchars($jeu->getSrcImg()) ?>" alt="<?= htmlspecialchars($jeu->getTitre()) ?>" class="game-image">
    </div>

    <div class="game-info">
      <h1><?= htmlspecialchars($jeu->getTitre()) ?></h1>
      <div class="game-category">
        Catégorie : <?= htmlspecialchars($jeu->nom_categorie ?? 'Non classé') ?>
      </div>

      <div class="game-price">
        <?= number_format($jeu->getPrix(), 2) ?> TND
      </div>

      <?php if ($jeu->getDescription()): ?>
        <div class="game-description">
          <h3>Description</h3>
          <p><?= nl2br(htmlspecialchars($jeu->getDescription())) ?></p>
        </div>
      <?php endif; ?>

      <?php
      $isGame    = $jeu->nom_categorie && stripos($jeu->nom_categorie, 'jeu') !== false;
      $isOwned   = $jeu->isOwned ?? false;
      $userScore = $jeu->userScore ?? 0;
      $canBuy    = !$isGame || ($isGame && $actualCredits >= $jeu->getPrix());
      ?>

      <div style="margin:3rem 0; text-align:center;">
        <?php if ($isGame && $isOwned): ?>
          <div style="padding:2.5rem;background:rgba(0,255,195,0.2);border:3px solid #00ffc3;border-radius:2rem;display:inline-block;">
            <p style="font-size:3.5rem;color:#00ffc3;margin:0;font-weight:900;text-shadow:0 0 40px #00ffc3;">OWNED</p>
            <p style="color:white;font-size:2rem;margin:1.5rem 0;">
              Score: <strong style="color:#8b5cf6;"><?= $userScore ?></strong>
            </p>
            <<?php
                // Clean the title to make a safe and valid filename
                $gameTitle = $jeu->getTitre();
                // Convert to lowercase, replace spaces and special chars with hyphens
                $filename = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $gameTitle)));
                // Remove multiple hyphens and trim
                $filename = preg_replace('/-+/', '-', $filename);
                $filename = trim($filename, '-');

                // Optional: add .html extension
                $gameUrl = "../../games/{$filename}/{$filename}.html";
                ?>

                <a href="<?= $gameUrl ?>" class="btn-master btn-owned" target="_blank">
                  PLAY NOW
                </a>
          </div>

        <?php elseif ($canBuy): ?>
          <button id="buyBtn" class="btn-master btn-buy">
            <?= $isGame ? 'BUY WITH CREDITS' : 'ACHETER MAINTENANT' ?>
            <br><small style="font-size:0.9rem;opacity:0.9;">
              <?= $isGame ? number_format($jeu->getPrix(), 2).' TND • You have '.number_format($actualCredits, 2) : '' ?>
            </small>
          </button>

        <?php else: ?>
          <div style="display:inline-block;">
            <button class="btn-master btn-disabled" disabled>
              <?= $isGame ? 'NOT ENOUGH CREDITS' : 'ACHETER MAINTENANT' ?>
            </button>
            <?php if ($isGame): ?>
              <p style="color:#ff6b6b;margin:1.5rem 0 0;font-size:1.5rem;font-weight:700;">
                Need <strong><?= number_format($jeu->getPrix() - $actualCredits, 2) ?> TND</strong> more
              </p>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- MODAL DE CONFIRMATION (nouvelle version pro) -->
      <?php if ($isGame && $canBuy && !$isOwned): ?>
      <div id="confirmOverlay" class="modal-backdrop">
        <div class="modal-content">
          <div class="modal-card">
            <h2>BUY NOW?</h2>
            <p class="game-title"><?= htmlspecialchars($jeu->getTitre()) ?></p>
            <p class="price"><?= number_format($jeu->getPrix(), 2) ?> TND</p>
            <p class="credits">Vos crédits : <span><?= number_format($actualCredits, 2) ?> TND</span></p>

            <div class="modal-actions">
              <button id="confirmBuy" class="btn-confirm">YES, BUY IT</button>
              <button id="cancelBuy" class="btn-cancel">Cancel</button>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>



<script>
<?php if ($isGame && $canBuy && !$isOwned): ?>
  const overlay = document.getElementById('confirmOverlay');

  document.getElementById('buyBtn').onclick = () => overlay.style.display = 'flex';
  document.getElementById('cancelBuy').onclick = () => overlay.style.display = 'none';

  document.getElementById('confirmBuy').onclick = () => {
    fetch("./buy_game.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "id_jeu=<?= $jeu->getIdJeu() ?>"
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        overlay.innerHTML = `
          <div class="modal-content">
            <div class="modal-card" style="padding:5rem 3rem; text-align:center;">
              <h2 style="font-size:8rem; color:#10b981;">BOUGHT!</h2>
              <p style="font-size:3.5rem; color:white; margin:2rem 0;">${data.titre}</p>
              <p style="font-size:2rem; color:#a0aec0;">
                Crédits restants: <strong style="color:#00ffc3;">${data.credits} TND</strong>
              </p>
              <button onclick="location.reload()" 
                      style="margin-top:2rem; padding:1.8rem 6rem; background:#8b5cf6; border:none; border-radius:60px; color:white; font-size:2rem; cursor:pointer;">
                Continuer
              </button>
            </div>
          </div>`;
      } else {
        alert(data.message || "Erreur lors de l'achat");
        overlay.style.display = 'none';
      }
    })
    .catch(() => {
      alert("Erreur réseau");
      overlay.style.display = 'none';
    });
  };
<?php endif; ?>
</script>
</body>
</html>