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

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT UserID, Username, PasswordHash FROM [User] WHERE Username = ?";
    $params = [$username];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt && sqlsrv_has_rows($stmt)) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        $storedHash = strtolower(trim($row['PasswordHash']));
        $inputHash  = strtolower(hash('sha256', $password));

        // Cho phép login nếu mật khẩu thô khớp hoặc hash khớp
        $match = ($password === $storedHash) || ($inputHash === $storedHash);

        if ($match) {
            $_SESSION['user_id']  = $row['UserID'];
            $_SESSION['username'] = $row['Username'];

            // Redirect sang product_list.php
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

