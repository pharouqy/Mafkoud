<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="./css/style.css" />
    <title>Profil</title>
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
                <?php 
                    session_start();
                    ini_set('display_errors', 'on');
                    include 'connectdb.php';
                    $id = $_GET['id'];
                    $sql = "SELECT * FROM user WHERE iduser = $id";
                    $result = $db->query($sql);
                    $row = $result->fetch();
                ?>
                <div>
                    <p>Pseudo: <span id="pseudo"><?php echo $row['pseudo'] ?></span></p>
                    <p>Full Name: <span id="name"><?php echo $row['fullName'] ?></span></p>
                    <p>Phone Number: <span id="phone"><?php echo $row['phone'] ?></span></p>
                    <p>Email: <span id="mail"><?php echo $row['email'] ?></span></p>
                </div>
            </div>
            <div>
                <div><button>Update</button><button>Delete</button></div>
            </div>
            <div id="label">
                <div>
                    <h1>Missing Persons ...</h1>
                </div>
                <?php 
                    $sql = "SELECT * FROM missing WHERE user_idUser = $id ORDER BY idMissing DESC";
                    $result = $db->query($sql);
                    while ($row = $result->fetch()) {
                        $_id = $row['idmissing'];
                        echo '<article>';
                        echo '<div>';
                        echo '<h2>ID : ' . $row['idmissing'] . ' --- ' . $row['lastName'] . " " . $row['firstName'] . '</h2>';
                        echo '</div>';
                        echo '<div>';
                        if(isset($_POST['find'])) {
                            $sql = "UPDATE missing SET isFind = 1 WHERE idmissing = $_id";
                            $db->query($sql);
                            header('Location: profil.php?id=' . $id);
                        }
                        echo '<form action="" method="POST">';
                        echo '<button type="submit" name="find">';
                        if ($row['isFind'] == 1) {
                            echo '<img src="./assets/images/isFind.png" alt="isFind" title="isFind">';
                        } else {
                            echo '<img src="./assets/images/isNotFind.png" alt="isNotFind" title="isNotFind">';
                        }
                        echo '</button>';
                        echo '</form>';
                        echo '<form action="" method="POST">';
                        echo '<button type="submit">';
                        echo '<img src="./assets/images/Update.png" alt="Update">';
                        echo '</button>';
                        echo '</form>';
                        if (isset($_POST['delete'])) {
                            $sql = "DELETE FROM missing WHERE idmissing = $_id";
                            $result = $db->query($sql);
                            header('Location: profil.php?id=' . $id);
                        }
                        echo '<form action="" method="POST">';
                        echo '<button type="submit" name="delete">';
                        echo '<img src="./assets/images/Delete.png" alt="Delete">';
                        echo '</button>';
                        echo '</form>';
                        echo '</div>';
                        echo '</article>';
                    }
                ?>
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