<?php
session_start();
include("../database/db.php");

if(!isset($_SESSION["user"])){
    header("Location: index.php");
    exit;
}

if(!isset($_GET["id"])){
    header("Location: admin.php");
    exit;
}

$id = $_GET["id"];

$stmt = $conn-> prepare("SELECT id, name, mail FROM users WHERE id = ? ");
$stmt -> bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Kullanıcı bulunamadı.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>View User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-page">

    <div class="admin-container">
        <div class="admin-card user-view-card">

            <h2>User Details</h2>

            <div class="user-info">
                <p><strong>ID:</strong> <?= $user["id"]; ?></p>
                <p><strong>Name:</strong> <?= $user["name"]; ?></p>
                <p><strong>Mail:</strong> <?= $user["mail"]; ?></p>
            </div>

            <a href="admin.php" class="back-btn">Back to Admin</a>

        </div>
    </div>

</body>
</html>