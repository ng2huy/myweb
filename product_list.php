<?php
session_start();

// ⚠️ Bật hiển thị lỗi (chỉ dùng khi debug, không nên để ở môi trường production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 📁 Ghi log lỗi vào file riêng
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/logs/php_errors.log');

// 🔍 Kiểm tra extension SQL Server
if (!extension_loaded('pdo_sqlsrv')) {
    error_log("❌ Extension pdo_sqlsrv chưa được load.");
    http_response_code(500);
    exit("Extension pdo_sqlsrv chưa được cài hoặc kích hoạt.");
}

// 🔒 Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    // ❌ Không có session → user truy cập trực tiếp
    error_log("❌ Truy cập trực tiếp vào product_list.php mà không có cookie/session.");
    header("Location: /index.html");
    exit();
} else {
    // ✅ Có session → user dùng cookie hợp lệ
    error_log("✅ User ID " . $_SESSION['user_id'] . " truy cập bằng cookie/session hợp lệ.");
}

// 📦 Cho phép cache phía reverse proxy
header("Cache-Control: public, max-age=600");

