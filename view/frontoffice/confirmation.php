<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit;
}

require_once '../../controller/jeuController.php';
require_once '../../controller/LivraisonController.php';

$jeuController = new JeuController();
$livraisonController = new LivraisonController();

$id_jeu = (int)($_POST['id_jeu'] ?? $_GET['id'] ?? 0);
$jeu = $jeuController->getJeu($id_jeu);

if (!$jeu || empty($_SESSION['temp_adresse'])) {
    header('Location: catalogue.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode_paiement'])) {
    $_SESSION['temp_mode_paiement'] = $_POST['mode_paiement'];
}

$mode_paiement = $_SESSION['temp_mode_paiement'] ?? 'credit_site';

$total = $jeu->getPrix() + 8.000;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_commande'])) {
    $livraison = new Livraison(
        $_SESSION['user']['id'],
        $id_jeu,
        $_SESSION['temp_adresse'],
        $_SESSION['temp_lat'],
        $_SESSION['temp_lng'],
        $mode_paiement  
    );

    if ($livraisonController->createLivraison($livraison)) {
        $id_livraison = $livraisonController->getPdo()->lastInsertId();
        unset($_SESSION['temp_adresse'], $_SESSION['temp_lat'], $_SESSION['temp_lng'], $_SESSION['temp_mode_paiement']);
        header("Location: tracking.php?id_livraison=$id_livraison");
        exit;
    } else {
        $error = "Erreur lors de la création de la commande";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmation - NextGen</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Orbitron:wght@700;900&family=Rajdhani:wght@600;800&display=swap" rel="stylesheet">
  <style>
    body { background: url('./bg-jeux.gif') no-repeat center center fixed !important; background-size: cover !important; margin: 0; color: white; min-height: 100vh; }
    body::before { content: ''; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 1; }
    main { position: relative; z-index: 2; max-width: 900px; margin: 3rem auto; padding: 0 2rem; text-align: center; }
    .card { background: linear-gradient(135deg, rgba(56,28,135,0.95), rgba(59,7,100,0.9)); border-radius: 1.5rem; padding: 3rem; box-shadow: 0 20px 50px rgba(0,0,0,0.7); border: 1px solid rgba(139,92,246,0.6); }
    h1 { font-family: 'Rajdhani', sans-serif; font-size: 3.5rem; text-shadow: 0 0 30px rgba(255,255,255,0.4); }
    .btn { padding: 1.4rem 5rem; border-radius: 50px; font-weight: 700; text-transform: uppercase; font-size: 1.5rem; cursor: pointer; border: none; }
    .btn.confirm { background: linear-gradient(45deg, #10b981, #34d399); }
    .btn.cancel { background: linear-gradient(45deg, #ef4444, #f87171); }
  </style>
</head>
<body>
  <main>
    <div class="card">
      <h1>Commande confirmée !</h1>
      <p style="font-size:2rem;">Merci pour ton achat bro ❤️</p>
      <p style="font-size:1.6rem; color:#00ffc3;">Tu seras redirigé vers le suivi de ta livraison...</p>

      <?php if (isset($error)): ?>
        <p style="color:#fca5a5; font-size:1.4rem; margin:2rem 0;"><?= $error ?></p>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="confirm_commande" value="1">
        <input type="hidden" name="id_jeu" value="<?= $id_jeu ?>">
        <button type="submit" class="btn confirm">Aller au suivi</button>
      </form>
      <a href="catalogue.php" class="btn cancel">Retour au catalogue</a>
    </div>
  </main>
</body>
</html>