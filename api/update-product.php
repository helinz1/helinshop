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


/* DATABASE */

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

$conn->set_charset("utf8mb4");


/* POST VERİLERİ */

$product_id = trim($_POST["product_id"] ?? "");
$user_id = trim($_POST["user_id"] ?? "");
$title = trim($_POST["title"] ?? "");
$price = trim($_POST["price"] ?? "");
$description = trim($_POST["description"] ?? "");

/* YENİ: KATEGORİ */
$category_id = trim($_POST["category_id"] ?? "");

$remove_image =
    ($_POST["remove_image"] ?? "0") === "1";


/* KONTROL */

if (
    $product_id === "" ||
    $user_id === "" ||
    $title === "" ||
    $price === "" ||
    $category_id === ""
) {
    echo json_encode([
        "success" => false,
        "message" => "Ürün adı, fiyat, kategori ve gerekli alanları doldurun."
    ]);

    $conn->close();
    exit;
}


/* ID KONTROLLERİ */

if (
    !ctype_digit($product_id) ||
    !ctype_digit($user_id) ||
    !ctype_digit($category_id)
) {
    echo json_encode([
        "success" => false,
        "message" => "Geçersiz ürün, kullanıcı veya kategori ID."
    ]);

    $conn->close();
    exit;
}


/* KATEGORİ VAR MI? */

$categoryStmt = $conn->prepare("
    SELECT id
    FROM categories
    WHERE id = ?
    LIMIT 1
");

if (!$categoryStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Kategori kontrol sorgusu hazırlanamadı."
    ]);

    $conn->close();
    exit;
}

$categoryStmt->bind_param(
    "i",
    $category_id
);

$categoryStmt->execute();

$categoryResult =
    $categoryStmt->get_result();

$category =
    $categoryResult->fetch_assoc();

$categoryStmt->close();


if (!$category) {
    echo json_encode([
        "success" => false,
        "message" => "Seçilen kategori bulunamadı."
    ]);

    $conn->close();
    exit;
}


/* ÜRÜNÜ KONTROL ET */

