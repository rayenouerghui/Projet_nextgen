<?php
session_start();
require_once '../../config/config.php';
require_once '../../controller/userController.php';

$controller = new userController();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $user = $controller->getUserByEmail($email);

   if ($user && $password == $user->getMdp()) {
    $_SESSION['user'] = [
        'id'          => $user->getId(),
        'nom'         => $user->getNom(),
        'prenom'      => $user->getPrenom(),
        'email'       => $user->getEmail(),
        'telephone'   => $user->getTelephone(),
        'role'        => $user->getRole(),
        'photo_profil'=> $user->getPhotoProfil() ?? 'default.jpg',
        'credits'     => $user->getCredits()
    ];

    // IMPORTANT : On met à jour la dernière connexion
    try {
        $pdo = Config::getConnexion(); // ou $controller->getPdo() si tu as une méthode
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user->getId()]);
    } catch (Exception $e) {
        error_log("Échec mise à jour last_login : " . $e->getMessage());
    }

    $_SESSION['success_message'] = "Bienvenue, " . htmlspecialchars($user->getPrenom()) . " !";
    header('Location: index.php');
    exit;
}
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion - NextGen</title>

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Raleway:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <link rel="stylesheet" href="connexion.css">
  
  <style>
    .toast-container { z-index: 9999; }
    .toast-body { font-weight: 600; }
  </style>
</head>
<body>

  <?php if ($success): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3">
      <div class="toast show align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
          <div class="toast-body">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= $success ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    </div>
  <?php endif; ?>


  <?php if ($error): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3">
      <div class="toast show align-items-center text-white bg-danger border-0" role="alert">
        <div class="d-flex">
          <div class="toast-body">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="login-container">
    <div class="row g-0">
      <div class="col-lg-5 login-left">
        <div style="color:white;" class="logo">NextGen</div>
        <h2>Bienvenue sur NextGen</h2>
        <p>Each game you buy contributes to giving orphaned children better care and brighter futures.</p>
      </div>

      <div class="col-lg-7 login-right">
        <div class="d-lg-none logo">
          <i class="bi bi-hexagon-fill"></i> NextGen
        </div>

        <h3 class="login-title">Connexion</h3>
        <p class="login-subtitle">Connectez-vous pour accéder à votre espace</p>

        <form method="POST" id="loginForm" novalidate>
          <div class="mb-3">
            <label class="form-label">Adresse Email</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input type="text" name="email" class="form-control" placeholder="votre@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label">Mot de passe</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input type="password" name="password" class="form-control" placeholder="••••••••" id="password">
              <span class="input-group-text" style="cursor: pointer;" onclick="togglePassword()">
                <i class="bi bi-eye" id="toggleIcon"></i>
              </span>
            </div>
          </div>

          <div class="text-end mb-4">
            <a href="mdpo1.html" class="forgot-password">Mot de passe oublié ?</a>
          </div>

          <button type="submit" class="btn-login">
            <i class="bi bi-box-arrow-in-right me-2"></i> Se connecter
          </button>
        </form>

        <div class="register-link">
          Pas encore de compte ? <a href="inscriptiom.php">Créer un compte</a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="connexion.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.toast').forEach(toast => {
        new bootstrap.Toast(toast, { delay: 4000 }).show();
      });
    });
  </script>
</body>
</html>