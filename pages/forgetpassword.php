<?php include "../database/db.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <div class="form-box">

        <h2>Forgot Password</h2>

        <p style="text-align:center; margin-bottom:20px; color:#666;">
            Enter your email address and we'll send you a link to reset your password.
        </p>

        <form action="sendreset.php" method="POST">

            <input
                type="email"
                name="mail"
                placeholder="Email Address"
                required
            >

            <button type="submit">
                Send Reset Link
            </button>

        </form>

        <div class="links">

            <p>
                Remember your password?
                <a href="index.php">Back to Login</a>
            </p>

        </div>

    </div>

</div>

</body>
</html>