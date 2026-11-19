
<?php
include("../database/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $token = $_POST["token"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password != $confirm_password) {
    header("Location: reset-password.php?token=" . $token . "&error=match");
    exit;
} else {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $update = $conn->prepare("
                UPDATE users
                SET password = ?, reset_token = NULL, reset_expires = NULL
                WHERE reset_token = ?
            ");

            $update->bind_param("ss", $hashedPassword, $token);
            $update->execute();

            $message = "🎉 Your password has been successfully updated!";
            $messageType = "success";

        } else {

            $message = "❌ This reset link is invalid or has expired.";
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

    <title>Password Updated</title>

    <link rel="stylesheet" href="style.css">

</head>
<body>

<div class="container">

    <div class="form-box">

        <h2>Password Reset</h2>

        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>

        <div class="links">

            <?php if($messageType == "success"){ ?>

                <p>
                    Your password has been changed successfully.
                </p>

                <p>
                    <a href="index.php">← Back to Login</a>
                </p>

            <?php } else { ?>

                <p>
                    <a href="forgetpassword.php">Request a New Reset Link</a>
                </p>

                <p>
                    <a href="index.php">Back to Login</a>
                </p>

            <?php } ?>

        </div>

    </div>

</div>

</body>
</html>

