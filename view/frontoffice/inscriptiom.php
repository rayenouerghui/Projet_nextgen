<?php
session_start();
require_once '../../config/config.php';
require_once '../../controller/userController.php';
require_once 'includes/PHPMailer.php';
require_once 'includes/SMTP.php';
require_once 'includes/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$controller = new userController();
$message = '';
$step = $_GET['step'] ?? 'form';

// ============================================
// STEP 2: VERIFY CODE
// ============================================
if ($step === 'verify' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $sessionData = $_SESSION['pending_verification'] ?? null;

    if (!$sessionData || $code !== $sessionData['code'] || time() > $sessionData['expires']) {
        $message = '<div class="alert alert-danger">Code incorrect ou expiré.</div>';
        unset($_SESSION['pending_verification']);
        $step = 'form';
    } else {
        // Create account
        $hash = password_hash($sessionData['password'], PASSWORD_DEFAULT);
        $user = new User($sessionData['nom'], $sessionData['prenom'], $sessionData['email'], $sessionData['telephone'], $hash, 'user');

        if ($controller->addUser($user)) {
            $userId = Config::getConnexion()->lastInsertId();
            $stmt = Config::getConnexion()->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
            $stmt->execute([$userId]);

            // Log historique
            $stmt = Config::getConnexion()->prepare("INSERT INTO historique (id_user, type_action, description) VALUES (?, 'inscription', 'Compte créé')");
            $stmt->execute([$userId]);

            // Auto-login
            $_SESSION['user'] = [
                'id' => $userId,
                'nom' => $sessionData['nom'],
                'prenom' => $sessionData['prenom'],
                'email' => $sessionData['email'],
                'role' => 'user',
                'photo_profil' => 'default.jpg',
                'statut' => 'actif'
            ];

            unset($_SESSION['pending_verification']);
            header('Location: index.php');
            exit;
        }
    }
}

// ============================================
// STEP 1: SEND CODE VIA BREVO (100% WORKING)
// ============================================
if ($step === 'form' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $password = $_POST['password'] ?? '';

    if (empty($nom) || empty($prenom) || empty($email) || empty($telephone) || strlen($password) < 8) {
        $message = '<div class="alert alert-danger">Tous les champs sont obligatoires. Mot de passe ≥ 8 caractères.</div>';
    } else {
        $stmt = Config::getConnexion()->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = '<div class="alert alert-danger">Email déjà utilisé.</div>';
        } else {
            $code = sprintf("%06d", mt_rand(100000, 999999));
            $_SESSION['pending_verification'] = [
                'nom' => $nom, 'prenom' => $prenom, 'email' => $email,
                'telephone' => $telephone, 'password' => $password,
                'code' => $code, 'expires' => time() + 600
            ];

            $mail = new PHPMailer(true);
           
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.mailersend.net';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'MS_Mkf9Zo@test-65qngkd8wkdlwr12.mlsender.net';
                $mail->Password   = 'mssp.N3SGo6z.0p7kx4x3yqm49yjr.8386lWN';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                // FIXED: Use your test email as "From" (required for free plan)
                $mail->setFrom('MS_Mkf9Zo@test-65qngkd8wkdlwr12.mlsender.net', 'NextGen');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Votre code de vérification NextGen';
                $mail->Body    = "
                    <h2 style='color:#4f46e5;'>Bienvenue sur NextGen !</h2>
                    <p>Votre code de vérification est :</p>
                    <h1 style='background:#4f46e5;color:white;padding:30px;border-radius:15px;letter-spacing:10px;font-size:48px;text-align:center;'>$code</h1>
                    <p>Ce code expire dans 10 minutes.</p>
                ";

                $mail->send();
                $step = 'verify';
                $message = '<div class="alert alert-success">Code envoyé à ' . htmlspecialchars($email) . ' ! Vérifiez inbox et spam.</div>';

            } catch (Exception $e) {
                $message = '<div class="alert alert-danger">Erreur: ' . $mail->ErrorInfo . '</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Inscription - NextGen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh; display: flex; align-items: center; }
        .register-container { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); max-width: 500px; margin: auto; }
        .verification-code-input { font-size: 40px; letter-spacing: 15px; text-align: center; }
        .btn-register { background: #4f46e5; color: white; padding: 15px; font-size: 18px; }
    </style>
</head>
<body>
<div class="register-container">
    <h2 class="text-center mb-4"><?= $step === 'verify' ? 'Vérification' : 'Inscription' ?></h2>
    <?php echo $message; ?>

    <?php if ($step === 'form'): ?>
        <form method="POST">
            <div class="row">
                <div class="col-6 mb-3"><input type="text" name="prenom" class="form-control" placeholder="Prénom" required></div>
                <div class="col-6 mb-3"><input type="text" name="nom" class="form-control" placeholder="Nom" required></div>
            </div>
            <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
            <input type="text" name="telephone" class="form-control mb-3" placeholder="Téléphone" required>
            <input type="password" name="password" class="form-control mb-4" placeholder="Mot de passe (8+)" required>
            <button type="submit" class="btn btn-register w-100">Recevoir le code</button>
        </form>
    <?php else: ?>
        <form method="POST" action="?step=verify">
            <p class="text-center">Code envoyé à <strong><?= htmlspecialchars($_SESSION['pending_verification']['email']) ?></strong></p>
            <input type="text" name="code" class="form-control verification-code-input mb-4" maxlength="6" required autofocus>
            <button type="submit" class="btn btn-register w-100">Vérifier</button>
        </form>
    <?php endif; ?>

    <div class="text-center mt-3">
        <a href="connexion.php">Déjà un compte ? Se connecter</a>
    </div>
</div>
</body>
</html>