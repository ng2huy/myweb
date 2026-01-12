<?php
session_start();

// ⚠️ Bật hiển thị lỗi (chỉ dùng khi debug, không nên để ở môi trường production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 📁 Ghi log lỗi vào file riêng
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/logs/php_errors.log');

// 🔍 Kiểm tra extension SQL Server
if (!extension_loaded('pdo_sqlsrv')) {
    error_log("❌ Extension pdo_sqlsrv chưa được load.");
    http_response_code(500);
    exit("Extension pdo_sqlsrv chưa được cài hoặc kích hoạt.");
}

// 🔒 Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    error_log("🔒 Người dùng chưa đăng nhập.");
    header("Location: /index.html");
    exit();
}

// 📦 Cho phép cache phía reverse proxy
header("Cache-Control: public, max-age=600");

// 🔌 Kết nối CSDL
require_once '/var/www/includes/db_connect.php';

// Ghi log thông tin kết nối để kiểm tra
error_log("🔧 serverName: " . print_r($serverName, true));
error_log("🔧 connectionOptions: " . print_r($connectionOptions, true));

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    error_log("❌ Lỗi kết nối SQL Server: " . print_r(sqlsrv_errors(), true));
    http_response_code(500);
    exit("Không kết nối được CSDL.");
}

// 📄 Truy vấn sản phẩm
$sql = "SELECT ProductID, ProductName, Price, Description FROM Product";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    error_log("❌ Lỗi truy vấn SQL: " . print_r(sqlsrv_errors(), true));
    http_response_code(500);
    exit("Lỗi truy vấn CSDL.");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách sản phẩm</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        caption { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
    </style>
</head>
<body>
    <table>
        <caption>🛒 Danh sách sản phẩm</caption>
        <thead>
            <tr>
                <th>Tên sản phẩm</th>
                <th>Giá</th>
                <th>Mô tả</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $count = 0;
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['ProductName']) . "</td>";
                echo "<td>" . number_format($row['Price'], 0, ',', '.') . " VND</td>";
                echo "<td>" . htmlspecialchars($row['Description']) . "</td>";
                echo "</tr>";
                $count++;
            }

            if ($count === 0) {
                echo "<tr><td colspan='3'>Không có sản phẩm nào trong cơ sở dữ liệu.</td></tr>";
            }

            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);
            ?>
        </tbody>
    </table>
</body>
</html>

