<?php
session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
    header('Location: connexion.php');
    exit;
}

require_once '../../controller/userController.php';
$controller = new userController();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom']);
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $password = $_POST['password'] ?? '';

    if (!empty($password) && strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } else {
        $updatedUser = new User(
            $nom,
            $prenom,
            $email,
            $telephone,
            !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : '',
            $_SESSION['user']['role'],
            $_SESSION['user']['id']
        );

        // Gestion de l'upload photo
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $error = "Format d'image non autorisé (JPG, PNG, WebP uniquement).";
            } elseif ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
                $error = "L'image ne doit pas dépasser 5 Mo.";
            } else {
                $uploadDir = dirname(__DIR__, 2) . '/resources/';
                $newName = 'user_' . $_SESSION['user']['id'] . '_' . time() . '.' . $ext;
                $destination = $uploadDir . $newName;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                    // Supprimer l'ancienne photo si ce n'est pas default.jpg
                    if (!empty($_SESSION['user']['photo_profil']) && $_SESSION['user']['photo_profil'] !== 'default.jpg') {
                        $oldPath = $uploadDir . $_SESSION['user']['photo_profil'];
                        if (file_exists($oldPath)) unlink($oldPath);
                    }

                    $sql = "UPDATE users SET photo_profil = :photo WHERE id = :id";
                    $stmt = Config::getConnexion()->prepare($sql);
                    $stmt->execute([':photo' => $newName, ':id' => $_SESSION['user']['id']]);

                    $_SESSION['user']['photo_profil'] = $newName;
                    $success = "Profil et photo mis à jour avec succès !";
                } else {
                    $error = "Erreur lors de l'upload de l'image.";
                }
            }
        } elseif (!$error) {
            // Mise à jour sans photo
            $controller->updateUserProfile($updatedUser);
            $_SESSION['user']['prenom'] = $prenom;
            $_SESSION['user']['nom'] = $nom;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['telephone'] = $telephone;
            $success = "Profil mis à jour avec succès !";
        }
    }
}

$user = $_SESSION['user'];
$photoPath = !empty($user['photo_profil']) 
    ? '../../resources/' . $user['photo_profil'] 
    : '../../resources/default.jpg';
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gérer mon profil – NextGen</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  
  <!-- NextGen Design System -->
  <link rel="stylesheet" href="../css/nextgen-design-system.css">
  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Exo+2:wght@500;700&display=swap" rel="stylesheet">
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

  /* Ombre douce derrière pour que le texte soit lisible */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background: rgba(10, 8, 35, 0.75);
    backdrop-filter: blur(4px);
    z-index: 1;
  }

  /* Contenu au-dessus de l'ombre */
  .content {
    position: relative;
    z-index: 2;
  }

  /* Boîte centrée, pas trop large, style NextGen */
  .profile-container {
    max-width: 800px;
    margin: 120px auto 60px;
    padding: 0 20px;
  }

  .profile-box {
    background: rgba(15,12,41,0.92);
    backdrop-filter: blur(16px);
    border-radius: 32px;
    padding: 40px 50px;
    border: 1px solid rgba(139,92,246,0.4);
    box-shadow: 0 20px 60px rgba(0,0,0,0.6);
  }

  /* Titre néon */
  .neon-text {
    text-shadow: 0 0 40px #8b5cf6;
  }

  /* Boutons */
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

  /* Bouton retour */
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

  /* Erreurs */
  .error {
    color: #ff6b6b;
    font-size: 0.9rem;
    margin-top: 6px;
    display: block;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .profile-box { padding: 30px 25px; }
    .profile-container { margin: 100px 15px 40px; }
    .back-btn { top: 20px; left: 20px; width: 50px; height: 50px; font-size: 1.5rem; }
  }