$checkStmt = $conn->prepare("
    SELECT id, image
    FROM products
    WHERE id = ?
    AND user_id = ?
");

if (!$checkStmt) {
    echo json_encode([
        "success" => false,
        "message" => "Ürün kontrol sorgusu hazırlanamadı."
    ]);

    $conn->close();
    exit;
}

$checkStmt->bind_param(
    "ii",
    $product_id,
    $user_id
);

$checkStmt->execute();

$result =
    $checkStmt->get_result();

$product =
    $result->fetch_assoc();

$checkStmt->close();


if (!$product) {
    echo json_encode([
        "success" => false,
        "message" => "Ürün bulunamadı veya bu ürün sana ait değil."
    ]);

    $conn->close();
    exit;
}


/* ESKİ RESİM */

$oldImage =
    $product["image"] ?? null;


/* RESİM DEĞİŞKENLERİ */

$newImagePath =
    $oldImage;

$newImageUploaded =
    false;

$newImageFullPath =
    null;


/* YENİ RESİM GELDİ Mİ? */

if (
    isset($_FILES["image"]) &&
    $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
) {

    /* UPLOAD HATASI */

    if (
        $_FILES["image"]["error"] !== UPLOAD_ERR_OK
    ) {

        echo json_encode([
            "success" => false,
            "message" =>
                "Resim yükleme hatası. Kod: " .
                $_FILES["image"]["error"]
        ]);

        $conn->close();
        exit;
    }


    /* DOSYA BİLGİLERİ */

    $tmpName =
        $_FILES["image"]["tmp_name"];

    $fileSize =
        $_FILES["image"]["size"];


    /* BOYUT */

    if (
        $fileSize >
        5 * 1024 * 1024
    ) {

        echo json_encode([
            "success" => false,
            "message" => "Resim boyutu en fazla 5 MB olabilir."
        ]);

        $conn->close();
        exit;
    }


    /* RESİM KONTROLÜ */

    $imageInfo =
        @getimagesize($tmpName);

    if ($imageInfo === false) {

        echo json_encode([
            "success" => false,
            "message" => "Geçerli bir resim dosyası gönderilmedi."
        ]);

        $conn->close();
        exit;
    }


    /* MIME */

    $allowedMimeTypes = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    $mimeType =
        $imageInfo["mime"];


    if (
        !isset(
            $allowedMimeTypes[$mimeType]
        )
    ) {

        echo json_encode([
            "success" => false,
            "message" => "Sadece JPG, PNG veya WEBP resimler yüklenebilir."
        ]);

        $conn->close();
        exit;
    }


    $extension =
        $allowedMimeTypes[$mimeType];


    /* UPLOADS */

    $uploadDir =
        __DIR__ . "/../uploads/";


    if (!is_dir($uploadDir)) {

        if (
            !mkdir(
                $uploadDir,
                0777,
                true
            )
        ) {

            echo json_encode([
                "success" => false,
                "message" => "Uploads klasörü oluşturulamadı."
            ]);

            $conn->close();
            exit;
        }
    }


    /* DOSYA ADI */

    try {

        $randomName =
            bin2hex(
                random_bytes(8)
            );

    } catch (Exception $e) {

        $randomName =
            uniqid();
    }


    $newFileName =
        time() .
        "_" .
        $randomName .
        "." .
        $extension;


    $newImageFullPath =
        $uploadDir .
        $newFileName;


    /* DOSYAYI KAYDET */

    if (
        !move_uploaded_file(
            $tmpName,
            $newImageFullPath
        )
    ) {

        echo json_encode([
            "success" => false,
            "message" => "Yeni resim uploads klasörüne kaydedilemedi."
        ]);

        $conn->close();
        exit;
    }


    /* DATABASE PATH */

    $newImagePath =
        "uploads/" .
        $newFileName;

    $newImageUploaded =
        true;
}


/* RESMİ KALDIR */

if (
    $remove_image &&
    !$newImageUploaded
) {

    $newImagePath =
        null;
}


/* DATABASE UPDATE */

$stmt = $conn->prepare("
    UPDATE products
    SET
        title = ?,
        price = ?,
        description = ?,
        image = ?,
        category_id = ?
    WHERE id = ?
    AND user_id = ?
");


if (!$stmt) {

    if (
        $newImageUploaded &&
        $newImageFullPath &&
        file_exists($newImageFullPath)
    ) {

        @unlink(
            $newImageFullPath
        );
    }

    echo json_encode([
        "success" => false,
        "message" => "Güncelleme sorgusu hazırlanamadı."
    ]);

    $conn->close();
    exit;
}


/* PARAMETRELER */

$stmt->bind_param(
    "ssssiii",
    $title,
    $price,
    $description,
    $newImagePath,
    $category_id,
    $product_id,
    $user_id
);


/* UPDATE */

if ($stmt->execute()) {

    /* ESKİ RESMİ SİL */

    if (
        ($newImageUploaded || $remove_image) &&
        !empty($oldImage)
    ) {

        $oldImageFile =
            __DIR__ .
            "/../" .
            ltrim(
                $oldImage,
                "/"
            );


        if (
            file_exists($oldImageFile) &&
            is_file($oldImageFile)
        ) {

            @unlink(
                $oldImageFile
            );
        }
    }


    /* RESİM URL */

    $imageUrl =
        null;


    if (!empty($newImagePath)) {

        $imageUrl =
            APP_URL . "/" .
            ltrim(
                $newImagePath,
                "/"
            );
    }


    echo json_encode([
        "success" => true,
        "message" => "Ürün başarıyla güncellendi.",
        "image" => $newImagePath,
        "image_url" => $imageUrl,
        "category_id" => (int)$category_id
    ]);

} else {

    /* DATABASE BAŞARISIZSA YENİ RESMİ SİL */

    if (
        $newImageUploaded &&
        $newImageFullPath &&
        file_exists($newImageFullPath)
    ) {

        @unlink(
            $newImageFullPath
        );
    }


    echo json_encode([
        "success" => false,
        "message" => "Ürün güncellenemedi."
    ]);
}


$stmt->close();

$conn->close();

?>