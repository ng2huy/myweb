<?php
session_start();
require_once '/var/www/includes/db_connect.php';

// Kết nối SQL Server
$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Chỉ xử lý khi form gửi bằng POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form, nếu không có thì gán rỗng
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Kiểm tra input rỗng
    if ($username === '' || $password === '') {
        header("Location: index.html?error=empty");
        exit();
    }

    // Truy vấn lấy thông tin user
    $sql = "SELECT UserID, Username, PasswordHash FROM [User] WHERE Username = ?";
    $params = [$username];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die("Query failed: " . print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_has_rows($stmt)) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        // Hash mật khẩu nhập vào bằng SHA256
        $hashedInput = strtolower(hash('sha256', $password));
        $storedHash  = strtolower($row['PasswordHash']);

        if ($hashedInput === $storedHash) {
            // Đăng nhập thành công
            $_SESSION['user_id']  = $row['UserID'];
            $_SESSION['username'] = $row['Username'];

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
    // Nếu không phải POST thì báo lỗi
    header("Location: index.html?error=empty");
    exit();
}

sqlsrv_close($conn);
?>

