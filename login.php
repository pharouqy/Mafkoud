<?php
session_start();
ini_set('display_errors', 'on');
//connect to database
include 'connectdb.php';
if($db){
  if(isset($_POST['login']))
  {
      $pseudo=$_POST['pseudo'] ;
      $password=$_POST['password'];
      $password=md5($password); //Remember we hashed password before storing last time
      $stmt="SELECT * FROM user WHERE  pseudo='$pseudo' AND password='$password'";
      $result=$db->prepare($stmt);
      $result->execute();
      $row = $result->fetch();
      if($result->rowCount()>0)
      {
        $_SESSION['pseudo']=$pseudo;
        $_SESSION['iduser']=$row['iduser'];
        $_SESSION['isAdmin']=$row['isAdmin'];
        header("location:index.php");
      }
      else
      {
        $_SESSION['message']="Username/password combination incorrect";
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
    <title>Login</title>
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
                <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
                    <div>
                        <label for="pseudo">Pseudo :</label>
                        <input type="text" name="pseudo" id="pseudo" />
                    </div>
                    <div>
                        <label for="password">Password :</label>
                        <input type="password" name="password" id="password" />
                    </div>
                    <div>
                        <button type="submit" name="login">Login</button>
                    </div>
                </form>
            </div>
        </div>
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