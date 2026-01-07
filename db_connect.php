<?php
// Kết nối SQL Server dùng chung
$serverName = "192.168.255.200";
$connectionOptions = [
    "Database" => "MyAppDB",
    "Uid" => "sa",
    "PWD" => "n0kk@N73",
    "Encrypt" => true,
    "TrustServerCertificate" => true
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    die("Kết nối SQL Server thất bại: " . print_r(sqlsrv_errors(), true));
}
?>

