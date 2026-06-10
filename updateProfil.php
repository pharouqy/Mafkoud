<?php
session_start();
ini_set('display_errors', 'off'); // Sécurité : pas d'erreurs en production
include 'connectdb.php';

// Régénération de session pour prévenir la fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Redirection si non connecté
if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit;
}

// Validation de l'ID passé en GET
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    die("ID invalide.");
}

// Vérifier que l'utilisateur connecté est bien le propriétaire ou admin
if ($_SESSION['iduser'] != $id && empty($_SESSION['isAdmin'])) {
    die("Accès interdit.");
}

// Récupérer les informations de l'utilisateur à modifier
$stmt = $db->prepare("SELECT * FROM user WHERE iduser = :id");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();
if (!$user) {
    die("Utilisateur introuvable.");
}

// Génération d'un token CSRF si absent
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialisation des erreurs
$errors = [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF invalide.");
    }

    // Récupération des données
    $fullName = trim($_POST['fullName'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if ($fullName === '') {
        $errors[] = "Le nom complet est obligatoire.";
    }
    if ($email === '') {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    // Gestion du mot de passe (optionnel)
    $hashedPassword = $user['password']; // conserver l'ancien hachage par défaut
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        } else {
            // Hashage sécurisé bcrypt
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        }
    }

    // Vérification unicité de l'email si modifié
    if (empty($errors) && $email !== $user['email']) {
        $check = $db->prepare("SELECT iduser FROM user WHERE email = :email AND iduser != :id");
        $check->execute([':email' => $email, ':id' => $id]);
        if ($check->fetch()) {
            $errors[] = "Cet email est déjà utilisé par un autre compte.";
        }
    }

    // Si pas d'erreur, mise à jour
    if (empty($errors)) {
        try {
            $sql = "UPDATE user SET fullname = :fullname, phone = :phone, email = :email, password = :password WHERE iduser = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':fullname' => $fullName,
                ':phone' => $phone,
                ':email' => $email,
                ':password' => $hashedPassword,
                ':id' => $id
            ]);

            // Redirection après succès
            header("Location: profil.php?id=" . $id);
            exit;
        } catch (PDOException $e) {
            error_log("Erreur PDO updateProfil : " . $e->getMessage());
            $errors[] = "Une erreur est survenue lors de la mise à jour.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="./css/style.css" />
    <link rel="apple-touch-icon" sizes="180x180" href="./assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./assets/favicon-16x16.png">
    <link rel="manifest" href="./assets/site.webmanifest">
    <title>Update Profil</title>
</head>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <p>Mafkoud</p>
                </a>
            </div>
            <nav>
                <ul class="profil">
                    <li><a href="logout.php">Logout</a></li>
                    <li><a href="missing.php">Missing</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <div id="register">
            <div>
                <h1>Update Profil</h1>

                <?php if (!empty($errors)): ?>
                    <div class="error">
                        <?php foreach ($errors as $err): ?>
                            <p><?php echo htmlspecialchars($err); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div>
                        <label for="pseudo">Pseudo :</label>
                        <input type="text" name="pseudo" id="pseudo"
                            value="<?php echo htmlspecialchars($user['pseudo']); ?>" disabled />
                    </div>
                    <div>
                        <label for="fullName">Full Name :</label>
                        <input type="text" name="fullName" id="fullName"
                            value="<?php echo htmlspecialchars($fullName ?? $user['fullName']); ?>" />
                    </div>
                    <div>
                        <label for="phone">Phone :</label>
                        <input type="tel" name="phone" id="phone"
                            value="<?php echo htmlspecialchars($phone ?? $user['phone']); ?>" />
                    </div>
                    <div>
                        <label for="email">Email :</label>
                        <input type="email" name="email" id="email"
                            value="<?php echo htmlspecialchars($email ?? $user['email']); ?>" />
                    </div>
                    <div>
                        <label for="password">Password :</label>
                        <input type="password" name="password" id="password"
                            placeholder="Laisser vide pour ne pas changer" autocomplete="off" />
                    </div>
                    <div>
                        <button type="submit" name="update">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>

</html>