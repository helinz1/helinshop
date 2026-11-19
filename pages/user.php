<?php
include("../database/db.php");

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

/* URL'ye başka id yazarsa 403'e gönder */
if (isset($_GET["id"]) && $_GET["id"] != $_SESSION["user"]["id"]) {
    header("Location: 403.php");
    exit;
}

/* Normal user kendi id'sini session'dan alır */
$user_id = $_SESSION["user"]["id"];

$stmt = $conn->prepare("SELECT id, name, mail, profile_image FROM users WHERE id = ?");

if (!$stmt) {
    die("User SQL hatası: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Kullanıcı bulunamadı.";
    exit;
}

$productStmt = $conn->prepare(
    "SELECT id, title, price, user_id, description, image, created_at 
     FROM products 
     WHERE user_id = ? 
     ORDER BY id DESC"
);

if (!$productStmt) {
    die("Product SQL hatası: " . $conn->error);
}

$productStmt->bind_param("i", $user_id);
$productStmt->execute();

$products = $productStmt->get_result();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>User Page</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="admin-page">

<div class="admin-wrapper">
    <div class="admin-card profile-card">

        <div class="profile-header">

            <?php if (!empty($user["profile_image"])): ?>

                <div 
                    class="avatar" 
                    style="background-image: url('../<?= $user["profile_image"]; ?>');">
                </div>

            <?php else: ?>

                <div class="avatar">
                    <?= strtoupper(substr($user["name"], 0, 1)); ?>
                </div>

            <?php endif; ?>

            <div>
                <h1><?= $user["name"]; ?>'s Page</h1>
                <p><?= $user["mail"]; ?></p>
            </div>

        </div>

        <div class="profile-actions">

            <a href="add-product.php" class="btn-edit">
                + Add Product
            </a>

            <a href="edit-profile.php" class="btn-view">
                Edit Profile
            </a>

            <a href="logout.php" class="btn-delete">
                Logout
            </a>

        </div>

        <h2>Products</h2>

        <?php if ($products->num_rows > 0): ?>

            <div class="product-grid">

                <?php while ($product = $products->fetch_assoc()): ?> 

    <div class="product-card">

        <?php if (!empty($product["image"])): ?>

            <img 
                src="../<?= $product["image"]; ?>" 
                alt="Product Image" 
                class="product-image">
        <?php else: ?>

                        <div class="product-image no-image">
                            <span>📷</span>
                            <p>No Image</p>
                        </div>
        <?php endif; ?>

        <h3><?= $product["title"]; ?></h3>

        <p><?= $product["description"]; ?></p>

        <strong><?= $product["price"]; ?> TL</strong>

        <small><?= $product["created_at"]; ?></small>

        <div class="product-actions">

            <a href="edit-product.php?id=<?= $product["id"]; ?>" class="btn-view">
                Edit Product
            </a>

            <a href="delete-product.php?id=<?= $product["id"]; ?>"
                class="btn-delete"
                onclick="return confirm('Are you sure you want to delete this product?');">
                    Delete Product
            </a>

        </div>

    </div>

<?php endwhile; ?>

            </div>

        <?php else: ?>

            <p class="empty-text">
                Bu kullanıcının henüz ürünü yok.
            </p>

        <?php endif; ?>

    </div>
</div>

</body>
</html>