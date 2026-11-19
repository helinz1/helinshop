<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../database/config.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli(
    DB_HOST,
    DB_USER,
    DB_PASSWORD,
    DB_NAME
);

$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Sadece POST isteği kabul edilir."
    ]);
    exit;
}


// ========================================
// GELEN BİLGİLER
// ========================================

$user_id = $_POST["user_id"] ?? null;

$customer_name = trim(
    $_POST["customer_name"] ?? ""
);

$customer_phone = trim(
    $_POST["customer_phone"] ?? ""
);

$customer_email = trim(
    $_POST["customer_email"] ?? ""
);

$city = trim(
    $_POST["city"] ?? ""
);

$district = trim(
    $_POST["district"] ?? ""
);

$neighborhood = trim(
    $_POST["neighborhood"] ?? ""
);

$address = trim(
    $_POST["address"] ?? ""
);

$postal_code = trim(
    $_POST["postal_code"] ?? ""
);

$payment_method = trim(
    $_POST["payment_method"] ?? ""
);


// ========================================
// KONTROLLER
// ========================================

if (!$user_id) {
    echo json_encode([
        "success" => false,
        "message" => "Kullanıcı bilgisi eksik."
    ]);
    exit;
}


if (
    $customer_name === "" ||
    $customer_phone === "" ||
    $customer_email === "" ||
    $city === "" ||
    $district === "" ||
    $neighborhood === "" ||
    $address === "" ||
    $payment_method === ""
) {

    echo json_encode([
        "success" => false,
        "message" => "Lütfen zorunlu alanların tamamını doldurun."
    ]);

    exit;
}


// ========================================
// EMAIL KONTROLÜ
// ========================================

if (
    !filter_var(
        $customer_email,
        FILTER_VALIDATE_EMAIL
    )
) {

    echo json_encode([
        "success" => false,
        "message" => "Geçerli bir e-posta adresi giriniz."
    ]);

    exit;
}


// ========================================
// ÖDEME YÖNTEMİ KONTROLÜ
// ========================================

$allowedPaymentMethods = [
    "cash",
    "card"
];

if (
    !in_array(
        $payment_method,
        $allowedPaymentMethods,
        true
    )
) {

    echo json_encode([
        "success" => false,
        "message" => "Geçersiz ödeme yöntemi."
    ]);

    exit;
}


try {

    // ========================================
    // TRANSACTION BAŞLAT
    // ========================================

    $conn->begin_transaction();


    // ========================================
    // SEPETİ GETİR
    // ========================================

    $cartStmt = $conn->prepare("
        SELECT
            cart.product_id,
            cart.quantity,
            products.title,
            products.price
        FROM cart

        INNER JOIN products
            ON cart.product_id = products.id

        WHERE cart.user_id = ?
    ");


    $cartStmt->bind_param(
        "i",
        $user_id
    );


    $cartStmt->execute();


    $result =
        $cartStmt->get_result();


    // ========================================
    // SEPET BOŞ MU?
    // ========================================

    if (
        $result->num_rows === 0
    ) {

        $conn->rollback();


        echo json_encode([
            "success" => false,
            "message" => "Sepetiniz boş."
        ]);


        exit;
    }


    // ========================================
    // SEPET ÜRÜNLERİ + TOPLAM
    // ========================================

    $items = [];

    $totalPrice = 0;


    while (
        $row = $result->fetch_assoc()
    ) {

        $quantity =
            (int)$row["quantity"];


        $price =
            (float)$row["price"];


        $totalPrice +=
            $price * $quantity;


        $items[] = [

            "product_id" =>
                (int)$row["product_id"],

            "title" =>
                $row["title"],

            "price" =>
                $price,

            "quantity" =>
                $quantity

        ];
    }


    // ========================================
    // SİPARİŞ OLUŞTUR
    // ========================================

    $status =
        "Hazırlanıyor";


    $orderStmt =
        $conn->prepare("
            INSERT INTO orders (
                user_id,
                total_price,
                status,

                customer_name,
                customer_phone,
                customer_email,

                city,
                district,
                neighborhood,
                address,
                postal_code,

                payment_method
            )

            VALUES (
                ?,
                ?,
                ?,

                ?,
                ?,
                ?,

                ?,
                ?,
                ?,
                ?,
                ?,

                ?
            )
        ");


    $orderStmt->bind_param(

        "idssssssssss",

        $user_id,
        $totalPrice,
        $status,

        $customer_name,
        $customer_phone,
        $customer_email,

        $city,
        $district,
        $neighborhood,
        $address,
        $postal_code,

        $payment_method

    );


    $orderStmt->execute();


    // ========================================
    // OLUŞAN SİPARİŞ ID
    // ========================================

    $order_id =
        $conn->insert_id;


    // ========================================
    // SİPARİŞ ÜRÜNLERİNİ KAYDET
    // ========================================

    $itemStmt =
        $conn->prepare("
            INSERT INTO order_items (
                order_id,
                product_id,
                title,
                price,
                quantity
            )

            VALUES (
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");


    foreach (
        $items as $item
    ) {

        $product_id =
            $item["product_id"];


        $title =
            $item["title"];


        $price =
            $item["price"];


        $quantity =
            $item["quantity"];


        $itemStmt->bind_param(

            "iisdi",

            $order_id,
            $product_id,
            $title,
            $price,
            $quantity

        );


        $itemStmt->execute();
    }


    // ========================================
    // SEPETİ TEMİZLE
    // ========================================

    $deleteStmt =
        $conn->prepare("
            DELETE FROM cart
            WHERE user_id = ?
        ");


    $deleteStmt->bind_param(
        "i",
        $user_id
    );


    $deleteStmt->execute();


    // ========================================
    // HER ŞEY BAŞARILI
    // ========================================

    $conn->commit();


    echo json_encode([

        "success" => true,

        "message" =>
            "Siparişiniz başarıyla oluşturuldu.",

        "order_id" =>
            $order_id,

        "total_price" =>
            $totalPrice

    ]);


} catch (Throwable $e) {

    // ========================================
    // HATA VARSA GERİ AL
    // ========================================

    $conn->rollback();


    echo json_encode([

        "success" => false,

        "message" =>
            "Sipariş oluşturulurken hata oluştu.",

        
    ]);

}


$conn->close();

?>