<?php
session_start();
$serverName = "192.168.255.200";
$connectionOptions = [
    "Database" => "MyAppDB", // đúng tên database
    "Uid" => "sa",
    "PWD" => "n0kk@N73",
    "Encrypt" => true,
    "TrustServerCertificate" => true
];

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

