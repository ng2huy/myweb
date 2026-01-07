<?php
session_start();
require_once '/var/www/includes/db_connect.php'; // dùng kết nối chung

if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['username']) 
    && isset($_POST['password'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Lấy user từ DB
    $sql = "SELECT * FROM [User] WHERE Username = ?";
    $params = [$username];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die("Query failed: " . print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmt)) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        // Hash mật khẩu nhập vào bằng SHA256 để so sánh
        $hashedInput = hash('sha256', $password);

        if ($hashedInput === strtolower($row['PasswordHash'])) {
            echo "Login successful! Welcome " . htmlspecialchars($row['Username']);
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found.";
    }
} else {
    echo "Vui lòng nhập username và password.";
}

sqlsrv_close($conn);
?>

