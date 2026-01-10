<?php
session_start();

// Nếu chưa đăng nhập thì chuyển hướng về trang index.html
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.html");
    exit();
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
                echo "<td>{$row['ProductName']}</td>";
                echo "<td>" . number_format($row['Price'], 0, ',', '.') . " VND</td>";
                echo "<td>{$row['Description']}</td>";
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

