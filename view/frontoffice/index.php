<?php 
session_start(); 

require_once '../../controller/jeuController.php';
require_once '../../controller/userController.php';

$jeuController = new JeuController();
$userController = new userController();

$totalJeux   = count($jeuController->afficherJeux());
$totalUsers  = count($userController->getAllUsers());
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>NextGen – Accueil</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Exo+2:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="styles.css">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <style>
    :root {
      --primary: #8b5cf6;
      --secondary: #ec4899;
      --accent: #06b6d4;
      --dark: #0f0c29;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      background:#000;
      color:#e0e7ff;
      font-family:'Exo 2',sans-serif;
      overflow-x:hidden;
    }
    .glass{
      background:rgba(255,255,255,0.08);
      backdrop-filter:blur(16px);
      border:1px solid rgba(139,92,246,0.3);
      box-shadow:0 8px 32px rgba(0,0,0,0.5);
    }
    .neon-text{ text-shadow:0 0 40px var(--primary); }
    .btn-neon{
      background:linear-gradient(45deg,var(--primary),var(--secondary));
      padding:16px 40px;
      border-radius:50px;
      font-weight:bold;
      color:white;
      transition:all .4s;
      box-shadow:0 0 30px rgba(139,92,246,.6);
    }
    .btn-neon:hover{
      transform:translateY(-8px);
      box-shadow:0 0 50px rgba(139,92,246,.9);
    }

    /* ==== HEADER & DROPDOWN ==== */
    .user-dropdown{position:absolute;right:40px;display:inline-block;}
    .user-btn{background:none;border:none;display:flex;align-items:center;gap:12px;cursor:pointer;padding:8px 14px;border-radius:50px;transition:all .3s;font-family:'Inter',sans-serif;}
    .user-btn:hover{background:rgba(79,70,229,.1);}
    .user-name{color:#4f46e5;font-weight:600;font-size:.95rem;}
    .user-avatar{width:42px;height:42px;border-radius:50%;object-fit:cover;border:3px solid #4f46e5;box-shadow:0 4px 15px rgba(79,70,229,.25);}
    .dropdown-menu{position:absolute;top:100%;right:0;background:white;min-width:230px;border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.18);opacity:0;visibility:hidden;transform:translateY(-10px);transition:all .3s;z-index:1000;overflow:hidden;margin-top:10px;}
    .user-dropdown:hover .dropdown-menu{opacity:1;visibility:visible;transform:translateY(0);}
    .dropdown-menu a{display:flex;align-items:center;gap:12px;padding:14px 20px;color:#374151;font-size:.95rem;transition:all .2s;text-decoration:none;}
    .dropdown-menu a:hover{background:#f8f9ff;color:#4f46e5;padding-left:26px;}
    .dropdown-menu hr{margin:8px 0;border:none;border-top:1px solid #e5e7eb;}
    .logout-item{color:#ef4444!important;}
    .logout-item:hover{background:#fef2f2!important;color:#dc2626!important;}

    /* ==== HERO ==== */
    .hero{
      min-height:100vh;
      background:url('../../resources/gamer-ezgif.com-added-text.gif') center/cover no-repeat fixed;
      display:flex;align-items:center;justify-content:center;text-align:center;
      position:relative;
    }
    .hero::before{
      content:'';position:absolute;inset:0;background:rgba(0,0,0,.55);z-index:1;
    }
    .hero>div{position:relative;z-index:2;padding:0 20px;}
    .hero h1{
      font-family:'Orbitron',sans-serif;
      font-size:5.5rem;
      font-weight:900;
      background:linear-gradient(90deg,var(--primary),var(--secondary),var(--accent),var(--primary));
      background-size:300%;
      -webkit-background-clip:text;
      background-clip:text;
      -webkit-text-fill-color:transparent;
      text-shadow:0 0 60px rgba(139,92,246,.8);
      animation:glow 4s ease-in-out infinite;
      margin-bottom:20px;
    }
    .hero p{font-size:2rem;margin-bottom:40px;}
    @keyframes glow{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}

    /* ==== SECTIONS ==== */
    .section-title{
      font-family:'Orbitron',sans-serif;
      font-size:4.5rem;
      text-align:center;
      margin:120px 0 80px;
      background:linear-gradient(to right,var(--primary),var(--secondary));
      -webkit-background-clip:text;
      -webkit-text-fill-color:transparent;
    }
    .impact-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
      gap:50px;
      max-width:1400px;
      margin:0 auto;
      padding:0 40px;
    }
    .impact-card{
      background:rgba(15,12,41,.9);
      border-radius:32px;
      padding:50px 30px;
      text-align:center;
      border:2px solid transparent;
      transition:all .6s;
    }
    .impact-card:hover{
      border-color:var(--primary);
      transform:translateY(-20px);
      box-shadow:0 0 60px rgba(139,92,246,.4);
    }
    .impact-card .num{
      font-size:5rem;
      font-weight:900;
      background:linear-gradient(to bottom,#a78bfa,#ec4899);
      -webkit-background-clip:text;
      -webkit-text-fill-color:transparent;
    }
    .impact-card .label{
      font-size:1.6rem;
      margin-top:16px;
      color:#c4b5fd;
    }

    .mission{
      padding:180px 40px;
      text-align:center;
      background:linear-gradient(135deg,#1a173a,#0f0c29);
    }
    .mission-grid{
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(420px,1fr));
      gap:60px;
      max-width:1400px;
      margin:0 auto;
    }
    .mission-card{
      background:rgba(255,255,255,0.06);
      backdrop-filter:blur(12px);
      border-radius:28px;
      padding:40px;
      border:1px solid rgba(139,92,246,0.2);
      transition:all .5s;
    }
    .mission-card:hover{
      transform:scale(1.05);
      box-shadow:0 0 50px rgba(139,92,246,.3);
    }
    .mission-card img{
      width:140px;
      height:140px;
      border-radius:24px;
      object-fit:cover;
      margin-bottom:24px;
      box-shadow:0 10px 30px rgba(0,0,0,.6);
    }

    footer{
      background:rgba(10,8,35,.98);
      padding:120px 40px 60px;
      text-align:center;
      border-top:1px solid rgba(139,92,246,.3);
    }
    footer h3{
      font-size:2.8rem;
      background:linear-gradient(to right,var(--primary),var(--secondary));
      -webkit-background-clip:text;
      -webkit-text-fill-color:transparent;
      margin-bottom:30px;
    }
    footer a{color:#a78bfa;transition:.3s;}
    footer a:hover{color:#ec4899;text-shadow:0 0 15px #ec4899;padding-left:8px;}
  </style>
</head>
<body>

<?php if (isset($_GET['show_history']) && isset($_SESSION['user'])): 
    require_once '../../controller/HistoriqueController.php';
    $histCtrl = new HistoriqueController();
    $history = $histCtrl->getUserFullHistory($_SESSION['user']['id']);
    usort($history, fn($a,$b) => strtotime($b->getDateAction()) <=> strtotime($a->getDateAction()));
?>
<div style="position:fixed;inset:0;background:rgba(0,0,0,.75);backdrop-filter:blur(14px);z-index:9999;display:flex;align-items:center;justify-content:center;">
  <div style="background:#0f0c29;width:90%;max-width:580px;border-radius:28px;overflow:hidden;box-shadow:0 30px 90px rgba(139,92,246,.5);border:1px solid rgba(139,92,246,.4);">
    <div style="background:linear-gradient(135deg,#8b5cf6,#ec4899);color:white;padding:26px;text-align:center;font-size:1.7rem;font-weight:800;position:relative;">
      Historique d'activité
      <button onclick="history.back()" style="position:absolute;top:50%;right:26px;transform:translateY(-50%);background:rgba(255,255,255,.25);border:none;width:46px;height:46px;border-radius:50%;color:white;font-size:2rem;cursor:pointer;">×</button>
    </div>
    <div style="padding:34px;max-height:68vh;overflow-y:auto;background:#1a173a;">
      <?php if (empty($history)): ?>
        <p style="text-align:center;color:#94a3b8;font-style:italic;padding:50px 20px;">Aucune activité enregistrée pour le moment.</p>
      <?php else: foreach ($history as $item): ?>
        <div style="display:flex;align-items:center;gap:18px;padding:16px 0;border-bottom:1px solid rgba(139,92,246,.2);color:#e0e7ff;">
          <div style="width:54px;height:54px;border-radius:16px;display:grid;place-items:center;font-size:1.6rem;flex-shrink:0;background:<?= $item->getTypeAction()==='purchase'?'rgba(16,185,129,.2)':'rgba(99,102,241,.2)' ?>;color:<?= $item->getTypeAction()==='purchase'?'#10b981':'#6366f1' ?>;">
            <?= $item->getTypeAction()==='purchase'?'<i class="bi bi-bag-check-fill"></i>':($item->getTypeAction()==='login'?'<i class="bi bi-box-arrow-in-right"></i>':'<i class="bi bi-person-plus-fill"></i>') ?>
          </div>
          <div>
            <strong>
              <?= $item->getTypeAction()==='purchase' ? htmlspecialchars($item->getDescription()) : ($item->getTypeAction()==='login' ? 'Dernière connexion' : 'Inscription sur NextGen') ?>
            </strong><br>
            <small><?= date('d/m/Y à H\hi', strtotime($item->getDateAction())) ?></small>
          </div>
        </div>
      <?php endforeach; endif; ?>
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
        
        <nav class="menu" style="position:absolute;left:300px; !important">
          <a href="index.php" class="active">Accueil</a>
          <a href="catalogue.php" >Produits</a>
          <a href="../livraison.php"><i class="bi bi-truck"></i> Livraison</a>
          <a href="apropos.html">À Propos</a>
        </nav>
      </div>

      <div style="display:flex; gap:1rem; align-items:center;">

        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
          <a style="position:absolute;right:270px;color:black; !important" href="../backoffice/admin_users.php" >
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
                  <a href="reclamation.php">
                    <i class="bi bi-exclamation-triangle"></i> Passer une réclamations
                  </a>
                  <a href="mes_reclamations.php">
                    <i class="fas fa-exclamation-triangle"></i> Mes réclamations
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


<section class="hero">
  <div class="container" style="position:relative;">
    <h1>Bienvenue sur NextGen</h1>
    <p>Jouer pour Espérer</p>
    <div>
      <a href="catalogue.php" class="btn btn-primary">Voir le Catalogue</a>
      <a href="catalogue.php" class="btn btn-secondary">En Savoir Plus</a>
    </div>
  </div>
</section>

<section class="py-32">
  <h2 class="section-title" data-aos="fade-up">NOTRE IMPACT</h2>
  <div class="impact-grid">
    <div class="impact-card glass" data-aos="zoom-in" data-aos-delay="100">
      <div class="num"><?= $totalJeux ?>+</div>
      <div class="label">Jeux Disponibles</div>
    </div>
    <div class="impact-card glass" data-aos="zoom-in" data-aos-delay="300">
      <div class="num"><?= $totalUsers ?>+</div>
      <div class="label">Joueurs Solidaires</div>
    </div>
    <div class="impact-card glass" data-aos="zoom-in" data-aos-delay="500">
      <div class="num">127</div>
      <div class="label">Enfants aidés ce mois-ci</div>
    </div>
  </div>
</section>

<section class="mission">
  <h2 class="section-title" data-aos="fade-up">CHAQUE PARTIE COMPTE</h2>
  <div class="mission-grid">
    <div class="mission-card glass" data-aos="fade-right">
      <img src="https://cdn.nawaat.org/wp-content/uploads/2022/09/sos-gammarth-feat.jpg" alt="Enfant heureux">
      <h3 class="text-2xl font-bold mb-4 neon-text">Des sourires d’enfants</h3>
      <p>30% de chaque achat va directement aux orphelinats et centres d’accueil.</p>
    </div>
    <div class="mission-card glass" data-aos="fade-up" data-aos-delay="200">
      <img src="https://hips.hearstapps.com/hmg-prod/images/things-you-must-do-before-giving-a-child-a-games-console-1631118183.jpg?crop=1.00xw:0.751xh;0,0.122xh&resize=980:*" alt="Gaming & fun">
      <h3 class="text-2xl font-bold mb-4 neon-text">Du fun qui change des vies</h3>
      <p>Tes parties financent des consoles, des jeux et des ateliers pour les enfants sans parents.</p>
    </div>
    <div class="mission-card glass" data-aos="fade-left">
      <img src="https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="Don">
      <h3 class="text-2xl font-bold mb-4 neon-text">Zéro intermédiaire</h3>
      <p>100% des dons arrivent aux associations partenaires. Transparence totale.</p>
    </div>
  </div>
</section>

<footer>
  <h3>NextGen</h3>
  <p>Plateforme de vente de jeux vidéo à vocation solidaire</p>
  <p style="margin-top:40px;opacity:0.7;">© 2025 NextGen. Tous droits réservés.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  AOS.init({ once:true, duration:1000, easing:'ease-out-cubic' });

  document.addEventListener('DOMContentLoaded', () => {
    const toastElList = document.querySelectorAll('.toast');
    toastElList.forEach(toastEl => {
      const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
      toast.show();
    });
  });

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') history.back();
  });
</script>
</body>
</html>