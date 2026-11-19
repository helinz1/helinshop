<?php
include("../database/db.php");

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: user.php");
    exit;
}

$product_id = $_GET["id"];

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: 403.php");
    exit;
}

if (
    $_SESSION["user"]["role"] != "admin" &&
    $product["user_id"] != $_SESSION["user"]["id"]
) {
    header("Location: 403.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST["title"];
    $price = $_POST["price"];
    $description = $_POST["description"];

    $imagePath = $product["image"];

    if ($_FILES["image"]["error"] == 0) {

        if (!empty($product["image"])) {
            unlink("../" . $product["image"]);
        }

        $imageOriginalName = $_FILES["image"]["name"];
        $imageTmp = $_FILES["image"]["tmp_name"];

        $imageExtension = pathinfo($imageOriginalName, PATHINFO_EXTENSION);
        $imageName = time() . "_" . uniqid() . "." . $imageExtension;

        $imagePath = "uploads/" . $imageName;
        $uploadPath = "../" . $imagePath;

        move_uploaded_file($imageTmp, $uploadPath);
    }

    $update = $conn->prepare(
        "UPDATE products 
         SET title = ?, price = ?, description = ?, image = ? 
         WHERE id = ?"
    );

    $update->bind_param("sdssi", $title, $price, $description, $imagePath, $product_id);

    if ($update->execute()) {
        header("Location: user.php");
        exit;
    } else {
        echo "Güncelleme başarısız.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-page">

<div class="admin-wrapper">
    <div class="admin-card user-view-card">

        <h2>Edit Product</h2>

        <form method="post" enctype="multipart/form-data" class="edit-form">

            <?php if (!empty($product["image"])): ?>
                <img 
                    src="../<?= $product["image"]; ?>" 
                    alt="Product Image" 
                    class="product-image">
            <?php endif; ?>

            <div class="input-box">
                <input 
                    type="text" 
                    name="title" 
                    value="<?= $product["title"]; ?>" 
                    required
                >
            </div>

            <div class="input-box">
                <input 
                    type="number" 
                    step="0.01" 
                    name="price" 
                    value="<?= $product["price"]; ?>" 
                    required
                >
            </div>

            <div class="input-box">
                <textarea name="description" rows="5"><?= $product["description"]; ?></textarea>
            </div>

            <div class="input-box">
                <input type="file" name="image" accept="image/*">
            </div>

            <button type="submit" class="save-btn">Save Product</button>

        </form>

        <a href="user.php" class="back-btn">
            Back to Profile
        </a>

    </div>
</div>

</body>
</html>