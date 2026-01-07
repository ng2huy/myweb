<?php
session_start();

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['username']) 
    && isset($_POST['password'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT UserID, Username, PasswordHash FROM [User] WHERE Username = ?";
    $params = [$username];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt && sqlsrv_has_rows($stmt)) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        $hashedInput = strtolower(hash('sha256', $password));
        $storedHash  = strtolower(trim($row['PasswordHash']));

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

