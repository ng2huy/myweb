<?php
session_start();
require_once '/var/www/includes/db_connect.php';

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Chỉ xử lý khi form gửi bằng POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Kiểm tra input rỗng
    if ($username === '' || $password === '') {
        header("Location: index.php?error=empty");
        exit();
    }

    // Truy vấn lấy thông tin người dùng
    $sql = "SELECT UserID, Username, PasswordHash FROM [User] WHERE Username = ?";
    $params = [$username];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die("Query failed: " . print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmt)) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        // Hash mật khẩu nhập vào bằng SHA256 (chuẩn hóa chữ hoa)
        $hashedInput = strtoupper(hash('sha256', $password));
        $storedHash  = strtoupper($row['PasswordHash']);

        if ($hashedInput === $storedHash) {
            $_SESSION['user_id']  = $row['UserID'];
            $_SESSION['username'] = $row['Username'];

            header("Location: product_list.php");
            exit();
        } else {
            header("Location: index.php?error=wrongpass");
            exit();
        }
    } else {
        header("Location: index.php?error=nouser");
        exit();
    }
} else {
    // Nếu không phải POST thì quay về trang login
    header("Location: index.php?error=empty");
    exit();
}

sqlsrv_close($conn);
?>

