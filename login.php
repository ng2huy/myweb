<?php
session_start();


require_once '/var/www/includes/db_connect.php';

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

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
            $_SESSION['user_id']  = $row['UserID']; // dùng đúng cột trong DB
            $_SESSION['username'] = $row['Username'];
	// Debug: in ra session ID và biến session 
	   

            // Đăng nhập thành công → chuyển sang product_list.php
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

