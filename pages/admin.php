<?php 
session_start();
include("../database/db.php");

if(!isset($_SESSION["user"])){
    header("Location: index.php");
    exit;
}

if ($_SESSION["user"]["role"] != "admin") {
    header("Location: user.php?id=" . $_SESSION["user"]["id"]);
    exit;
}

$result = $conn->query("SELECT * FROM users");
$totalUsers = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="admin-page">

<div class="admin-wrapper">

    <div class="admin-card">

        <div class="admin-top">

            <div>
                
            <h1>Welcome, <?= $_SESSION["user"]["name"]; ?> 👋</h1>
                <p>Manage registered users</p>
            </div>

            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div class="stats-box">
            Total Users: <span><?= $totalUsers; ?></span>
        </div>

        <table class="admin-table">
            <tr>
                <th>NO</th>
                <th>ID</th>
                <th>Name</th>
                <th>Mail</th> 
                <th>Actions</th>
                
            </tr>
            <?php
            $no = 1;
            ?>

            <?php while($row = $result->fetch_assoc()){ ?>
                <tr>
                    <td><?= $no ?> </td>
                    <td><?= $row["id"]; ?></td>
                    <td><?= $row["name"]; ?></td>
                    <td><?= $row["mail"]; ?></td>
                    
                    <td class="actions">

                        <a href="view-user.php?id=<?= $row["id"]; ?>" class="btn-view">
    User Page
</a>

                        <a href="edit.php?id=<?= $row["id"] ?>" class="btn-edit">
                            Edit
                        </a>

                        <a href="delete.php?id=<?= $row["id"] ?>" class="btn-delete"
                        onclick="return confirm('Bu kullanıcı silinsin mi?')">
                            Delete
                        </a>

                    </td>
                </tr>
                <?php
                $no += 1;
                ?>
            <?php } ?>
        </table>

    </div>

</div>

</body>
</html>