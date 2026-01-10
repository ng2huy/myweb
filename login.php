<?php
echo '<pre>'; print_r($_POST); 
echo $_SERVER['REQUEST_METHOD']; 
exit();
session_start();
require_once '/var/www/includes/db_connect.php';

// Kết nối SQL Server
$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    die("Kết nối thất bại: " . print_r(sqlsrv_errors(), true));
}

// Chỉ xử lý khi form gửi bằng POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Nếu truy cập trực tiếp login.php → không xử lý
    http_response_code(405);
    echo "Phương thức không hợp lệ.";
    exit();
}

// Lấy dữ liệu từ form
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// Kiểm tra input rỗng
if ($username === '' || $password === '') {
    header("Location: index.html?error=empty");
    exit();
}

// Truy vấn lấy thông tin người dùng
$sql = "SELECT UserID, Username, PasswordHash FROM [User] WHERE Username = ?";
$params = [$username];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Lỗi truy vấn: " . print_r(sqlsrv_errors(), true));
}

// Kiểm tra kết quả
if (sqlsrv_has_rows($stmt)) {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    // Hash mật khẩu nhập vào bằng SHA256
    $hashedInput = strtoupper(hash('sha256', $password));
    $storedHash  = strtoupper($row['PasswordHash']);

    // So sánh hash
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

// Đóng kết nối
sqlsrv_close($conn);
?>

