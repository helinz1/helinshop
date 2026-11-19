<?php
include("../database/db.php");

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

http_response_code(403);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>403 Access Denied</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-page">

<div class="admin-wrapper">
    <div class="admin-card user-view-card">

        <h2>🚫 403 Access Denied</h2>

        <p class="error-message">
            Bu sayfaya erişim yetkiniz yok.
        </p>

        <a href="user.php?id=<?= $_SESSION["user"]["id"]; ?>" class="back-btn">
            Back to My Profile
        </a>

    </div>
</div>

</body>
</html>