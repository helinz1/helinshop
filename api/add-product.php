<?php

header("Content-Type: application/json; charset=UTF-8");

try {

    require_once __DIR__ . "/../database/db.php";

    // Sadece POST kabul et
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode([
            "success" => false,
            "message" => "Sadece POST isteği kabul edilir."
        ]);
        exit;
    }

    // Form verileri
    $title = trim($_POST["title"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $user_id = trim($_POST["user_id"] ?? "");
    $category_id = trim($_POST["category_id"] ?? "");

    // Zorunlu alanlar
    if (
        $title === "" ||
        $price === "" ||
        $description === "" ||
        $user_id === "" ||
        $category_id === ""
    ) {
        echo json_encode([
            "success" => false,
            "message" => "Başlık, fiyat, açıklama, kategori ve kullanıcı bilgisi zorunludur."
        ]);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | KATEGORİ KONTROLÜ
    |--------------------------------------------------------------------------
    */

    $categoryStmt = $conn->prepare("
        SELECT id
        FROM categories
        WHERE id = ?
    ");

    $categoryStmt->execute([$category_id]);

    $category = $categoryStmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        echo json_encode([
            "success" => false,
            "message" => "Geçersiz kategori seçildi."
        ]);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | RESİM İŞLEMLERİ
    |--------------------------------------------------------------------------
    */

    $imagePath = null;

    if (
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
            echo json_encode([
                "success" => false,
                "message" => "Resim yüklenirken bir hata oluştu."
            ]);
            exit;
        }

        $tmpName = $_FILES["image"]["tmp_name"];
        $fileSize = $_FILES["image"]["size"];

        // Maksimum 5 MB
        if ($fileSize > 5 * 1024 * 1024) {
            echo json_encode([
                "success" => false,
                "message" => "Resim boyutu en fazla 5 MB olabilir."
            ]);
            exit;
        }

        $imageInfo = getimagesize($tmpName);

        if ($imageInfo === false) {
            echo json_encode([
                "success" => false,
                "message" => "Gönderilen dosya geçerli bir resim değil."
            ]);
            exit;
        }

        $allowedMimeTypes = [
            "image/jpeg" => "jpg",
            "image/png"  => "png",
            "image/webp" => "webp"
        ];

        $mimeType = $imageInfo["mime"];

        if (!isset($allowedMimeTypes[$mimeType])) {
            echo json_encode([
                "success" => false,
                "message" => "Sadece JPG, PNG veya WEBP resimler yüklenebilir."
            ]);
            exit;
        }

        $extension = $allowedMimeTypes[$mimeType];

        /*
        |--------------------------------------------------------------------------
        | UPLOADS KLASÖRÜ
        |--------------------------------------------------------------------------
        */

        $uploadDir = __DIR__ . "/../uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        /*
        |--------------------------------------------------------------------------
        | BENZERSİZ DOSYA ADI
        |--------------------------------------------------------------------------
        */

        $newFileName =
            time() .
            "_" .
            bin2hex(random_bytes(6)) .
            "." .
            $extension;

        $destination =
            $uploadDir . $newFileName;

        /*
        |--------------------------------------------------------------------------
        | DOSYAYI TAŞI
        |--------------------------------------------------------------------------
        */

        if (!move_uploaded_file($tmpName, $destination)) {
            echo json_encode([
                "success" => false,
                "message" => "Resim uploads klasörüne kaydedilemedi."
            ]);
            exit;
        }

        $imagePath =
            "uploads/" . $newFileName;
    }

    /*
    |--------------------------------------------------------------------------
    | ÜRÜNÜ DATABASE'E EKLE
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        INSERT INTO products
        (
            title,
            price,
            user_id,
            description,
            image,
            category_id
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $title,
        $price,
        $user_id,
        $description,
        $imagePath,
        $category_id
    ]);

    /*
    |--------------------------------------------------------------------------
    | BAŞARILI CEVAP
    |--------------------------------------------------------------------------
    */

    echo json_encode([
        "success" => true,
        "message" => "Ürün başarıyla eklendi.",
        "product_id" => $conn->lastInsertId(),
        "image" => $imagePath,
        "category_id" => $category_id
    ]);

} catch (Throwable $error) {

    echo json_encode([
        "success" => false,
        "message" => "Ürün eklenirken bir hata oluştu."
    ]);
}