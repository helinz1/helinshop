
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "../vendor/autoload.php";

include("../database/db.php");
    

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["mail"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE mail = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();


    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $token = bin2hex(random_bytes(32)); //32 bytlık hex token olustur 
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE mail = ?");
        $update->bind_param("sss", $token, $expires, $email);
        $update->execute();


        $resetLink = "http://helin.local/pages/reset-password.php?token=" . $token;
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;

        $mail->Username = "helinzengin6@gmail.com";

        $mail->Password = "";

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom("helinzengin6@gmail.com", "Password Reset");

        $mail->addAddress($email);

        $mail->isHTML(true);

        $mail->Subject = "Reset your password";

        $mail->Body = "
        <h3>Şifre Sıfırlama</h3>

        <p>Aşağıdaki linke tıklayın:</p>

        <a href='$resetLink'>$resetLink</a>

        <p>Bu link 1 saat geçerlidir.</p>
        ";

        $mail->send();


        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Sent</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <div class="form-box">

        <h2>📧 Check Your Email</h2>

        <div class="message success">
            If an account matches the information you entered,
            we'll send you an email with a link to reset your password.
        </div>

        <div class="links">

            <p>
                Didn't receive the email?
                <a href="forgetpassword.php">Try Again</a>
            </p>

            <p>
                <a href="index.php">Back to Login</a>
            </p>

        </div>

    </div>

</div>

</body>
</html>
<?php
exit;
        }else{
             echo "Bu mail adresi kayıtlı değil.";
        }
}