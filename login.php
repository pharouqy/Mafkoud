<?php
session_start();
ini_set('display_errors', 'off'); // Sécurité : désactiver en production
include 'connectdb.php';

// Vérification que la connexion PDO est bien établie
if (!isset($db) || !($db instanceof PDO)) {
    die("Erreur de connexion à la base de données.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Validation des entrées
    $pseudo = trim($_POST['pseudo'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];
    if ($pseudo === '') {
        $errors[] = "Le pseudo est requis.";
    }
    if ($password === '') {
        $errors[] = "Le mot de passe est requis.";
    }

    if (empty($errors)) {
        // Requête préparée
        $stmt = $db->prepare("SELECT * FROM user WHERE pseudo = :pseudo");
        $stmt->execute([':pseudo' => $pseudo]);
        $row = $stmt->fetch();

        if ($row && password_verify($password, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION['pseudo'] = $row['pseudo'];
            $_SESSION['iduser'] = $row['iduser'];
            $_SESSION['isAdmin'] = $row['isAdmin'];
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Pseudo ou mot de passe incorrect.";
        }
    }

    // Stockage des erreurs en session pour affichage après redirection (facultatif)
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        // Redirection vers la même page pour éviter la resoumission (PRG)
        header("Location: login.php");
        exit();
    }
}

// Récupération des erreurs depuis la session (après redirection)
$login_errors = $_SESSION['login_errors'] ?? [];
unset($_SESSION['login_errors']);
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
    <title>Login</title>
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
                    <li><a href="register.php">Register</a></li>
                    <li><a href="missing.php">Missing</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <div id="login">
            <div>
                <h1>Login</h1>

                <?php if (!empty($login_errors)): ?>
                    <div class="error">
                        <?php foreach ($login_errors as $err): ?>
                            <p><?php echo htmlspecialchars($err); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <div>
                        <label for="pseudo">Pseudo :</label>
                        <input type="text" name="pseudo" id="pseudo" required autocomplete="username" />
                    </div>
                    <div>
                        <label for="password">Password :</label>
                        <input type="password" name="password" id="password" required autocomplete="off" />
                    </div>
                    <div>
                        <button type="submit" name="login">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>

</html>