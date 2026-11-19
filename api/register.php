<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../database/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Sadece POST isteği kabul edilir."
    ]);
    exit;
}

$conn = new mysqli(
    DB_HOST,
    DB_USER,
    DB_PASSWORD,
    DB_NAME
);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Veritabanı bağlantı hatası."
    ]);
    exit;
}

$name = trim($_POST["name"] ?? "");
$mail = trim($_POST["mail"] ?? "");
$password = trim($_POST["password"] ?? "");

if (
    empty($name) ||
    empty($mail) ||
    empty($password)
) {
    echo json_encode([
        "success" => false,
        "message" => "Lütfen bütün alanları doldurun."
    ]);
    exit;
}

$checkStmt = $conn->prepare("
    SELECT id
    FROM users
    WHERE mail = ?
");

if (!$checkStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Mail kontrol sorgusu hazırlanamadı."
    ]);
    exit;
}

$checkStmt->bind_param("s", $mail);

$checkStmt->execute();

$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Bu mail zaten kayıtlı."
    ]);

    $checkStmt->close();
    $conn->close();
    exit;
}

$checkStmt->close();

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);

$insertStmt = $conn->prepare("
    INSERT INTO users (
        name,
        mail,
        password
    )
    VALUES (
        ?,
        ?,
        ?
    )
");

if (!$insertStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Kayıt sorgusu hazırlanamadı."
    ]);
    exit;
}

$insertStmt->bind_param(
    "sss",
    $name,
    $mail,
    $hashedPassword
);

if ($insertStmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Kayıt başarıyla oluşturuldu."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Kayıt oluşturulamadı."
    ]);
}

$insertStmt->close();
$conn->close();