<?php
session_start();
require_once 'db_connect.php';

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['username']) 
    && isset($_POST['password'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT Id, Username, PasswordHash FROM [User] WHERE Username = ?";
    $params = [$username];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt && sqlsrv_has_rows($stmt)) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $hashedInput = strtolower(hash('sha256', $password));

        if ($hashedInput === strtolower($row['PasswordHash'])) {
            $_SESSION['user_id']  = $row['Id'];
            $_SESSION['username'] = $row['Username'];
            header("Location: product_list.php");
            exit();
        } else {
            // Sai mật khẩu → quay lại index.html với thông báo lỗi
            header("Location: index.html?error=wrongpass");
            exit();
        }
    } else {
        // Không tìm thấy user → quay lại index.html với thông báo lỗi
        header("Location: index.html?error=nouser");
        exit();
    }
} else {
    header("Location: index.html?error=empty");
    exit();
}

sqlsrv_close($conn);
?>

