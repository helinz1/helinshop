<?php include '../database/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    


    <div class="container">
        <?php if(!isset($_SESSION["user"])): ?>
        <div class="form-box" id="login-form">
            <form action="control.php" method="post">
                <h2>Login</h2>
                <input type="email" name="mail" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>

                <div class="links">

                <p>
                    Don't have an account?
                    <a href="register.php">Create one</a>
                </p>

                <p>
                    Forgot your password?
                    <a href="forgetpassword.php">Reset it</a>
                </p>

            </div>              
            </form>
        </div>
        <?php else: ?>
            Kullanıcı giriş yaptı 
            <a href="logout.php">Çıkış Yap</a>
        <?php endif;?> 
    </div>

</body>
</html>