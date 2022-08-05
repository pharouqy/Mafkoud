<?php
session_start();
ini_set('display_errors', 'on');
//connect to database
include 'connectdb.php';

if(isset($_POST['register_btn']))
{
    $username=mysqli_real_escape_string($db,$_POST['pseudo']);
    $fullname=mysqli_real_escape_string($db,$_POST['fullName']);
    $phone=mysqli_real_escape_string($db,$_POST['phone']);
    $email=mysqli_real_escape_string($db,$_POST['email']);
    $password=mysqli_real_escape_string($db,$_POST['password']);
    $password2=mysqli_real_escape_string($db,$_POST['password2']);  
    $query = "SELECT * FROM user WHERE pseudo = '$username'";
    $result=mysqli_query($db,$query);
      if($result)
      {
     
        if( mysqli_num_rows($result) > 0)
        {
                
                echo '<script language="javascript">';
                echo 'alert("Username already exists")';
                echo '</script>';
        }
        
          else
          {
            
            if($password==$password2)
            {           //Create User
                $password=md5($password); //hash password before storing for security purposes
                $sql="INSERT INTO user(pseudo,fullname,phone,email,password) VALUES('$username','$fullname','$phone','$email','$password')";
                mysqli_query($db,$sql);  
                header("location:login.php");  //redirect home page
            }
            else
            {
                $_SESSION['message']="The two password do not match";   
            }
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
                <form method="post" action="register.php">
                    <table>
                        <tr>
                            <td>Pseudo : </td>
                            <td><input type="text" name="pseudo" class="textInput"></td>
                        </tr>
                        <tr>
                            <td>Full Name : </td>
                            <td><input type="text" name="fullName" class="textInput"></td>
                        </tr>
                        <tr>
                            <td>Phone : </td>
                            <td><input type="tel" name="phone" class="textInput"></td>
                        </tr>
                        <tr>
                            <td>Email : </td>
                            <td><input type="email" name="email" class="textInput"></td>
                        </tr>
                        <tr>
                            <td>Password : </td>
                            <td><input type="password" name="password" class="textInput"></td>
                        </tr>
                        <tr>
                            <td>Password again: </td>
                            <td><input type="password" name="password2" class="textInput"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="submit" name="register_btn" class="Register"></td>
                        </tr>
                    </table>

                </form>
            </div>

        </main>
    </div>

</body>

</html>