<?php
include("../database/db.php");

if ( !isset($_SESSION["user"])){
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
    echo "Ürün bulunamadı.";
    exit;
}

if (
    $_SESSION["user"]["role"] != "admin" &&
    $_SESSION["user"]["id"] != $product["user_id"]
) {
    header("Location: 403.php");
    exit;
}

$delete = $conn->prepare("DELETE FROM products WHERE id = ?");
$delete->bind_param("i", $product_id);

if ($delete->execute()) {
    header("Location: user.php");
    exit;
} else {
    echo "Ürün silinemedi.";
}
?>