</style>
</head>
<body>
  <div class="content">

    <!-- Bouton retour -->
    <a href="index.php" class="back-btn" title="Retour à l'accueil">
      <i class="fas fa-arrow-left"></i>
    </a>

    <!-- Boîte profil -->
    <div class="profile-container">
      <div class="profile-box">
        <h1 class="text-center text-5xl font-black bg-gradient-to-r from-cyan-400 via-purple-500 to-pink-500 bg-clip-text text-transparent neon-text mb-12">
          GÉRER MON PROFIL
        </h1>

      <?php if ($success): ?>
        <div class="glass p-6 rounded-2xl border border-green-500/50 mb-8 text-green-300 text-center font-bold text-xl">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="glass p-6 rounded-2xl border border-red-500/50 mb-8 text-red-300 text-center font-bold text-xl">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <div class="glass rounded-2xl p-10 md:p-12">
        <div class="text-center mb-10">
          <div class="relative inline-block">
            <img src="<?= $photoPath ?>" alt="Photo de profil" id="previewImg"
                 class="w-32 h-32 rounded-full object-cover ring-4 ring-purple-500 shadow-2xl">
            <label for="photo" class="absolute bottom-0 right-0 bg-gradient-to-r from-purple-600 to-pink-600 p-3 rounded-full cursor-pointer hover:scale-110 transition">
              <i class="fas fa-camera text-white"></i>
            </label>
          </div>
          <div id="previewContainer" class="mt-4 hidden">
            <p class="text-purple-300 text-sm">Prévisualisation :</p>
            <img src="" id="previewImg" class="w-32 h-32 rounded-full object-cover ring-4 ring-cyan-500">
          </div>
        </div>

        <form id="profileForm" method="POST" enctype="multipart/form-data" class="space-y-8">
          <input type="file" id="photo" id="photo" name="photo" accept="image/*" class="hidden">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
              <label class="block text-purple-300 font-bold mb-2">Prénom</label>
              <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required 
                     class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg">
              <small class="error" id="prenomError"></small>
            </div>
            <div>
              <label class="block text-purple-300 font-bold mb-2">Nom</label>
              <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required 
                     class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg">
              <small class="error" id="nomError"></small>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
              <label class="block text-purple-300 font-bold mb-2">Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required 
                     class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg">
              <small class="error" id="emailError"></small>
            </div>
            <div>
              <label class="block text-purple-300 font-bold mb-2">Téléphone</label>
              <input type="text" name="telephone" value="<?= htmlspecialchars($user['telephone']) ?>" required 
                     class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg">
              <small class="error" id="telephoneError"></small>
            </div>
          </div>

          <div>
            <label class="block text-purple-300 font-bold mb-2">
              Nouveau mot de passe <span class="text-gray-400 text-sm font-normal">(laisser vide pour ne pas changer)</span>
            </label>
            <input type="password" name="password" class="w-full px-5 py-4 bg-black/40 rounded-xl border border-purple-500 focus:border-cyan-400 text-white text-lg">
            <small class="error" id="passwordError"></small>
          </div>

          <div class="flex justify-center gap-6 pt-8">
            <button type="submit" class="btn-neon text-white font-bold py-4 px-12 rounded-xl text-xl hover:scale-105 transition">
              Sauvegarder
            </button>
            <a href="index.php" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-4 px-12 rounded-xl text-xl transition">
              Annuler
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- VALIDATION JS IDENTIQUE À TOUT LE RESTE -->
  <script>
    document.getElementById('photo').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const previewContainer = document.getElementById('previewContainer');
      const previewImg = document.getElementById('previewImg');

      if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
          previewImg.src = ev.target.result;
          previewContainer.style.display = 'block';
        }
        reader.readAsDataURL(file);
      } else {
        previewContainer.style.display = 'none';
      }
    });

    document.getElementById('profileForm').addEventListener('submit', function(e) {
      let hasError = false;
      document.querySelectorAll('.error').forEach(el => el.textContent = '');

      const prenom = document.querySelector('[name="prenom"]').value.trim();
      if (!prenom) { document.getElementById('prenomError').textContent = 'Le prénom est obligatoire'; hasError = true; }

      const nom = document.querySelector('[name="nom"]').value.trim();
      if (!nom) { document.getElementById('nomError').textContent = 'Le nom est obligatoire'; hasError = true; }

      const email = document.querySelector('[name="email"]').value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!email || !emailRegex.test(email)) { document.getElementById('emailError').textContent = 'Email invalide'; hasError = true; }

      const tel = document.querySelector('[name="telephone"]').value.trim();
      if (!/^[2459]\d{7}$/.test(tel)) { 
        document.getElementById('telephoneError').textContent = 'Téléphone invalide (8 chiffres, commence par 2/4/5/9)'; 
        hasError = true; 
      }

      const pwd = document.querySelector('[name="password"]').value;
      if (pwd && pwd.length < 8) { 
        document.getElementById('passwordError').textContent = 'Minimum 8 caractères'; 
        hasError = true; 
      }

      const photo = document.getElementById('photo').files[0];
      if (photo && photo.size > 5 * 1024 * 1024) {
        alert('L\'image ne doit pas dépasser 5 Mo');
        hasError = true;
      }

      if (hasError) e.preventDefault();
    });
  </script>

  <!-- Disable HTML5 validation -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('form').forEach(form => {
        form.setAttribute('novalidate', 'novalidate');
      });
    });
  </script>
</body>
</html>