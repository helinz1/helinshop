<?php

include "../database/db.php";


 $message = "";
    $messageType = "";
if($_SERVER["REQUEST_METHOD"]=="POST"){


   
    $username=$_POST["username"];
    $mail=$_POST["mail"];
    $password=$_POST["password"];

    $check="SELECT * FROM users WHERE mail='$mail'";
    $result=$conn->query($check);

    if($result->num_rows>0){ //kayıtlı mail listesi cıkıyorsa karsmıza 

        $message = "This email is already registered!";
        $messageType = "error";
    }else{
        $password = password_hash($password, PASSWORD_DEFAULT);
        $sql="INSERT INTO users(name,mail,password)
              VALUES('$username','$mail','$password')";

        if($conn->query($sql)){
            $message = "Registration successful!";
            $messageType = "success";
        }else{
            $message = "Registration not successful!";
            $messageType = "error";
        }

    }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="form-box">

        <h2>Create Account</h2>

        <?php
        if(!empty($message)){
        ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php
        }
        ?>
        <form method="POST">

            <input type="text" name="username" placeholder="Username" required>

            <input type="email" name="mail" placeholder="Email" required>

            <input type="password" name="password" placeholder="Password" required>

            <button type="submit">Register</button>

        </form>

        <div class="links">

            <p>
                Already have an account?
                <a href="index.php">Sign In</a>
            </p>

        </div>

    </div>
</div>

</body>
</html>