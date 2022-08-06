<?php
    session_start();
    ini_set('display_errors', 'on');
    include 'connectdb.php';

    $id = $_GET['id'];
    $sql = "SELECT * FROM missing WHERE idmissing = $id";
    $result = $db->prepare($sql);
    $result->execute();
    $missing = $result->fetch();
    
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="./css/style.css" />
    <title>Profil de <?php echo $missing['firstName'] . " " . $missing['lastName'] ?></title>
</head>

<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <p>Logo</p>
                </a>
            </div>
            <nav>
                <ul>
                    <li><a
                            href="<?php echo $_SESSION['isAdmin'] ? 'admin.php?id=' . $_SESSION['iduser'] :  'profil.php?id=' . $_SESSION['iduser']?>">Profil</a>
                    </li>
                    <li><a href="missing.php">Missing</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main id="profil">
        <aside>
            <div>
                <img src="<?php echo $missing['photo'] ?>" alt="missing Person">
            </div>
            <div>
                <p>Height :<span><?php echo $missing['height']  ?></span> Cm</p>
                <p>Weight :<span><?php echo $missing['weight']  ?></span> Kg</p>
                <p>Hair :<span><?php echo $missing['hair']  ?></span></p>
                <p>Eyes :<span><?php echo $missing['eyes']  ?></span></p>
                <p>Sexe :<span><?php echo $missing['Sexe']  ?></span></p>
                <p>Age Of Missing :<span><?php echo $missing['ageOfMissing']  ?></span> Years</p>
            </div>
        </aside>
        <section id="info">
            <div>
                <div>
                    <p>First Name :<span><?php echo $missing['firstName']  ?></span></p>
                    <p>Birth Date :<span><?php echo $missing['birthDate']  ?></span></p>
                    <p>Current Adress :<span><?php echo $missing['currentAdress']  ?></span></p>
                    <p>City :<span><?php echo $missing['city']  ?></span></p>
                    <p>City Of Missing :<span><?php echo $missing['cityOfMissing']  ?></span></p>
                    <p>Date Of Missing :<span><?php echo $missing['dateOfMissing']  ?></span></p>
                    <p>Blood :<span><?php echo $missing['blood']  ?></span></p>
                    <p>Diabet :<span><?php echo $missing['diabet'] ? "true" : "false"; ?></span></p>
                    <p>BirthMarks :<span><?php echo $missing['birthmarks'] ? "true" : "false";  ?></span></p>
                </div>
                <div>
                    <p>Last Name :<span><?php echo $missing['lastName']  ?></span></p>
                    <p>Birth Place :<span><?php echo $missing['BirthPlace']  ?></span></p>
                    <p>Previous Adress :<span><?php echo $missing['previousAdress']  ?></span></p>
                    <p>Wilaya :<span><?php echo $missing['wilaya']  ?></span></p>
                    <p>wilaya Of Missing :<span><?php echo $missing['wilayaOfMissing']  ?></span></p>
                    <p>Phone :<span>0<?php echo $missing['phone']  ?></span></p>
                    <p>Blood Pressure :<span><?php echo $missing['bloodPressure'] ? "true" : "false";  ?></span></p>
                    <p>Tatoos :<span><?php echo $missing['tatoos'] ? "true" : "false";  ?></span></p>
                    <p>Scars :<span><?php echo $missing['scars'] ? "true" : "false";  ?></span></p>
                </div>
            </div>
            <div>
                <p>Describing The Situation Of Missing :<span><?php echo $missing['describing']  ?></span></p>
            </div>
        </section>
    </main>
    <footer>
        <div>
            <a href="#"><img src="./assets/images/facebook.png" alt="facebook" /></a>
            <a href="#"><img src="./assets/images/twitter.png" alt="twitter" /></a>
            <a href="#"><img src="./assets/images/linkedin.png" alt="linkedin" /></a>
            <a href="#"><img src="./assets/images/github.png" alt="github" /></a>
        </div>
        <div>
            <p>© All Right Reserved 2022</p>
        </div>
    </footer>
    <script src="./js/script.js"></script>
</body>

</html>