<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

echo '<pre>';
print_r($_SESSION);

if (!isset($_SESSION['user_id'])) {
    die("Session không tồn tại. Bạn chưa đăng nhập.");
}

require_once '/var/www/includes/db_connect.php';

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    die("Lỗi kết nối: " . print_r(sqlsrv_errors(), true));
}

$sql = "SELECT ProductID, ProductName, Price, Description FROM Product";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Lỗi truy vấn: " . print_r(sqlsrv_errors(), true));
}

