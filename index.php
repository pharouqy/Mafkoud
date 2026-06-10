<?php
session_start();
// En production, désactiver l'affichage des erreurs
ini_set('display_errors', 'off');
include 'connectdb.php';

// Régénération de l'ID de session (sécurité)
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

/**
 * Affiche une fiche personne disparue (HTML)
 */
function renderMissingCard(array $row): string
{
    $html = '<a href="fiche.php?id=' . (int) $row['idmissing'] . '">';
    $html .= '<article class="card">';
    $html .= '<div class="card_img">';
    $html .= '<img src="' . htmlspecialchars($row['photo'], ENT_QUOTES, 'UTF-8') . '" alt="card_img" />';
    $html .= '</div>';
    $html .= '<div class="card_text">';
    $html .= '<h3>';
    $html .= '<span>' . htmlspecialchars($row['firstName'], ENT_QUOTES, 'UTF-8') . ' </span>';
    $html .= '<span>' . htmlspecialchars($row['lastName'], ENT_QUOTES, 'UTF-8') . '</span>';
    $html .= '</h3>';
    $html .= '<h4>';
    $html .= '<span>' . htmlspecialchars($row['city'], ENT_QUOTES, 'UTF-8') . ' </span>';
    $html .= '<span>' . htmlspecialchars($row['wilaya'], ENT_QUOTES, 'UTF-8') . '</span>';
    $html .= '</h4>';
    $html .= '<p>';
    $html .= '<span>Age: ' . (int) $row['ageOfMissing'] . ' years</span>';
    $html .= '<br />';
    $html .= '<span>';
    $html .= '<img src="./assets/images/heart.png" alt="heart" />';
    $html .= '</span>';
    $html .= '<span>Last Seen:<br> ' . htmlspecialchars($row['dateOfMissing'], ENT_QUOTES, 'UTF-8') . '</span>';
    $html .= '</p>';
    $html .= '</div>';
    $html .= '</article>';
    $html .= '</a>';
    return $html;
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
    <title>Mafkoud</title>
</head>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <p>Mafkoud</p>
                </a>
            </div>
            <?php if (isset($_SESSION['iduser'])): ?>
                <nav>
                    <ul class="profil">
                        <li><a href="logout.php">Logout</a></li>
                        <li><a href="missing.php">Missing</a></li>
                    </ul>
                </nav>
            <?php else: ?>
                <nav>
                    <ul class="profil">
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </header>
    <main>
        <div class="dash">
            <div>
                <h1>
                    Find the people<br />
                    who matter<br />
                    to you !!!
                </h1>
            </div>
            <div class="form">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post">
                    <input type="text" name="firstName" id="firstName" placeholder="First Name" />
                    <input type="text" name="lastName" id="lastName" placeholder="Last Name" />
                    <input type="text" name="city" id="city" placeholder="City" />
                    <select name="wilaya" id="wilaya">
                        <option value="">wilaya</option>
                        <option value="Alger">Alger</option>
                        <option value="Annaba">Annaba</option>
                        <option value="Oran">Oran</option>
                        <option value="Constantine">Constantine</option>
                        <option value="Tipaza">Tipaza</option>
                        <option value="Boumerdes">Boumerdes</option>
                    </select>
                    <button type="submit" name="find">Find</button>
                </form>
            </div>
        </div>
        <section id="search">
            <?php
            if (isset($_POST['find'])) {
                // Récupération et assainissement des entrées
                $firstName = trim($_POST['firstName'] ?? '');
                $lastName = trim($_POST['lastName'] ?? '');
                $city = trim($_POST['city'] ?? '');
                $wilaya = trim($_POST['wilaya'] ?? '');

                // Construction de la requête préparée avec LIKE partiel
                $sql = "SELECT * FROM missing 
                        WHERE isActive = 1 
                          AND isFind = 0 
                          AND firstName LIKE :firstName 
                          AND lastName LIKE :lastName 
                          AND (city LIKE :city OR wilaya LIKE :wilaya)";

                $stmt = $db->prepare($sql);

                // Ajout des % pour la recherche partielle
                $stmt->bindValue(':firstName', '%' . $firstName . '%');
                $stmt->bindValue(':lastName', '%' . $lastName . '%');
                $stmt->bindValue(':city', '%' . $city . '%');
                $stmt->bindValue(':wilaya', '%' . $wilaya . '%');

                $stmt->execute();
                $result = $stmt->fetchAll();

                if ($result) {
                    foreach ($result as $row) {
                        echo renderMissingCard($row);
                    }
                } else {
                    echo '<p>No result found</p>';
                }
            }
            ?>
        </section>
        <div class="main">
            <h2>Last Missing !!!</h2>
            <div id="card">
                <?php
                try {
                    $stmt = $db->prepare("SELECT * FROM missing WHERE isActive = 1 AND isFind = 0 ORDER BY idmissing DESC LIMIT 16");
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                    foreach ($result as $row) {
                        echo renderMissingCard($row);
                    }
                } catch (PDOException $e) {
                    // En production, loguer l'erreur plutôt que l'afficher
                    error_log('Erreur PDO : ' . $e->getMessage());
                    echo '<p>Une erreur est survenue. Veuillez réessayer plus tard.</p>';
                }
                ?>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>

</html>