
<?php
include("../database/db.php");

if (!isset($_GET["token"])) {
    $message = "❌ Invalid reset link.";
    $messageType = "error";
} else {

    $token = $_GET["token"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $message = "❌ This reset link is invalid or has expired.";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <div class="form-box">

        <h2>Reset Password</h2>

        <?php if(isset($_GET["error"]) && $_GET["error"] == "match"){ ?>
    <div class="message error">
        Passwords do not match. Please try again.
    </div>
<?php } ?>

        <?php if(isset($message)){ ?>

            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>

            <div class="links">

                <p>
                    <a href="forgetpassword.php">Request a New Reset Link</a>
                </p>

                <p>
                    <a href="index.php">Back to Login</a>
                </p>

            </div>

        <?php } else { ?>

            <p style="text-align:center; color:#666; margin-bottom:20px;">
                Please enter your new password below.
            </p>

            <form action="update-password.php" method="POST">

                <input
                    type="hidden"
                    name="token"
                    value="<?php echo $token; ?>"
                >

                <input
                    type="password"
                    name="password"
                    placeholder="New Password"
                    required
                >

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm New Password"
                    required
                >

                <button type="submit">
                    Update Password
                </button>

            </form>

            <div class="links">

                <p>
                    Remember your password?
                    <a href="index.php">Back to Login</a>
                </p>

            </div>

        <?php } ?>

    </div>

</div>

</body>
</html>
