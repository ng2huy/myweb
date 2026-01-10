<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    die("❌ Session không tồn tại. Bạn chưa đăng nhập.");
}

// Kết nối CSDL
require_once '/var/www/includes/db_connect.php';

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    die("❌ Lỗi kết nối: " . print_r(sqlsrv_errors(), true));
}

// Truy vấn sản phẩm
$sql = "SELECT ProductID, ProductName, Price, Description FROM Product";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("❌ Lỗi truy vấn: " . print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách sản phẩm</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .product { border-bottom: 1px solid #ccc; padding: 10px 0; }
        .product h2 { margin: 0; }
        .product p { margin: 5px 0; }
    </style>
</head>
<body>
    <h1>🛒 Danh sách sản phẩm</h1>

    <?php
    $count = 0;
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        echo "<div class='product'>";
        echo "<h2>{$row['ProductName']}</h2>";
        echo "<p><strong>Giá:</strong> " . number_format($row['Price'], 0, ',', '.') . " VND</p>";
        echo "<p>{$row['Description']}</p>";
        echo "</div>";
        $count++;
    }

    if ($count === 0) {
        echo "<p>Không có sản phẩm nào trong cơ sở dữ liệu.</p>";
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    ?>
</body>
</html>

