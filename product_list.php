<?php
session_start();

// Bật hiển thị lỗi khi debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ghi log lỗi vào file riêng
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/logs/php_errors.log');

// Kiểm tra extension SQL Server (sqlsrv)
if (!extension_loaded('sqlsrv')) {
    error_log("❌ Extension sqlsrv chưa được load.");
    http_response_code(500);
    exit("Extension sqlsrv chưa được cài hoặc kích hoạt.");
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    error_log("❌ Truy cập trực tiếp vào product_list.php mà không có cookie/session. IP=" . $_SERVER['REMOTE_ADDR']);
    header("Location: /index.html");
    exit();
} else {
    $username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'unknown';
    $userId   = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $ip       = $_SERVER['REMOTE_ADDR'];
    error_log("✅ User ID=$userId Username=$username IP=$ip truy cp product bằng cookie/session hợp lệ \n.");
}

// Cho phép cache phía reverse proxy
header("Cache-Control: public, max-age=600");

// Kết nối CSDL
require_once '/var/www/includes/db_connect.php';
$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    error_log("❌ Lỗi kết nối SQL Server: " . print_r(sqlsrv_errors(), true));
    http_response_code(500);
    exit("Không kết nối được CSDL.");
}

// Truy vấn sản phẩm
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
        .user-info {
            position: fixed;
            top: 10px;
            right: 20px;
            background: #f2f2f2;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            color: #333;
            z-index: 9999;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 60px; }
        th, td { border: 1px solid #ccc; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        caption { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
    </style>
</head>
<body>
    <!-- Góc hiển thị thông tin user -->
    <div class="user-info">
        👤 User: <?php echo $username; ?> (ID: <?php echo $userId; ?>)
        | <a href="logout.php">Đăng xuất</a>
    </div>

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

