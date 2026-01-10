<?php
session_start();
require_once '/var/www/includes/db_connect.php';

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        header("Location: index.html?error=empty");
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

        // Hash mật khẩu nhập vào bằng SHA256
        $hashedInput = strtoupper(hash('sha256', $password));
        $storedHash  = strtoupper($row['PasswordHash']);

        if ($hashedInput === $storedHash) {
            $_SESSION['user_id']  = $row['UserID'];
            $_SESSION['username'] = $row['Username'];

            header("Location: product_list.php");
            exit();
        } else {
            header("Location: index.html?error=wrongpass");
            exit();
        }
    } else {
        header("Location: index.html?error=nouser");
        exit();
    }
} else {
    header("Location: index.html?error=empty");
    exit();
}

sqlsrv_close($conn);
?>

