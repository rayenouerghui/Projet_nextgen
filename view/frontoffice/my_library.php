<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../controller/JeuController.php';

if (!isset($_SESSION['user']['id'])) {
    header('Location: connexion.php');
    exit;
}

$controller = new JeuController();
$allGames = $controller->afficherJeux();
$ownedGames = array_filter($allGames, fn($jeu) => $jeu->isOwned);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ma Bibliothèque • NextGen</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <!-- NextGen Design System -->
  <link rel="stylesheet" href="../css/nextgen-design-system.css">
  
  <link rel="stylesheet" href="styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Rajdhani:wght@600;800&display=swap" rel="stylesheet">
  <style>
    body {
      background: url('../../resources/bg-jeux.gif') no-repeat center top fixed !important;
      background-size: cover !important;
      margin: 0; min-height: 100vh;
    }
    body::before {
      content:''; position:fixed; top:0; left:0; right:0; bottom:0;
      background:rgba(0,0,0,0.55); backdrop-filter:blur(8px);
      z-index:1; pointer-events:none;
    }
    header, .container { position:relative; z-index:2; }

    .library-title {
      text-align: center;
      font-size: 3.5rem;
      font-weight: 900;
      margin: 3rem 0 2rem;
      background: linear-gradient(90deg, #8b5cf6, #ec4899);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 0 0 30px rgba(139,92,246,0.5);
    }

    .games-count {
      text-align: center;
      color: #e0d4ff;
      font-size: 1.4rem;
      margin-bottom: 3rem;
    }

    .games-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 2.5rem;
      padding: 0 2rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    .game-card {
      background: linear-gradient(135deg, rgba(62,37,255,0.9), rgba(59,7,100,0.8));
      border-radius: 1.5rem;
      overflow: hidden;
      box-shadow: 0 15px 40px rgba(0,0,0,0.5);
      transition: all 0.4s ease;
      border: 1px solid rgba(139,92,246,0.6);
    }

    .game-card:hover {
      transform: translateY(-15px);
      box-shadow: 0 30px 60px rgba(139,92,246,0.7);
    }

    .game-card img {
      width: 100%;
      height: 220px;
      object-fit: cover;
    }

    .game-info {
      padding: 1.5rem;
      text-align: center;
    }

    .game-info h3 {
      color: white;
      font-size: 1.6rem;
      margin: 0 0 0.8rem;
      font-weight: 700;
    }

    .game-score {
      color: #00ffc3;
      font-size: 1.3rem;
      margin-bottom: 1.2rem;
      font-weight: 600;
    }

    .btn-play {
      display: inline-block;
      padding: 1rem 3rem;
      background: linear-gradient(45deg, #10b981, #34d399);
      color: white;
      font-weight: 900;
      text-transform: uppercase;
      border-radius: 60px;
      text-decoration: none;
      font-size: 1.3rem;
      box-shadow: 0 0 40px rgba(16,185,129,0.6);
      transition: all 0.3s;
    }

    .btn-play:hover {
      transform: translateY(-5px);
      box-shadow: 0 0 60px rgba(52,211,153,0.9);
    }

    .empty-library {
      text-align: center;
      padding: 6rem 2rem;
      color: #e0d4ff;
    }

    .empty-library i {
      font-size: 6rem;
      margin-bottom: 2rem;
      opacity: 0.6;
    }

    .empty-library h2 {
      font-size: 2.5rem;
      margin-bottom: 1rem;
    }

    .empty-library p {
      font-size: 1.3rem;
      opacity: 0.8;
    }

    .empty-library a {
      display: inline-block;
      margin-top: 2rem;
      padding: 1rem 3rem;
      background: linear-gradient(45deg, #8b5cf6, #ec4899);
      color: white;
      border-radius: 60px;
      text-decoration: none;
      font-weight: 700;
    }
  </style>
</head>
<body>

  <header>
        <div class="container nav">
          <div class="left">
            <a href="index.php" class="logo">NextGen</a>
            <nav class="menu">
              <a href="index.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">Accueil</a>
              <a href="catalogue.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'catalogue.php' || basename($_SERVER['PHP_SELF']) == 'catalogue_details.php') ? 'active' : '' ?>">Produits</a>
              <a href="../livraison.php"><i class="bi bi-truck"></i> Livraison</a>
              <a href="apropos.html">À Propos</a>
            </nav>
          </div>

          <div style="display:flex; gap:1rem; align-items:center;">

            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
              <a style="position:absolute;right:270px;" href="../backoffice/accueil.php">
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
                  <a href="my_library.php">
                    <i class="bi bi-collection-play"></i> Ma Bibliothèque
                  </a>
                  <a href="historique.php">
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
    <h1 class="library-title">Ma Bibliothèque</h1>
    <p class="games-count"><?= count($ownedGames) ?> jeu(x) dans votre collection</p>

    <?php if (empty($ownedGames)): ?>
      <div class="empty-library">
        <i class="bi bi-controller"></i>
        <h2>Vous n'avez aucun jeu pour le moment</h2>
        <p>Découvrez notre catalogue et achetez vos premiers jeux !</p>
        <a href="catalogue.php">Aller au catalogue</a>
      </div>
    <?php else: ?>
      <div class="games-grid">
        <?php foreach ($ownedGames as $jeu): ?>
          <?php
          $filename = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $jeu->getTitre()), '-'));
          $gameUrl = "../../games/{$filename}/{$filename}.html";
          ?>
          <div class="game-card">
            <img src="../../resources/<?= htmlspecialchars($jeu->getSrcImg()) ?>" alt="<?= htmlspecialchars($jeu->getTitre()) ?>">
            <div class="game-info">
              <h3><?= htmlspecialchars($jeu->getTitre()) ?></h3>
              <div class="game-score">Score : <?= $jeu->userScore ?></div>
              <a href="<?= $gameUrl ?>" target="_blank" class="btn-play">
                JOUER MAINTENANT
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <script>
  const userBtn = document.getElementById('userBtn');
  const dropdownMenu = document.getElementById('dropdownMenu');
  let timeout;

  // Quand on passe sur le bouton → affiche le menu
  userBtn.addEventListener('mouseenter', () => {
    clearTimeout(timeout);
    dropdownMenu.style.opacity = '1';
    dropdownMenu.style.visibility = 'visible';
    dropdownMenu.style.transform = 'translateY(0)';
  });

  // Quand on passe sur le menu → reste ouvert
  dropdownMenu.addEventListener('mouseenter', () => {
    clearTimeout(timeout);
  });

  // Quand on quitte le bouton OU le menu → cache après 300ms
  userBtn.addEventListener('mouseleave', () => {
    timeout = setTimeout(() => {
      dropdownMenu.style.opacity = '0';
      dropdownMenu.style.visibility = 'hidden';
      dropdownMenu.style.transform = 'translateY(-10px)';
    }, 300);
  });

  dropdownMenu.addEventListener('mouseleave', () => {
    timeout = setTimeout(() => {
      dropdownMenu.style.opacity = '0';
      dropdownMenu.style.visibility = 'hidden';
      dropdownMenu.style.transform = 'translateY(-10px)';
    }, 300);
  });
</script>

</body>
</html>