<?php
session_start();
ini_set('display_errors', 'off'); // Sécurité : pas d'erreurs affichées en production
include 'connectdb.php';

// Régénération de l'ID de session pour éviter la fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Vérification de la connexion à la base de données
if (!isset($db) || !($db instanceof PDO)) {
    die("Erreur de connexion à la base de données.");
}

// Initialisation des variables de formulaire
$formData = [
    'pseudo' => '',
    'fullName' => '',
    'phone' => '',
    'email' => ''
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    // Récupération et nettoyage
    $formData['pseudo'] = trim($_POST['pseudo'] ?? '');
    $formData['fullName'] = trim($_POST['fullName'] ?? '');
    $formData['phone'] = trim($_POST['phone'] ?? '');
    $formData['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validation
    if ($formData['pseudo'] === '') {
        $errors[] = "Le pseudo est obligatoire.";
    }
    if ($formData['fullName'] === '') {
        $errors[] = "Le nom complet est obligatoire.";
    }
    if ($formData['email'] === '') {
        $errors[] = "L'email est obligatoire.";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    if ($password === '') {
        $errors[] = "Le mot de passe est obligatoire.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }
    if ($password !== $password2) {
        $errors[] = "Les deux mots de passe ne correspondent pas.";
    }

    // Vérifications d'unicité (seulement si pas d'erreur de validation)
    if (empty($errors)) {
        try {
            // Vérifier l'unicité du pseudo
            $stmt = $db->prepare("SELECT iduser FROM user WHERE pseudo = :pseudo");
            $stmt->execute([':pseudo' => $formData['pseudo']]);
            if ($stmt->fetch()) {
                $errors[] = "Ce pseudo est déjà utilisé.";
            }

            // Vérifier l'unicité de l'email
            $stmt = $db->prepare("SELECT iduser FROM user WHERE email = :email");
            $stmt->execute([':email' => $formData['email']]);
            if ($stmt->fetch()) {
                $errors[] = "Cet email est déjà utilisé.";
            }
        } catch (PDOException $e) {
            error_log("Erreur PDO : " . $e->getMessage());
            $errors[] = "Une erreur est survenue. Veuillez réessayer plus tard.";
        }
    }

    // Si aucune erreur, insertion
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $db->prepare(
                "INSERT INTO user (pseudo, fullname, phone, email, password)
                 VALUES (:pseudo, :fullname, :phone, :email, :password)"
            );
            $stmt->execute([
                ':pseudo' => $formData['pseudo'],
                ':fullname' => $formData['fullName'],
                ':phone' => $formData['phone'],
                ':email' => $formData['email'],
                ':password' => $hashedPassword,
            ]);

            // Redirection après inscription réussie
            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            error_log("Erreur PDO : " . $e->getMessage());
            $errors[] = "Erreur lors de l'inscription. Veuillez réessayer.";
        }
    }

    // Stockage des erreurs pour affichage (déjà dans $errors)
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
    <title>Register</title>
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
                    <li><a href="login.php">Login</a></li>
                    <li><a href="missing.php">Missing</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <div id="register">
            <div>
                <h1>Register</h1>

                <?php if (!empty($errors)): ?>
                    <div class="error">
                        <?php foreach ($errors as $err): ?>
                            <p><?php echo htmlspecialchars($err); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <div>
                        <label for="pseudo">Pseudo :</label>
                        <input type="text" name="pseudo" id="pseudo" required
                            value="<?php echo htmlspecialchars($formData['pseudo']); ?>" />
                    </div>
                    <div>
                        <label for="fullName">Full Name :</label>
                        <input type="text" name="fullName" id="fullName" required
                            value="<?php echo htmlspecialchars($formData['fullName']); ?>" />
                    </div>
                    <div>
                        <label for="phone">Phone :</label>
                        <input type="tel" name="phone" id="phone"
                            value="<?php echo htmlspecialchars($formData['phone']); ?>" />
                    </div>
                    <div>
                        <label for="email">Email :</label>
                        <input type="email" name="email" id="email" required
                            value="<?php echo htmlspecialchars($formData['email']); ?>" />
                    </div>
                    <div>
                        <label for="password">Password :</label>
                        <input type="password" name="password" id="password" required minlength="8"
                            autocomplete="off" />
                    </div>
                    <div>
                        <label for="password2">Confirm Password :</label>
                        <input type="password" name="password2" id="password2" required minlength="8"
                            autocomplete="off" />
                    </div>
                    <div>
                        <button type="submit" name="signup">Signup</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>

</html>