<?php
include("../database/db.php");

$message = "";

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["user"]["id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST["title"];
    $price = $_POST["price"];
    $description = $_POST["description"];


    $imagePath = "";

    if ($_FILES["image"]["error"] == 0) { //kullancı resim sectiyse 0 secti demek
        $imageOriginalName = $_FILES["image"]["name"];
        $imageTmp = $_FILES["image"]["tmp_name"];

        $imageExtension = pathinfo($imageOriginalName, PATHINFO_EXTENSION);

        $imageName = time() . "_" . uniqid() . "." . $imageExtension;

        $imagePath = "uploads/" . $imageName;
        $uploadPath = "../" . $imagePath;

        move_uploaded_file($imageTmp, $uploadPath);
    }

    $stmt = $conn->prepare(
        "INSERT INTO products (user_id, title, price, description, image)
         VALUES (?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        die("SQL Prepare Hatası: " . $conn->error);
    }

    $stmt->bind_param("isdss", $user_id, $title, $price, $description, $imagePath);

    if ($stmt->execute()) {
        header("Location: user.php");
        exit;
    } else {
        $message = "Ürün eklenemedi: " . $stmt->error;
    }
}
?>


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="admin-page">

<div class="admin-wrapper">
    <div class="admin-card user-view-card">

        <h2>Add Product</h2>

        <?php if ($message != ""): ?>
    <p class="error-message"><?= $message; ?></p>
<?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <div class="input-box">
                <input type="text" name="title" placeholder="Product Name" required>
            </div>

            <div class="input-box">
                <input type="number" step="0.01" name="price" placeholder="Price" required>
            </div>

            <div class="input-box">
                <textarea name="description" placeholder="Description" rows="5"></textarea>
            </div>

            <div class="input-box">
                <input type="file" name="image" accept="image/*" >
            </div>

            <button type="submit" class="save-btn">Add Product</button>

        </form>

        <a href="user.php" class="back-btn">Back</a>

    </div>
</div>

</body>
</html>

