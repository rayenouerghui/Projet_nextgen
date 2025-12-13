<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../controller/jeuController.php';
$controller = new JeuController();
$jeux = $controller->afficherJeux();

// Get all unique categories
$categories = [];
foreach ($jeux as $jeu) {
    $cat = $jeu->nom_categorie ?? '';
    if ($cat && !in_array($cat, $categories)) {
        $categories[] = $cat;
    }
}

// Get filter values
$search = trim($_GET['q'] ?? '');
$selectedCategory = $_GET['category'] ?? '';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 1000;
$sortBy = $_GET['sort_by'] ?? 'name';
$sortOrder = $_GET['sort_order'] ?? 'asc';

// Filter and sort games
$filteredJeux = [];
foreach ($jeux as $jeu) {
    // Search filter
    if ($search !== '' && stripos($jeu->getTitre(), $search) === false) continue;
    
    // Category filter
    if ($selectedCategory !== '' && $jeu->nom_categorie !== $selectedCategory) continue;
    
    // Price filter
    $prix = $jeu->getPrix();
    if ($prix < $minPrice || $prix > $maxPrice) continue;
    
    $filteredJeux[] = $jeu;
}

// Sort games
usort($filteredJeux, function($a, $b) use ($sortBy, $sortOrder) {
    if ($sortBy === 'price') {
        $compare = $a->getPrix() <=> $b->getPrix();
    } else {
        $compare = strcasecmp($a->getTitre(), $b->getTitre());
    }
    return $sortOrder === 'desc' ? -$compare : $compare;
});

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>NextGen – Catalogue</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Rajdhani:wght@600;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Exo+2:wght@500;700&display=swap" rel="stylesheet">

  <style>
    .user-dropdown { position: absolute; right:40px; display: inline-block; }
    .user-btn { background: none; border: none; display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 8px 14px; border-radius: 50px; transition: all 0.3s ease; font-family: 'Inter', sans-serif; }
    .user-btn:hover { background: rgba(79, 70, 229, 0.1); }
    .user-name { color: #4f46e5; font-weight: 600; font-size: 0.95rem; }
    .user-avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 3px solid #4f46e5; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.25); }
    .dropdown-menu { position: absolute; top: 100%; right: 0; background: white; min-width: 230px; border-radius: 14px; box-shadow: 0 12px 40px rgba(0,0,0,0.18); opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.3s ease; z-index: 1000; overflow: hidden; margin-top: 10px; }
    .user-dropdown:hover .dropdown-menu { opacity: 1; visibility: visible; transform: translateY(0); }
    .dropdown-menu a { display: flex; align-items: center; gap: 12px; padding: 14px 20px; color: #374151; font-size: 0.95rem; transition: all 0.2s; text-decoration: none; }
    .dropdown-menu a:hover { background: #f8f9ff; color: #4f46e5; padding-left: 26px; }
    .dropdown-menu hr { margin: 8px 0; border: none; border-top: 1px solid #e5e7eb; }
    .logout-item { color: #ef4444 !important; }
    .logout-item:hover { background: #fef2f2 !important; color: #dc2626 !important; }

    html, body {
      min-height: 100%;
      height: 100%;
    }
    *{font-family:'Exo 2',sans-serif;}
    body {
      
      background: url('../../resources/bg-jeux.gif') no-repeat center top fixed !important;
      background-size: cover !important;
      background-attachment: fixed !important;
      position: relative;
      margin: 0;
      padding: 0;
      
    }

    body::before {
      content: '';
      position: fixed !important;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.55);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      z-index: 1;
      pointer-events: none;
    }

    header, .catalogue, footer {
      position: relative;
      z-index: 2;
    }

    .catalogue h1 {
      color: white !important;
      font-size: 3rem;
      text-shadow: 0 0 20px rgba(255,255,255,0.5);
    }

    .game-card {
      background: linear-gradient(135deg, rgba(62, 37, 255, 0.9), rgba(59, 7, 100, 0.8));
      border-radius: 1.5rem;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
      transition: all 0.4s ease;
      border: 1px solid rgba(139, 92, 246, 0.6);
    }

    .game-card:hover {
      transform: translateY(-15px);
      box-shadow: 0 25px 50px rgba(139, 92, 246, 0.6);
    }

    .game-card img {
      width: 100%;
      height: 220px;
      object-fit: cover;
    }

    .game-card h3 {
      color: white;
      font-size: 1.5rem;
      padding: 1rem;
      font-weight: 700;
    }

    .game-card p:not(.price) {
      color: #e0d4ff;
      padding: 0 1rem;
      font-size: 1.1rem;
    }

    .price {
      color: #00ffc3;
      font-size: 2rem;
      font-weight: 900;
      padding: 0.5rem 1rem;
      text-shadow: 0 0 20px #00ffc3;
    }

    .btn-buy {
      display: block;
      margin: 1rem;
      padding: 1rem;
      background: linear-gradient(45deg, #8b5cf6, #ec4899);
      color: white;
      text-align: center;
      border-radius: 50px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      box-shadow: 0 0 25px rgba(139, 92, 246, 0.6);
      transition: all 0.3s;
    }

    .btn-buy:hover {
      background: linear-gradient(45deg, #d946ef, #f43f5e);
      transform: translateY(-5px);
      box-shadow: 0 0 40px rgba(236, 72, 153, 0.8);
    }

    .user-dropdown {
      z-index: 9999 !important;
    }
    .dropdown-menu {
      z-index: 9999 !important;
    }
    header {
      z-index: 1000;
    }

    .catalogue {
      padding: 4rem 0;
    }

    .catalogue .container {
      max-width: 1400px;
    }

    .catalogue h1 {
      font-size: 2rem !important;
      font-weight: 700;
      letter-spacing: 2px;
      background: linear-gradient(90deg, #8b5cf6, #ec4899);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      text-shadow: 0 0 30px rgba(139, 92, 246, 0.5);
      margin-bottom: 2rem;
    }

    .search-bar input {
      width: 100%;
      max-width: 500px;
      padding: 1rem 1.5rem;
      border-radius: 50px;
      border: 2px solid rgba(139, 92, 246, 0.4);
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(12px);
      color: white;
      font-size: 1.1rem;
      box-shadow: 0 8px 32px rgba(0,0,0,0.3);
      transition: all 0.3s ease;
    }

    .search-bar input:focus {
      outline: none;
      border-color: #ec4899;
      box-shadow: 0 0 30px rgba(236, 72, 153, 0.5);
      transform: scale(1.02);
    }

    .search-bar input::placeholder {
      color: rgba(255,255,255,0.7);
    }

    .games-grid {
      justify-content: center;
      gap: 2.5rem;
      padding: 0 1rem;
    }

    .game-card {
      box-shadow: 0 15px 40px rgba(0,0,0,0.4);
    }

    .media-wrapper {
      position: relative;
      width: 100%;
      height: 220px;
      overflow: hidden;
      border-radius: 1.5rem 1.5rem 0 0;
    }

    .game-media {
      width: 100%;
      height: 100%;
      object-fit: cover;
      position: absolute;
      top: 0;
      left: 0;
      transition: opacity 0.4s ease;
    }

    .img-active {
      opacity: 1;
      z-index: 2;
    }

    .video-hover {
      opacity: 0;
      z-index: 1;
    }

    .game-card:hover .img-active {
      opacity: 0;
    }

    .game-card:hover .video-hover {
      opacity: 1;
      transform: scale(1.15);
      transform-origin: center center;
    }

    /* FILTER SIDEBAR STYLES */
    .catalogue-wrapper {
      display: flex;
      gap: 2rem;
      align-items: flex-start;
      position: relative;
    }

    .filter-sidebar {
      position: fixed;
      left: -370px;
      top: 90px;
      width: 320px;
      background: linear-gradient(135deg, rgba(62, 37, 255, 0.95), rgba(59, 7, 100, 0.9));
      border-radius: 1.5rem;
      padding: 2rem;
      box-shadow: 0 15px 40px rgba(0,0,0,0.5);
      border: 1px solid rgba(139, 92, 246, 0.6);
      backdrop-filter: blur(12px);
      z-index: 100;
      transition: left 0.4s ease;
      max-height: calc(100vh - 120px);
      overflow-y: auto;
    }
    
    .filter-sidebar.active {
      left: 20px;
    }
    
    .filter-sidebar::-webkit-scrollbar {
      width: 6px;
    }
    
    .filter-sidebar::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 10px;
    }
    
    .filter-sidebar::-webkit-scrollbar-thumb {
      background: linear-gradient(180deg, #8b5cf6, #ec4899);
      border-radius: 10px;
    }
    
    .filter-toggle-btn {
      position: fixed;
      left: 20px;
      top: 90px;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: linear-gradient(135deg, #8b5cf6, #ec4899);
      border: none;
      color: white;
      font-size: 1.5rem;
      cursor: pointer;
      box-shadow: 0 8px 25px rgba(139, 92, 246, 0.6);
      z-index: 99;
      transition: all 0.4s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .filter-toggle-btn:hover {
      transform: scale(1.1);
      box-shadow: 0 12px 35px rgba(236, 72, 153, 0.8);
    }
    
    .filter-toggle-btn.active {
      left: 360px;
    }
    
    .filter-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 98;
      opacity: 0;
      visibility: hidden;
      transition: all 0.4s ease;
    }
    
    .filter-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .filter-sidebar h2 {
      color: white;
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .filter-section {
      margin-bottom: 2rem;
    }

    .filter-section label {
      display: block;
      color: #e0d4ff;
      font-weight: 600;
      margin-bottom: 0.8rem;
      font-size: 1rem;
    }

    .filter-section select,
    .filter-section input[type="number"],
    .filter-section input[type="search"] {
      width: 100%;
      padding: 0.8rem;
      border-radius: 10px;
      border: 2px solid rgba(139, 92, 246, 0.4);
      background: rgba(255, 255, 255, 0.1);
      color: white;
      font-size: 1rem;
      transition: all 0.3s;
    }

    .filter-section select:focus,
    .filter-section input[type="number"]:focus,
    .filter-section input[type="search"]:focus {
      outline: none;
      border-color: #ec4899;
      box-shadow: 0 0 15px rgba(236, 72, 153, 0.4);
    }

    .filter-section select option {
      background: #1a1a2e;
      color: white;
    }

    /* Price Range Slider */
    .price-inputs {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .price-inputs input {
      padding: 0.6rem;
      font-size: 0.9rem;
    }

    input[type="range"] {
      width: 100%;
      height: 6px;
      border-radius: 5px;
      background: rgba(255, 255, 255, 0.2);
      outline: none;
      -webkit-appearance: none;
      margin: 1rem 0;
    }

    input[type="range"]::-webkit-slider-thumb {
      -webkit-appearance: none;
      appearance: none;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: linear-gradient(45deg, #8b5cf6, #ec4899);
      cursor: pointer;
      box-shadow: 0 0 10px rgba(139, 92, 246, 0.8);
    }

    input[type="range"]::-moz-range-thumb {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: linear-gradient(45deg, #8b5cf6, #ec4899);
      cursor: pointer;
      box-shadow: 0 0 10px rgba(139, 92, 246, 0.8);
      border: none;
    }

    .range-values {
      display: flex;
      justify-content: space-between;
      color: #00ffc3;
      font-weight: 600;
      font-size: 0.9rem;
    }

    /* Sort Options */
    .sort-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    /* Filter Buttons */
    .filter-actions {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
    }

    .btn-filter {
      flex: 1;
      padding: 0.8rem;
      border-radius: 50px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      border: none;
      cursor: pointer;
      transition: all 0.3s;
      font-size: 0.9rem;
    }

    .btn-apply {
      background: linear-gradient(45deg, #8b5cf6, #ec4899);
      color: white;
      box-shadow: 0 0 20px rgba(139, 92, 246, 0.6);
    }

    .btn-apply:hover {
      transform: translateY(-2px);
      box-shadow: 0 0 30px rgba(236, 72, 153, 0.8);
    }

    .btn-reset {
      background: rgba(255, 255, 255, 0.1);
      color: white;
      border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .btn-reset:hover {
      background: rgba(255, 255, 255, 0.2);
      border-color: rgba(255, 255, 255, 0.5);
    }

    .catalogue-content {
      flex: 1;
      width: 100%;
      transition: all 0.4s ease;
    }
    
    .content-title {
      font-size: 1.8rem !important;
      font-weight: 700;
      letter-spacing: 2px;
      background: linear-gradient(90deg, #8b5cf6, #ec4899);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      text-shadow: 0 0 30px rgba(139, 92, 246, 0.5);
      margin-bottom: 1.5rem;
      text-align: center;
      color: white !important;
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .results-count {
      color: #e0d4ff;
      font-size: 1.1rem;
      font-weight: 600;
    }

    @media (max-width: 1024px) {
      .catalogue-wrapper {
        flex-direction: column;
      }

      .filter-sidebar {
        position: fixed;
        left: -370px;
        top: 90px;
        width: 90%;
        max-width: 320px;
      }
      
      .filter-sidebar.active {
        left: 5%;
      }
      
      .filter-toggle-btn.active {
        left: calc(90% + 20px);
      }
      
      .catalogue-content {
        margin-left: 0;
      }
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
  <!-- HEADER -->
  <header>
  <div class="container nav">
    <div class="left">
      <a href="index.php" class="logo">
        <img src="../../resources/nextgen.png" alt="NextGen Logo"style="height: 100px; width:auto;position:relative; left:-20%;!imprtant ">
      </a>
      <nav class="menu">
        <a href="index.php">Accueil</a>
        <a href="catalogue.php" class="active">Produits</a>
        <a href="../livraison.php"><i class="bi bi-truck"></i> Livraison</a>
        <a href="apropos.html">À Propos</a>
      </nav>
    </div>

    <div style="display:flex; gap:1rem; align-items:center;">
      <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
        <a style="position:absolute;right:270px;" href="../backoffice/admin_users.php">
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
            <a href="profil.php"><i class="bi bi-person-circle"></i> Gérer mon profil</a>
            <a href="../livraison.php"><i class="bi bi-truck"></i> Mes livraisons</a>
            <a href="index.php?show_history=1"><i class="bi bi-clock-history"></i> Historique d'activité</a>
            <hr>
            <a href="../backoffice/logout.php" class="logout-item"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
          </div>
        </div>
      <?php else: ?>
        <a href="connexion.php" style="color:#4f46e5; font-weight:600;">Connexion</a>
      <?php endif; ?>
    </div>
  </div>
</header>


  <section class="catalogue">
    <div class="container">
      <!-- Filter Toggle Button -->
      <button class="filter-toggle-btn" id="filterToggle">
        <i class="bi bi-funnel"></i>
      </button>
      
      <!-- Filter Overlay -->
      <div class="filter-overlay" id="filterOverlay"></div>
      
      <div class="catalogue-wrapper">
        <!-- FILTER SIDEBAR -->
        <aside class="filter-sidebar">
          <h2><i class="bi bi-funnel"></i> Filtres</h2>
          
          <form method="GET" id="filterForm">
            <!-- Search -->
            <div class="filter-section">
              <label for="search"><i class="bi bi-search"></i> Rechercher</label>
              <input type="search" name="q" id="search" placeholder="Nom du jeu..." value="<?= htmlspecialchars($search) ?>">
            </div>

            <!-- Category -->
            <div class="filter-section">
              <label for="category"><i class="bi bi-tag"></i> Catégorie</label>
              <select name="category" id="category">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= htmlspecialchars($cat) ?>" <?= $selectedCategory === $cat ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Price Range -->
            <div class="filter-section">
              <label><i class="bi bi-currency-dollar"></i> Plage de prix</label>
              <div class="price-inputs">
                <input type="number" name="min_price" id="minPrice" placeholder="Min" value="<?= $minPrice ?>" min="0" step="1">
                <input type="number" name="max_price" id="maxPrice" placeholder="Max" value="<?= $maxPrice ?>" min="0" step="1">
              </div>
              <input type="range" id="priceRange" min="0" max="1000" step="1" value="<?= $maxPrice ?>">
              <div class="range-values">
                <span><?= $minPrice ?> TND</span>
                <span><?= $maxPrice ?> TND</span>
              </div>
            </div>

            <!-- Sort Options -->
            <div class="filter-section">
              <label><i class="bi bi-sort-down"></i> Trier par</label>
              <div class="sort-grid">
                <select name="sort_by" id="sortBy">
                  <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>>Nom</option>
                  <option value="price" <?= $sortBy === 'price' ? 'selected' : '' ?>>Prix</option>
                </select>
                
                <select name="sort_order" id="sortOrder">
                  <option value="asc" <?= $sortOrder === 'asc' ? 'selected' : '' ?>>Croissant</option>
                  <option value="desc" <?= $sortOrder === 'desc' ? 'selected' : '' ?>>Décroissant</option>
                </select>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="filter-actions">
              <button type="submit" class="btn-filter btn-apply">
                <i class="bi bi-check-circle"></i> Appliquer
              </button>
              <button type="button" class="btn-filter btn-reset" onclick="window.location.href='catalogue.php'">
                <i class="bi bi-x-circle"></i> Réinitialiser
              </button>
            </div>
          </form>
        </aside>

        <!-- GAMES CONTENT -->
        <div class="catalogue-content">
          <h2 class="content-title">Liste des produits</h2>
          
          <div class="top-bar">
            <div class="results-count">
              <i class="bi bi-grid"></i> <?= count($filteredJeux) ?> jeu(x) trouvé(s)
            </div>
          </div>

          <div class="games-grid">
            <?php foreach ($filteredJeux as $jeu): ?>
              <div class="game-card">
                <div class="media-wrapper">
                  <img src="../../resources/<?= htmlspecialchars($jeu->getSrcImg()) ?>" alt="<?= htmlspecialchars($jeu->getTitre()) ?>" class="game-media img-active">

                  <?php if ($jeu->getVideoSrc()): ?>
                    <video src="../../resources/<?= htmlspecialchars($jeu->getVideoSrc()) ?>" 
                          class="game-media video-hover" 
                          muted loop playsinline 
                          preload="metadata"></video>
                  <?php endif; ?>
                </div>

                <h3><?= htmlspecialchars($jeu->getTitre()) ?></h3>
                <p><?= htmlspecialchars($jeu->nom_categorie ?? '') ?></p>
                <p class="price" style="color:white !important;"><?= number_format($jeu->getPrix(), 2) ?> TND</p>
                <a href="catalogue_details.php?id=<?= $jeu->getIdJeu() ?>" class="btn-buy">Détails</a>
              </div>
            <?php endforeach; ?>

            <?php if (empty($filteredJeux)): ?>
              <p style="color: white; text-align: center; width: 100%; font-size: 1.2rem; padding: 3rem;">
                <i class="bi bi-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                Aucun jeu ne correspond à vos critères de recherche.
              </p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <script>
    // Filter toggle functionality
    const filterToggle = document.getElementById('filterToggle');
    const filterSidebar = document.querySelector('.filter-sidebar');
    const filterOverlay = document.getElementById('filterOverlay');
    
    filterToggle.addEventListener('click', () => {
      filterSidebar.classList.toggle('active');
      filterToggle.classList.toggle('active');
      filterOverlay.classList.toggle('active');
    });
    
    filterOverlay.addEventListener('click', () => {
      filterSidebar.classList.remove('active');
      filterToggle.classList.remove('active');
      filterOverlay.classList.remove('active');
    });
    
    // Video hover effect
    document.querySelectorAll('.game-card').forEach(card => {
      const video = card.querySelector('.video-hover');
      if (!video) return;

      card.addEventListener('mouseenter', () => {
        video.play().catch(() => {});
      });

      card.addEventListener('mouseleave', () => {
        video.pause();
        video.currentTime = 0;
      });
    });

    // Price range slider
    const priceRange = document.getElementById('priceRange');
    const maxPriceInput = document.getElementById('maxPrice');
    const rangeValues = document.querySelector('.range-values');

    priceRange.addEventListener('input', function() {
      maxPriceInput.value = this.value;
      updateRangeDisplay();
    });

    maxPriceInput.addEventListener('input', function() {
      priceRange.value = this.value;
      updateRangeDisplay();
    });

    function updateRangeDisplay() {
      const min = document.getElementById('minPrice').value || 0;
      const max = maxPriceInput.value || 1000;
      rangeValues.innerHTML = `<span>${min} TND</span><span>${max} TND</span>`;
    }
  </script>
</body>
</html>