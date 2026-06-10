<?php
session_start();
ini_set('display_errors', 'on'); // Désactiver en production
include 'connectdb.php';

// Validation stricte : $id doit être un entier positif
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

if (!$id) {
    header("Location: index.php");
    exit();
}

// Requête paramétrée — élimine l'injection SQL
$stmt = $db->prepare("SELECT * FROM missing WHERE idmissing = :id");
$stmt->execute([':id' => $id]);
$missing = $stmt->fetch();

// Fiche introuvable → redirection propre
if (!$missing) {
    header("Location: index.php");
    exit();
}

// Fonction utilitaire anti-XSS pour tous les affichages
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
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
    <title>Fiche de <?php echo e($missing['firstName']) . ' ' . e($missing['lastName']); ?></title>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php"><p>Mafkoud</p></a>
            </div>
            <nav>
                <ul>
                    <li>
                        <a href="<?php
                            if (isset($_SESSION['pseudo'])) {
                                echo $_SESSION['isAdmin'] == 1
                                    ? 'admin.php?id='  . e($_SESSION['iduser'])
                                    : 'profil.php?id=' . e($_SESSION['iduser']);
                            } else {
                                echo 'login.php';
                            }
                        ?>">Profil</a>
                    </li>
                    <li><a href="missing.php">Missing</a></li>
                    <li>
                        <?php if (isset($_SESSION['pseudo'])): ?>
                            <a href="logout.php">Logout</a>
                        <?php else: ?>
                            <a href="login.php">Login</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <main id="profil">
        <aside>
            <div>
                <img src="<?php echo e($missing['photo']); ?>" alt="missing Person">
            </div>
            <div>
                <p>Height : <span><?php echo e($missing['height']); ?></span> Cm</p>
                <p>Weight : <span><?php echo e($missing['weight']); ?></span> Kg</p>
                <p>Hair : <span><?php echo e($missing['hair']); ?></span></p>
                <p>Eyes : <span><?php echo e($missing['eyes']); ?></span></p>
                <p>Sexe : <span><?php echo e($missing['Sexe']); ?></span></p>
                <p>Mental Illness : <span><?php echo $missing['mentalIlness'] ? 'Yes' : 'No'; ?></span></p>
                <p>Age Of Missing : <span><?php echo e($missing['ageOfMissing']); ?></span> Years</p>
            </div>
        </aside>
        <section id="info">
            <div>
                <div>
                    <p>First Name : <span><?php echo e($missing['firstName']); ?></span></p>
                    <p>Birth Date : <span><?php echo e($missing['birthDate']); ?></span></p>
                    <p>Current Address : <span><?php echo e($missing['currentAdress']); ?></span></p>
                    <p>City : <span><?php echo e($missing['city']); ?></span></p>
                    <p>City Of Missing : <span><?php echo e($missing['cityOfMissing']); ?></span></p>
                    <p>Date Of Missing : <span><?php echo e($missing['dateOfMissing']); ?></span></p>
                    <p>Blood : <span><?php echo e($missing['blood']); ?></span></p>
                    <p>Diabet : <span><?php echo $missing['diabet'] ? 'Yes' : 'No'; ?></span></p>
                    <p>BirthMarks : <span><?php echo $missing['birthmarks'] ? 'Yes' : 'No'; ?></span></p>
                </div>
                <div>
                    <p>Last Name : <span><?php echo e($missing['lastName']); ?></span></p>
                    <p>Birth Place : <span><?php echo e($missing['BirthPlace']); ?></span></p>
                    <p>Previous Address : <span><?php echo e($missing['previousAdress']); ?></span></p>
                    <p>Wilaya : <span><?php echo e($missing['wilaya']); ?></span></p>
                    <p>Wilaya Of Missing : <span><?php echo e($missing['wilayaOfMissing']); ?></span></p>
                    <p>Phone : <span>0<?php echo e($missing['phone']); ?></span></p>
                    <p>Blood Pressure : <span><?php echo $missing['bloodPressure'] ? 'Yes' : 'No'; ?></span></p>
                    <p>Tatoos : <span><?php echo $missing['tatoos'] ? 'Yes' : 'No'; ?></span></p>
                    <p>Scars : <span><?php echo $missing['scars'] ? 'Yes' : 'No'; ?></span></p>
                </div>
            </div>
            <div>
                <p>Describing The Situation : <span><?php echo e($missing['describing']); ?></span></p>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>