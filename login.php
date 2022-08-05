<?php
session_start();
ini_set('display_errors', 'on');
if(isset($_SESSION['pseudo']) )
{
  header("location:home.php");
  die();
}
//connect to database
include 'connectdb.php';
if($db)
{
  if(isset($_POST['login_btn']))
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
    <title>Rabbani sarkar</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <hgroup>
            <h1 class="site-title" style="text-align: center; color: green;">Login, Registration, Logout</h1><br>
        </hgroup>

        <br>
        <nav class="navbar navbar-inverse">
            <div class="container-fluid">
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav center">
                        <li><a href="login.php">LogIN</a></li>
                        <li><a href="register.php">SignUp</a></li>
                        <li><a href="logout.php">LogOut</a></li>
                    </ul>

                </div>
            </div>
        </nav>

        <main class="main-content">
            <div class="col-md-6 col-md-offset-2">
                <?php
    if(isset($_SESSION['message']))
    {
         echo "<div id='error_msg'>".$_SESSION['message']."</div>";
         unset($_SESSION['message']);
    }
?>
                <form method="post" action="login.php">
                    <table>
                        <tr>
                            <td>Pseudo : </td>
                            <td><input type="text" name="pseudo" class="textInput"></td>
                        </tr>
                        <tr>
                            <td>Password : </td>
                            <td><input type="password" name="password" class="textInput"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="submit" name="login_btn" class="Log In"></td>
                        </tr>

                    </table>
                </form>
            </div>

        </main>
    </div>

</body>

</html>