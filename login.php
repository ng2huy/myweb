<?php
session_start();

// Nếu đã đăng nhập thì chuyển thẳng sang trang sản phẩm
if (isset($_SESSION['user_id'])) {
    header("Location: product_list.php");
    exit();
}

// Kết nối DB dùng file chung
require_once __DIR__ . '/../includes/db_connect.php'; 
// chỉnh lại đường dẫn cho phù hợp với cấu trúc thư mục của bạn

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && !empty($_POST['username']) 
    && !empty($_POST['password'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Lấy user từ DB
    $sql = "SELECT Id, Username, PasswordHash FROM [User] WHERE Username = ?";
    $params = [$username];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt && sqlsrv_has_rows($stmt)) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        // Hash mật khẩu nhập vào bằng SHA256 để so sánh
        $hashedInput = strtolower(hash('sha256', $password));

        if ($hashedInput === strtolower($row['PasswordHash'])) {
            // Đăng nhập thành công → set session
            $_SESSION['user_id']  = $row['Id'];
            $_SESSION['username'] = $row['Username'];

            // Redirect sang trang danh sách sản phẩm
            header("Location: product_list.php");
            exit();
        } else {
            $error = "Sai mật khẩu.";
        }
    } else {
        $error = "Không tìm thấy user.";
    }
}

sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
</head>
<body>
    <h2>Vui lòng nhập username và password.</h2>
    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <label>Username:</label>
        <input type="text" name="username" required><br><br>
        <label>Password:</label>
        <input type="password" name="password" required><br><br>
        <button type="submit">Đăng nhập</button>
    </form>
</body>
</html>

