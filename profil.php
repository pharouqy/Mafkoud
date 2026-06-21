<?php
session_start();
ini_set('display_errors', 'off');
include 'connectdb.php';

// Régénération de session
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Vérification connexion
if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit;
}

// Récupération et validation de l'ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    die("ID invalide.");
}

// Vérification des droits : propriétaire ou admin
if ($_SESSION['iduser'] != $id && empty($_SESSION['isAdmin'])) {
    die("Accès interdit.");
}

// Récupération de l'utilisateur cible
$stmt = $db->prepare("SELECT * FROM user WHERE iduser = :id");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();
if (!$user) {
    die("Utilisateur introuvable.");
}

// Génération d'un token CSRF s'il n'existe pas
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF invalide.");
    }

    // Action "find" (marquer comme retrouvé)
    if (isset($_POST['find'])) {
        $idMissing = filter_input(INPUT_POST, 'idIsFind', FILTER_VALIDATE_INT);
        if ($idMissing) {
            // Vérifier que la fiche appartient à l'utilisateur (ou admin)
            $stmt = $db->prepare("SELECT idmissing, photo FROM missing WHERE idmissing = :id AND user_iduser = :uid");
            $stmt->execute([':id' => $idMissing, ':uid' => $id]);
            $fiche = $stmt->fetch();
            if ($fiche) {
                // Basculer isFind à 1
                $db->prepare("UPDATE missing SET isFind = 1 WHERE idmissing = :id")->execute([':id' => $idMissing]);
            }
        }
        header("Location: profil.php?id=" . $id);
        exit;
    }

    // Action "update" (simple redirection)
    if (isset($_POST['update'])) {
        $idUpdate = filter_input(INPUT_POST, 'idUpdate', FILTER_VALIDATE_INT);
        if ($idUpdate) {
            // Vérifier appartenance avant de rediriger
            $stmt = $db->prepare("SELECT idmissing FROM missing WHERE idmissing = :id AND user_iduser = :uid");
            $stmt->execute([':id' => $idUpdate, ':uid' => $id]);
            if ($stmt->fetch()) {
                header("Location: updateMissing.php?id=" . $idUpdate);
                exit;
            }
        }
        header("Location: profil.php?id=" . $id);
        exit;
    }

    // Action "delete" (supprimer une fiche)
    if (isset($_POST['delete'])) {
        $idDelete = filter_input(INPUT_POST, 'idDelete', FILTER_VALIDATE_INT);
        $photo = $_POST['idPhoto'] ?? ''; // Chemin de l'image
        if ($idDelete) {
            // Vérifier appartenance
            $stmt = $db->prepare("SELECT photo FROM missing WHERE idmissing = :id AND user_iduser = :uid");
            $stmt->execute([':id' => $idDelete, ':uid' => $id]);
            $fiche = $stmt->fetch();
            if ($fiche) {
                // Supprimer l'image physique
                $photoPath = $fiche['photo'];
                if (!empty($photoPath) && file_exists($photoPath)) {
                    unlink($photoPath);
                }
                // Supprimer la fiche
                $db->prepare("DELETE FROM missing WHERE idmissing = :id")->execute([':id' => $idDelete]);
            }
        }
        header("Location: profil.php?id=" . $id);
        exit;
    }

    // Action "deleteProfil" (supprimer le compte utilisateur)
    if (isset($_POST['deleteProfil'])) {
        // Vérifier que c'est bien le propriétaire (ou admin) et que l'ID correspond
        if ($_SESSION['iduser'] == $id || !empty($_SESSION['isAdmin'])) {
            // Récupérer toutes les fiches pour supprimer les photos
            $st = $db->prepare("SELECT photo FROM missing WHERE user_iduser = :uid");
            $st->execute([':uid' => $id]);
            while ($fiche = $st->fetch()) {
                if (!empty($fiche['photo']) && file_exists($fiche['photo'])) {
                    unlink($fiche['photo']);
                }
            }
            // Supprimer les fiches associées
            $db->prepare("DELETE FROM missing WHERE user_iduser = :uid")->execute([':uid' => $id]);
            // Supprimer l'utilisateur
            $db->prepare("DELETE FROM user WHERE iduser = :id")->execute([':id' => $id]);

            // Si l'utilisateur supprime son propre compte, on détruit la session
            if ($_SESSION['iduser'] == $id) {
                session_destroy();
                header("Location: index.php");
                exit;
            }
            // Sinon (admin supprimant un autre), on redirige vers la page d'accueil admin ou index
            header("Location: index.php");
            exit;
        }
    }

    // Si aucune action reconnue, redirection par sécurité
    header("Location: profil.php?id=" . $id);
    exit;
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
    <title>Profil</title>
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
        <section>
            <div>
                <div>
                    <img src="./assets/images/card.png" alt="profil">
                </div>
                <div>
                    <p>Pseudo: <span id="pseudo"><?php echo htmlspecialchars($user['pseudo']); ?></span></p>
                    <p>Full Name: <span id="name"><?php echo htmlspecialchars($user['fullName']); ?></span></p>
                    <p>Phone Number: <span id="phone"><?php echo htmlspecialchars($user['phone']); ?></span></p>
                    <p>Email: <span id="mail"><?php echo htmlspecialchars($user['email']); ?></span></p>
                </div>
            </div>

            <?php if ($_SESSION['iduser'] == $id || !empty($_SESSION['isAdmin'])): ?>
                <div id="button-profil">
                    <form action="updateProfil.php?id=<?php echo $id; ?>" method="post">
                        <button type="submit" name="updateProfil" class="update">Update</button>
                    </form>
                    <?php if ($_SESSION['iduser'] == $id): ?>
                        <form action="" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <button type="submit" name="deleteProfil"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ?');" class="delete">Delete</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div id="label">
                <div>
                    <h1>Missing Persons ...</h1>
                </div>
                <?php
                // Récupération des fiches de l'utilisateur
                $stmt = $db->prepare("SELECT * FROM missing WHERE user_idUser = :uid ORDER BY idMissing DESC");
                $stmt->execute([':uid' => $id]);
                while ($fiche = $stmt->fetch()):
                    ?>
                    <article>
                        <div>
                            <h2>ID : <?php echo (int) $fiche['idmissing']; ?> -
                                <img src="<?php echo htmlspecialchars($fiche['photo']); ?>" alt="photo" /> -
                                <?php echo htmlspecialchars($fiche['lastName'] . ' ' . $fiche['firstName']); ?>
                            </h2>
                        </div>
                        <div>
                            <!-- Formulaire Find (marquer retrouvé) -->
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="idIsFind" value="<?php echo (int) $fiche['idmissing']; ?>">
                                <button type="submit" name="find">
                                    <?php if ($fiche['isFind'] == 1): ?>
                                        <img src="./assets/images/isFind.png" alt="isFind" title="isFind">
                                    <?php else: ?>
                                        <img src="./assets/images/isNotFind.png" alt="isNotFind" title="isNotFind">
                                    <?php endif; ?>
                                </button>
                            </form>

                            <!-- Formulaire Update (redirection) -->
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="idUpdate" value="<?php echo (int) $fiche['idmissing']; ?>">
                                <button type="submit" name="update">
                                    <img src="./assets/images/Update.png" alt="Update">
                                </button>
                            </form>

                            <!-- Formulaire Delete (supprimer fiche) -->
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="idDelete" value="<?php echo (int) $fiche['idmissing']; ?>">
                                <input type="hidden" name="idPhoto"
                                    value="<?php echo htmlspecialchars($fiche['photo']); ?>">
                                <button type="submit" name="delete" onclick="return confirm('Supprimer cette fiche ?');">
                                    <img src="./assets/images/Delete.png" alt="Delete">
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>

</html>