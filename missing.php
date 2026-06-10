<?php
session_start();
ini_set('display_errors', 'off');
include 'connectdb.php';

if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

if (!isset($_SESSION['iduser'])) {
    header("Location: login.php");
    exit;
}

$errors = [];
$formData = [
    'firstName' => '',
    'lastName' => '',
    'birthDate' => '',
    'birthPlace' => '',
    'ageOfMissing' => '',
    'sexe' => '',
    'currentAdress' => '',
    'previousAdress' => '',
    'city' => '',
    'wilaya' => '',
    'cityOfMissing' => '',
    'wilayaOfMissing' => '',
    'dateOfMissing' => '',
    'phone' => '',
    'height' => '',
    'weight' => '',
    'hair' => '',
    'eyes' => '',
    'blood' => '',
    'describing' => ''
];
$checkboxes = ['bloodPressure', 'diabet', 'mentalIlness', 'tatoos', 'birthmarks', 'scars'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    foreach ($formData as $field => $default) {
        $formData[$field] = trim($_POST[$field] ?? '');
    }
    foreach ($checkboxes as $cb) {
        $formData[$cb] = isset($_POST[$cb]) ? 1 : 0;
    }

    $required = ['firstName', 'lastName', 'birthDate', 'birthPlace', 'ageOfMissing', 'sexe', 'city', 'wilaya', 'dateOfMissing'];
    foreach ($required as $field) {
        if ($formData[$field] === '') {
            $errors[$field] = "Ce champ est requis.";
        }
    }

    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $errors['photo'] = "Veuillez télécharger une photo valide.";
    } else {
        $file = $_FILES['photo'];
        $allowedExt = ['jpg', 'jpeg', 'png'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $maxSize = 2 * 1024 * 1024; // 2 Mo

        if (!in_array($fileExt, $allowedExt)) {
            $errors['photo'] = "Format d'image non autorisé (jpg, jpeg, png).";
        } elseif ($file['size'] > $maxSize) {
            $errors['photo'] = "L'image ne doit pas dépasser 2 Mo.";
        } elseif (!@getimagesize($file['tmp_name'])) {
            $errors['photo'] = "Le fichier n'est pas une image valide.";
        }
    }

    if (empty($errors)) {
        $photoDir = './images/';
        if (!is_dir($photoDir)) {
            mkdir($photoDir, 0755, true);
        }
        $photoName = bin2hex(random_bytes(16)) . '.' . $fileExt;
        $photoPath = $photoDir . $photoName;

        if (!move_uploaded_file($file['tmp_name'], $photoPath)) {
            $errors['photo'] = "Erreur lors du déplacement du fichier.";
        } else {
            try {
                $sql = "INSERT INTO missing (
                            user_iduser, firstName, lastName, birthDate, BirthPlace, ageOfMissing, Sexe,
                            currentAdress, previousAdress, city, wilaya, cityOfMissing, wilayaOfMissing,
                            dateOfMissing, phone, height, weight, hair, eyes, photo,
                            blood, bloodPressure, diabet, mentalIlness, tatoos, birthmarks, scars, describing
                        ) VALUES (
                            :user_iduser, :firstName, :lastName, :birthDate, :birthPlace, :ageOfMissing, :Sexe,
                            :currentAdress, :previousAdress, :city, :wilaya, :cityOfMissing, :wilayaOfMissing,
                            :dateOfMissing, :phone, :height, :weight, :hair, :eyes, :photo,
                            :blood, :bloodPressure, :diabet, :mentalIlness, :tatoos, :birthmarks, :scars, :describing
                        )";

                $stmt = $db->prepare($sql);

                $stmt->bindValue(':user_iduser', $_SESSION['iduser'], PDO::PARAM_INT);
                $stmt->bindValue(':firstName', $formData['firstName']);
                $stmt->bindValue(':lastName', $formData['lastName']);
                $stmt->bindValue(':birthDate', $formData['birthDate']);
                $stmt->bindValue(':birthPlace', $formData['birthPlace']);
                $stmt->bindValue(':ageOfMissing', $formData['ageOfMissing'], PDO::PARAM_INT);
                $stmt->bindValue(':Sexe', $formData['sexe']);
                $stmt->bindValue(':currentAdress', $formData['currentAdress']);
                $stmt->bindValue(':previousAdress', $formData['previousAdress']);
                $stmt->bindValue(':city', $formData['city']);
                $stmt->bindValue(':wilaya', $formData['wilaya']);
                $stmt->bindValue(':cityOfMissing', $formData['cityOfMissing']);
                $stmt->bindValue(':wilayaOfMissing', $formData['wilayaOfMissing']);
                $stmt->bindValue(':dateOfMissing', $formData['dateOfMissing']);
                $stmt->bindValue(':phone', $formData['phone']);
                $stmt->bindValue(':height', $formData['height'], PDO::PARAM_INT);
                $stmt->bindValue(':weight', $formData['weight'], PDO::PARAM_INT);
                $stmt->bindValue(':hair', $formData['hair']);
                $stmt->bindValue(':eyes', $formData['eyes']);
                $stmt->bindValue(':photo', $photoPath);
                $stmt->bindValue(':blood', $formData['blood']);
                $stmt->bindValue(':bloodPressure', $formData['bloodPressure'], PDO::PARAM_INT);
                $stmt->bindValue(':diabet', $formData['diabet'], PDO::PARAM_INT);
                $stmt->bindValue(':mentalIlness', $formData['mentalIlness'], PDO::PARAM_INT);
                $stmt->bindValue(':tatoos', $formData['tatoos'], PDO::PARAM_INT);
                $stmt->bindValue(':birthmarks', $formData['birthmarks'], PDO::PARAM_INT);
                $stmt->bindValue(':scars', $formData['scars'], PDO::PARAM_INT);
                $stmt->bindValue(':describing', $formData['describing']);

                $stmt->execute();

                header("Location: index.php");
                exit;
            } catch (PDOException $e) {
                error_log("Erreur PDO : " . $e->getMessage());
                $errors['db'] = "Une erreur est survenue lors de l'enregistrement. Veuillez réessayer.";
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }
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
    <title>Missing</title>
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
                <h1>Missing Person</h1>
            </div>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($errors as $field => $msg): ?>
                            <li><?php echo htmlspecialchars($msg); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <div class="form">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data"
                    method="POST">
                    <!-- First Name / Last Name -->
                    <div>
                        <div>
                            <label for="firstName">First Name :</label>
                            <input type="text" name="firstName" id="firstName" placeholder="First Name"
                                value="<?php echo htmlspecialchars($formData['firstName']); ?>" />
                            <?php if (isset($errors['firstName'])): ?><span
                                    class="error"><?php echo htmlspecialchars($errors['firstName']); ?></span><?php endif; ?>
                        </div>
                        <div>
                            <label for="lastName">Last Name :</label>
                            <input type="text" name="lastName" id="lastName" placeholder="Last Name"
                                value="<?php echo htmlspecialchars($formData['lastName']); ?>" />
                            <?php if (isset($errors['lastName'])): ?><span
                                    class="error"><?php echo htmlspecialchars($errors['lastName']); ?></span><?php endif; ?>
                        </div>
                    </div>
                    <!-- Birth Date / Birth Place + Age Of Missing / Sexe -->
                    <div>
                        <div>
                            <label for="birthDate">Birth Date :</label>
                            <input type="date" name="birthDate" id="birthDate"
                                value="<?php echo htmlspecialchars($formData['birthDate']); ?>" />
                            <?php if (isset($errors['birthDate'])): ?><span
                                    class="error"><?php echo htmlspecialchars($errors['birthDate']); ?></span><?php endif; ?>
                        </div>
                        <div>
                            <label for="birthPlace">Birth Place :</label>
                            <input type="text" name="birthPlace" id="birthPlace" placeholder="Birth Place"
                                value="<?php echo htmlspecialchars($formData['birthPlace']); ?>" />
                            <?php if (isset($errors['birthPlace'])): ?><span
                                    class="error"><?php echo htmlspecialchars($errors['birthPlace']); ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <div>
                            <label for="ageOfMissing">Age Of Missing :</label>
                            <input type="number" name="ageOfMissing" id="ageOfMissing" placeholder="Age Of Missing"
                                value="<?php echo htmlspecialchars($formData['ageOfMissing']); ?>" />
                            <?php if (isset($errors['ageOfMissing'])): ?><span
                                    class="error"><?php echo htmlspecialchars($errors['ageOfMissing']); ?></span><?php endif; ?>
                        </div>
                        <div>
                            <label for="sexe">Sexe :</label>
                            <select name="sexe" id="sexe">
                                <option value="">sexe</option>
                                <option value="Male" <?php if ($formData['sexe'] === 'Male')
                                    echo 'selected'; ?>>Male
                                </option>
                                <option value="Female" <?php if ($formData['sexe'] === 'Female')
                                    echo 'selected'; ?>>Female
                                </option>
                            </select>
                            <?php if (isset($errors['sexe'])): ?><span
                                    class="error"><?php echo htmlspecialchars($errors['sexe']); ?></span><?php endif; ?>
                        </div>
                    </div>
                    <!-- Current Address -->
                    <div>
                        <label for="currentAdress">Current Adress :</label>
                        <input type="text" name="currentAdress" id="currentAdress" placeholder="Current Adress"
                            value="<?php echo htmlspecialchars($formData['currentAdress']); ?>" />
                    </div>
                    <!-- Previous Address -->
                    <div>
                        <label for="previousAdress">Previous Adress :</label>
                        <input type="text" name="previousAdress" id="previousAdress" placeholder="Previous Adress"
                            value="<?php echo htmlspecialchars($formData['previousAdress']); ?>" />
                    </div>
                    <!-- City / Wilaya -->
                    <div>
                        <div>
                            <label for="city">City :</label>
                            <input type="text" name="city" id="city" placeholder="City"
                                value="<?php echo htmlspecialchars($formData['city']); ?>" />
                            <?php if (isset($errors['city'])): ?><span
                                    class="error"><?php echo htmlspecialchars($errors['city']); ?></span><?php endif; ?>
                        </div>
                        <div>
                            <label for="wilaya">Wilaya :</label>
                            <select name="wilaya" id="wilaya">
                                <option value="">wilaya</option>
                                <?php $wilayas = ['Alger', 'Annaba', 'Oran', 'Constantine', 'Tipaza', 'Boumerdes'];
                                foreach ($wilayas as $w): ?>
                                    <option value="<?php echo $w; ?>" <?php if ($formData['wilaya'] === $w)
                                           echo 'selected'; ?>><?php echo $w; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['wilaya'])): ?><span
                                    class="error"><?php echo htmlspecialchars($errors['wilaya']); ?></span><?php endif; ?>
                        </div>
                    </div>
                    <!-- City Of Missing / Wilaya Of Missing -->
                    <div>
                        <div>
                            <label for="cityOfMissing">City Of Missing :</label>
                            <input type="text" name="cityOfMissing" id="cityOfMissing" placeholder="City Of Missing"
                                value="<?php echo htmlspecialchars($formData['cityOfMissing']); ?>" />
                        </div>
                        <div>
                            <label for="wilayaOfMissing">Wilaya Of Missing :</label>
                            <select name="wilayaOfMissing" id="wilayaOfMissing">
                                <option value="">wilaya</option>
                                <?php foreach ($wilayas as $w): ?>
                                    <option value="<?php echo $w; ?>" <?php if ($formData['wilayaOfMissing'] === $w)
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
                                value="<?php echo htmlspecialchars($formData['dateOfMissing']); ?>" />
                            <?php if (isset($errors['dateOfMissing'])): ?><span
                                    class="error"><?php echo htmlspecialchars($errors['dateOfMissing']); ?></span><?php endif; ?>
                        </div>
                        <div>
                            <label for="phone">Phone :</label>
                            <input type="tel" name="phone" id="phone" placeholder="Phone"
                                value="<?php echo htmlspecialchars($formData['phone']); ?>" />
                        </div>
                    </div>
                    <!-- Height / Weight -->
                    <div>
                        <div>
                            <label for="height">Height :</label>
                            <input type="number" name="height" id="height" placeholder="Height (Cm)"
                                value="<?php echo htmlspecialchars($formData['height']); ?>" />
                            <label for="weight">Weight :</label>
                            <input type="number" name="weight" id="weight" placeholder="Weight (Kg)"
                                value="<?php echo htmlspecialchars($formData['weight']); ?>" />
                        </div>
                        <div>
                            <label for="hair">Hair :</label>
                            <input type="text" name="hair" id="hair" placeholder="Hair Color"
                                value="<?php echo htmlspecialchars($formData['hair']); ?>" />
                            <label for="eyes">Eyes :</label>
                            <input type="text" name="eyes" id="eyes" placeholder="Eyes Color"
                                value="<?php echo htmlspecialchars($formData['eyes']); ?>" />
                        </div>
                    </div>
                    <!-- Photo / Blood + checkboxes -->
                    <div>
                        <div>
                            <label for="photo">Photo :<input type="file" name="photo" id="photo" />
                                <span class="file">Upload ...</span></label>
                            <?php if (isset($errors['photo'])): ?><span
                                    class="error"><?php echo htmlspecialchars($errors['photo']); ?></span><?php endif; ?>
                            <label for="blood">Blood :</label>
                            <select name="blood" id="blood">
                                <option value="A" <?php if ($formData['blood'] === 'A')
                                    echo 'selected'; ?>>A</option>
                                <option value="B" <?php if ($formData['blood'] === 'B')
                                    echo 'selected'; ?>>B</option>
                                <option value="O" <?php if ($formData['blood'] === 'O')
                                    echo 'selected'; ?>>O</option>
                                <option value="AB" <?php if ($formData['blood'] === 'AB')
                                    echo 'selected'; ?>>AB</option>
                            </select>
                        </div>
                        <div>
                            <label for="bloodPressure" class="label">Blood Pressure :<input type="checkbox"
                                    name="bloodPressure" id="bloodPressure" value="1" <?php if (!empty($formData['bloodPressure']))
                                        echo 'checked'; ?> />
                                <span class="checkmark"></span></label>
                            <label for="diabet" class="label">diabet<input type="checkbox" name="diabet" id="diabet"
                                    value="1" <?php if (!empty($formData['diabet']))
                                        echo 'checked'; ?> />
                                <span class="checkmark"></span>
                            </label>
                        </div>
                    </div>
                    <!-- Mental Ilness / Tatoos + Birthmarks / Scars -->
                    <div>
                        <div>
                            <label for="mentalIlness" class="label">Mental Ilness :<input type="checkbox"
                                    name="mentalIlness" id="mentalIlness" value="1" <?php if (!empty($formData['mentalIlness']))
                                        echo 'checked'; ?> />
                                <span class="checkmark"></span></label>
                            <label for="tatoos" class="label">Tatoos :<input type="checkbox" name="tatoos" id="tatoos"
                                    value="1" <?php if (!empty($formData['tatoos']))
                                        echo 'checked'; ?> />
                                <span class="checkmark"></span></label>
                        </div>
                        <div>
                            <label for="birthmarks" class="label">Birthmarks<input type="checkbox" name="birthmarks"
                                    id="birthmarks" value="1" <?php if (!empty($formData['birthmarks']))
                                        echo 'checked'; ?> />
                                <span class="checkmark"></span></label>
                            <label for="scars" class="label">scars<input type="checkbox" name="scars" id="scars"
                                    value="1" <?php if (!empty($formData['scars']))
                                        echo 'checked'; ?> />
                                <span class="checkmark"></span></label>
                        </div>
                    </div>
                    <!-- Describing -->
                    <div>
                        <textarea name="describing" id="describing"
                            placeholder="Describe the circumstances of the disappearance ..."><?php echo htmlspecialchars($formData['describing']); ?></textarea>
                    </div>
                    <!-- Submit -->
                    <div>
                        <button type="submit" name="create">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>

</html>