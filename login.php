<?php
session_start();
require_once '/var/www/includes/db_connect.php'; // đường dẫn bạn đã chỉnh

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['username']) 
    && isset($_POST['password'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Lấy user từ DB
    $sql = "SELECT UserID, Username, PasswordHash FROM [User] WHERE Username = ?";
    $params = [$username];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die("Query failed: " . print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmt)) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        $storedHash = trim($row['PasswordHash']);
        $inputHash  = strtolower(hash('sha256', $password));

        // Cho phép login nếu mật khẩu thô khớp hoặc hash khớp
        $match = ($password === $storedHash) || ($inputHash === strtolower($storedHash));

        if ($match) {
            $_SESSION['user_id']  = $row['UserID'];
            $_SESSION['username'] = $row['Username'];

            // Thành công → chuyển sang product_list.php
            header("Location: product_list.php");
            exit();
        } else {
            // Sai mật khẩu
            header("Location: index.html?error=wrongpass");
            exit();
        }
    } else {
        // Không tìm thấy user
        header("Location: index.html?error=nouser");
        exit();
    }
} else {
    // Không nhập username hoặc password
    header("Location: index.html?error=empty");
    exit();
}

sqlsrv_close($conn);
?>

