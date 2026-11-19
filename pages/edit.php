<?php
session_start();
include("../database/db.php");

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}
if (!isset($_GET["id"])) {
    header("Location: admin.php");
    exit;
}

$id = $_GET["id"];
$message = "";

$stmt = $conn->prepare("SELECT id, name, mail FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"];
    $mail = $_POST["mail"];

    if (!$user) {
        echo "Kullanıcı bulunamadı.";
        exit;
    }

    $update = $conn->prepare("UPDATE users SET name = ?, mail = ? WHERE id = ?");
    $update->bind_param("ssi", $name, $mail, $id);


    if($update->execute()){
        header("Location: admin.php");
        exit;
    }else{
        $message = "Güncelleme baarısız.";
    }
}
?><!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-page">

    <div class="admin-container">
        <div class="admin-card user-view-card">

            <h2>Edit User</h2>

            <?php if ($message != ""): ?>
                <p class="error-message"><?= $message; ?></p>
            <?php endif; ?>

            <form method="post" class="edit-form">

                <div class="input-box">
                    <input 
                        type="text" 
                        name="name" 
                        value="<?= $user['name']; ?>" 
                        required
                    >
                </div>

                <div class="input-box">
                    <input 
                        type="email" 
                        name="mail" 
                        value="<?= $user['mail']; ?>" 
                        required
                    >
                </div>

                <button type="submit" class="save-btn">Save Changes</button>

            </form>

            <a href="admin.php" class="back-btn">Back to Admin</a>

        </div>
    </div>

</body>
</html>