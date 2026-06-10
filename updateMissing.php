<?php
session_start();
ini_set('display_errors', 'off');
include 'connectdb.php';

// Régénération de session
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Redirection si non connecté
if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit;
}

// Récupération et validation de l'ID
$idMissing = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idMissing) {
    die("ID invalide.");
}

// Récupération de la fiche et vérification des droits
$stmt = $db->prepare("SELECT * FROM missing WHERE idmissing = :id");
$stmt->execute([':id' => $idMissing]);
$row = $stmt->fetch();
if (!$row) {
    die("Fiche introuvable.");
}
if ($_SESSION['iduser'] != $row['user_iduser'] && empty($_SESSION['isAdmin'])) {
    die("Accès interdit.");
}

// Initialisation des variables du formulaire
$formData = $row; // pour pré-remplir, on conserve les données actuelles

$errors = [];

// Génération d'un token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF invalide.");
    }

    // Récupération des données, avec fallback sur les valeurs existantes si champ vide
    $firstName = trim($_POST['firstName'] ?? '') ?: $row['firstName'];
    $lastName = trim($_POST['lastName'] ?? '') ?: $row['lastName'];
    $birthDate = trim($_POST['birthDate'] ?? '') ?: $row['birthDate'];
    $BirthPlace = trim($_POST['BirthPlace'] ?? '') ?: $row['BirthPlace'];
    $ageOfMissing = trim($_POST['ageOfMissing'] ?? '') ?: $row['ageOfMissing'];
    $Sexe = $_POST['Sexe'] ?? $row['Sexe'];
    $currentAdress = trim($_POST['currentAdress'] ?? '') ?: $row['currentAdress'];
    $previousAdress = trim($_POST['previousAdress'] ?? '') ?: $row['previousAdress'];
    $city = trim($_POST['city'] ?? '') ?: $row['city'];
    $wilaya = $_POST['wilaya'] ?? $row['wilaya'];
    $cityOfMissing = trim($_POST['cityOfMissing'] ?? '') ?: $row['cityOfMissing'];
    $wilayaOfMissing = $_POST['wilayaOfMissing'] ?? $row['wilayaOfMissing'];
    $dateOfMissing = trim($_POST['dateOfMissing'] ?? '') ?: $row['dateOfMissing'];
    $phone = trim($_POST['phone'] ?? '') ?: $row['phone'];
    $height = trim($_POST['height'] ?? '') ?: $row['height'];
    $weight = trim($_POST['weight'] ?? '') ?: $row['weight'];
    $hair = trim($_POST['hair'] ?? '') ?: $row['hair'];
    $eyes = trim($_POST['eyes'] ?? '') ?: $row['eyes'];
    $blood = $_POST['blood'] ?? $row['blood'];
    $describing = trim($_POST['describing'] ?? '') ?: $row['describing'];

    // Checkboxes
    $bloodPressure = isset($_POST['bloodPressure']) ? 1 : 0;
    $diabet = isset($_POST['diabet']) ? 1 : 0;
    $mentalIlness = isset($_POST['mentalIlness']) ? 1 : 0;
    $tatoos = isset($_POST['tatoos']) ? 1 : 0;
    $birthmarks = isset($_POST['birthmarks']) ? 1 : 0;
    $scars = isset($_POST['scars']) ? 1 : 0;

    // Gestion de la photo
    $photo = $row['photo']; // par défaut, on garde l'ancienne
    if (!empty($_FILES['photo']['name'])) {
        $file = $_FILES['photo'];
        // Validation de l'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Erreur lors du téléchargement de la photo.";
        } else {
            $allowedExt = ['jpg', 'jpeg', 'png'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $maxSize = 2 * 1024 * 1024; // 2 Mo

            if (!in_array($fileExt, $allowedExt)) {
                $errors[] = "Format d'image non autorisé (jpg, jpeg, png).";
            } elseif ($file['size'] > $maxSize) {
                $errors[] = "L'image ne doit pas dépasser 2 Mo.";
            } elseif (!@getimagesize($file['tmp_name'])) {
                $errors[] = "Le fichier n'est pas une image valide.";
            } else {
                // Générer un nouveau nom unique
                $photoDir = './images/';
                if (!is_dir($photoDir)) {
                    mkdir($photoDir, 0755, true);
                }
                $newName = bin2hex(random_bytes(16)) . '.' . $fileExt;
                $newPath = $photoDir . $newName;

                if (move_uploaded_file($file['tmp_name'], $newPath)) {
                    // Supprimer l'ancienne image si elle existe
                    if (!empty($row['photo']) && file_exists($row['photo'])) {
                        unlink($row['photo']);
                    }
                    $photo = $newPath;
                } else {
                    $errors[] = "Erreur lors du déplacement du fichier.";
                }
            }
        }
    }

    // Si pas d'erreur, mise à jour en base
    if (empty($errors)) {
        try {
            $sql = "UPDATE missing SET 
                        firstName = :firstName,
                        lastName = :lastName,
                        birthDate = :birthDate,
                        BirthPlace = :birthPlace,
                        ageOfMissing = :ageOfMissing,
                        Sexe = :sexe,
                        currentAdress = :currentAdress,
                        previousAdress = :previousAdress,
                        city = :city,
                        wilaya = :wilaya,
                        cityOfMissing = :cityOfMissing,
                        wilayaOfMissing = :wilayaOfMissing,
                        dateOfMissing = :dateOfMissing,
                        phone = :phone,
                        height = :height,
                        weight = :weight,
                        hair = :hair,
                        eyes = :eyes,
                        blood = :blood,
                        bloodPressure = :bloodPressure,
                        diabet = :diabet,
                        mentalIlness = :mentalIlness,
                        tatoos = :tatoos,
                        birthmarks = :birthmarks,
                        scars = :scars,
                        photo = :photo,
                        describing = :describing
                    WHERE idmissing = :id";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':firstName', $firstName);
            $stmt->bindValue(':lastName', $lastName);
            $stmt->bindValue(':birthDate', $birthDate);
            $stmt->bindValue(':birthPlace', $BirthPlace);
            $stmt->bindValue(':ageOfMissing', $ageOfMissing, PDO::PARAM_INT);
            $stmt->bindValue(':sexe', $Sexe);
            $stmt->bindValue(':currentAdress', $currentAdress);
            $stmt->bindValue(':previousAdress', $previousAdress);
            $stmt->bindValue(':city', $city);
            $stmt->bindValue(':wilaya', $wilaya);
            $stmt->bindValue(':cityOfMissing', $cityOfMissing);
            $stmt->bindValue(':wilayaOfMissing', $wilayaOfMissing);
            $stmt->bindValue(':dateOfMissing', $dateOfMissing);
            $stmt->bindValue(':phone', $phone);
            $stmt->bindValue(':height', $height, PDO::PARAM_INT);
            $stmt->bindValue(':weight', $weight, PDO::PARAM_INT);
            $stmt->bindValue(':hair', $hair);
            $stmt->bindValue(':eyes', $eyes);
            $stmt->bindValue(':blood', $blood);
            $stmt->bindValue(':bloodPressure', $bloodPressure, PDO::PARAM_INT);
            $stmt->bindValue(':diabet', $diabet, PDO::PARAM_INT);
            $stmt->bindValue(':mentalIlness', $mentalIlness, PDO::PARAM_INT);
            $stmt->bindValue(':tatoos', $tatoos, PDO::PARAM_INT);
            $stmt->bindValue(':birthmarks', $birthmarks, PDO::PARAM_INT);
            $stmt->bindValue(':scars', $scars, PDO::PARAM_INT);
            $stmt->bindValue(':photo', $photo);
            $stmt->bindValue(':describing', $describing);
            $stmt->bindValue(':id', $idMissing, PDO::PARAM_INT);
            $stmt->execute();

            header("Location: fiche.php?id=" . $idMissing);
            exit;
        } catch (PDOException $e) {
            error_log("Erreur PDO : " . $e->getMessage());
            $errors[] = "Une erreur est survenue lors de la mise à jour.";
            // Si nouvelle photo déplacée, la supprimer
            if ($photo !== $row['photo'] && file_exists($photo)) {
                unlink($photo);
            }
        }
    }

    // Mettre à jour les données du formulaire pour réafficher avec les valeurs saisies
    // On reconstruit formData avec ce qui a été saisi pour réafficher
    $formData = array_merge($row, [
        'firstName' => $firstName,
        'lastName' => $lastName,
        'birthDate' => $birthDate,
        'BirthPlace' => $BirthPlace,
        'ageOfMissing' => $ageOfMissing,
        'Sexe' => $Sexe,
        'currentAdress' => $currentAdress,
        'previousAdress' => $previousAdress,
        'city' => $city,
        'wilaya' => $wilaya,
        'cityOfMissing' => $cityOfMissing,
        'wilayaOfMissing' => $wilayaOfMissing,
        'dateOfMissing' => $dateOfMissing,
        'phone' => $phone,
        'height' => $height,
        'weight' => $weight,
        'hair' => $hair,
        'eyes' => $eyes,
        'blood' => $blood,
        'bloodPressure' => $bloodPressure,
        'diabet' => $diabet,
        'mentalIlness' => $mentalIlness,
        'tatoos' => $tatoos,
        'birthmarks' => $birthmarks,
        'scars' => $scars,
        'describing' => $describing,
        'photo' => $photo
    ]);
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
    <title>Update Missing</title>
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
                <ul>
                    <li><a
                            href="<?php echo isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] ? 'admin.php?id=' . (int) $_SESSION['iduser'] : 'profil.php?id=' . (int) $_SESSION['iduser']; ?>">Profil</a>
                    </li>
                    <li><a href="missing.php">Missing</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <div class="missing_form">
            <div>
                <h1>Update Missing Person</h1>
            </div>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <div class="form">
                <form action="" enctype="multipart/form-data" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <!-- First Name / Last Name -->
                    <div>
                        <div>
                            <label for="firstName">First Name :</label>
                            <input type="text" name="firstName" id="firstName"
                                value="<?php echo htmlspecialchars($formData['firstName'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label for="lastName">Last Name :</label>
                            <input type="text" name="lastName" id="lastName"
                                value="<?php echo htmlspecialchars($formData['lastName'] ?? ''); ?>" />
                        </div>
                    </div>
                    <!-- Birth Date / Birth Place -->
                    <div>
                        <div>
                            <label for="birthDate">Birth Date :</label>
                            <input type="date" name="birthDate" id="birthDate"
                                value="<?php echo htmlspecialchars($formData['birthDate'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label for="BirthPlace">Birth Place :</label>
                            <input type="text" name="BirthPlace" id="BirthPlace"
                                value="<?php echo htmlspecialchars($formData['BirthPlace'] ?? ''); ?>" />
                        </div>
                    </div>
                    <!-- Age Of Missing / Sexe -->
                    <div>
                        <div>
                            <label for="ageOfMissing">Age Of Missing :</label>
                            <input type="number" name="ageOfMissing" id="ageOfMissing"
                                value="<?php echo htmlspecialchars($formData['ageOfMissing'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label for="Sexe">Sexe :</label>
                            <select name="Sexe" id="Sexe">
                                <option value="Male" <?php if (($formData['Sexe'] ?? '') === 'Male')
                                    echo 'selected'; ?>>
                                    Male</option>
                                <option value="Female" <?php if (($formData['Sexe'] ?? '') === 'Female')
                                    echo 'selected'; ?>>Female</option>
                            </select>
                        </div>
                    </div>
                    <!-- Current Address -->
                    <div>
                        <label for="currentAdress">Current Adress :</label>
                        <input type="text" name="currentAdress" id="currentAdress"
                            value="<?php echo htmlspecialchars($formData['currentAdress'] ?? ''); ?>" />
                    </div>
                    <!-- Previous Address -->
                    <div>
                        <label for="previousAdress">Previous Adress :</label>
                        <input type="text" name="previousAdress" id="previousAdress"
                            value="<?php echo htmlspecialchars($formData['previousAdress'] ?? ''); ?>" />
                    </div>
                    <!-- City / Wilaya -->
                    <div>
                        <div>
                            <label for="city">City :</label>
                            <input type="text" name="city" id="city"
                                value="<?php echo htmlspecialchars($formData['city'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label for="wilaya">Wilaya :</label>
                            <select name="wilaya" id="wilaya">
                                <?php $wilayas = ['Alger', 'Annaba', 'Oran', 'Constantine', 'Tipaza', 'Boumerdes']; ?>
                                <?php foreach ($wilayas as $w): ?>
                                    <option value="<?php echo $w; ?>" <?php if (($formData['wilaya'] ?? '') === $w)
                                           echo 'selected'; ?>><?php echo $w; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- City Of Missing / Wilaya Of Missing -->
                    <div>
                        <div>
                            <label for="cityOfMissing">City Of Missing :</label>
                            <input type="text" name="cityOfMissing" id="cityOfMissing"
                                value="<?php echo htmlspecialchars($formData['cityOfMissing'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label for="wilayaOfMissing">Wilaya Of Missing :</label>
                            <select name="wilayaOfMissing" id="wilayaOfMissing">
                                <?php foreach ($wilayas as $w): ?>
                                    <option value="<?php echo $w; ?>" <?php if (($formData['wilayaOfMissing'] ?? '') === $w)
                                           echo 'selected'; ?>><?php echo $w; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- Date Of Missing / Phone -->
                    <div>
                        <div>
                            <label for="dateOfMissing">Date Of Missing :</label>
                            <input type="date" name="dateOfMissing" id="dateOfMissing"
                                value="<?php echo htmlspecialchars($formData['dateOfMissing'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label for="phone">Phone :</label>
                            <input type="tel" name="phone" id="phone"
                                value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>" />
                        </div>
                    </div>
                    <!-- Height / Weight + Hair / Eyes -->
                    <div>
                        <div>
                            <label for="height">Height :</label><input type="number" name="height" id="height"
                                value="<?php echo htmlspecialchars($formData['height'] ?? ''); ?>" placeholder="Cm" />
                            <label for="weight">Weight :</label><input type="number" name="weight" id="weight"
                                value="<?php echo htmlspecialchars($formData['weight'] ?? ''); ?>" placeholder="Kg" />
                        </div>
                        <div>
                            <label for="hair">Hair :</label><input type="text" name="hair" id="hair"
                                value="<?php echo htmlspecialchars($formData['hair'] ?? ''); ?>" placeholder="Color" />
                            <label for="eyes">Eyes :</label><input type="text" name="eyes" id="eyes"
                                value="<?php echo htmlspecialchars($formData['eyes'] ?? ''); ?>" placeholder="Color" />
                        </div>
                    </div>
                    <!-- Photo / Blood + checkboxes -->
                    <div>
                        <div>
                            <label for="photo">Photo :<input type="file" name="photo" id="photo" />
                                <span class="file">Upload ...</span></label>
                            <label for="blood">Blood :</label>
                            <select name="blood" id="blood">
                                <?php $bloods = ['A', 'B', 'O', 'AB']; ?>
                                <?php foreach ($bloods as $b): ?>
                                    <option value="<?php echo $b; ?>" <?php if (($formData['blood'] ?? '') === $b)
                                           echo 'selected'; ?>><?php echo $b; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="bloodPressure" class="label">Blood Pressure :<input type="checkbox"
                                    name="bloodPressure" id="bloodPressure" <?php if (!empty($formData['bloodPressure']))
                                        echo 'checked'; ?> />
                                <span class="checkmark"></span></label>
                            <label for="diabet" class="label">diabet<input type="checkbox" name="diabet" id="diabet"
                                    <?php if (!empty($formData['diabet']))
                                        echo 'checked'; ?> />
                                <span class="checkmark"></span></label>
                        </div>
                    </div>
                    <!-- Mental Ilness / Tatoos + Birthmarks / Scars -->
                    <div>
                        <div>
                            <label for="mentalIlness" class="label">Mental Ilness :<input type="checkbox"
                                    name="mentalIlness" id="mentalIlness" <?php if (!empty($formData['mentalIlness']))
                                        echo 'checked'; ?> />
                                <span class="checkmark"></span></label>
                            <label for="tatoos" class="label">Tatoos :<input type="checkbox" name="tatoos" id="tatoos"
                                    <?php if (!empty($formData['tatoos']))
                                        echo 'checked'; ?> />
                                <span class="checkmark"></span></label>
                        </div>
                        <div>
                            <label for="birthmarks" class="label">Birthmarks<input type="checkbox" name="birthmarks"
                                    id="birthmarks" <?php if (!empty($formData['birthmarks']))
                                        echo 'checked'; ?> />
                                <span class="checkmark"></span></label>
                            <label for="scars" class="label">scars<input type="checkbox" name="scars" id="scars" <?php if (!empty($formData['scars']))
                                echo 'checked'; ?> />
                                <span class="checkmark"></span></label>
                        </div>
                    </div>
                    <!-- Describing -->
                    <div>
                        <textarea name="describing" id="describing"
                            placeholder="Describe the circumstances of the disappearance ..."><?php echo htmlspecialchars($formData['describing'] ?? ''); ?></textarea>
                    </div>
                    <!-- Submit -->
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