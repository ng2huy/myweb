<?php
session_cache_limiter('');
session_start();

// Xóa các header cấm cache do PHP tự động gửi header_remove("Set-Cookie"); 
header_remove("Cache-Control"); 
header_remove("Pragma"); 
header_remove("Expires");
header_remove("Set-Cookie"); // thêm dòng này
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '/var/www/includes/db_connect.php'; // dùng kết nối chung

$sql = "SELECT ProductID, ProductName, Price, Description FROM Product";
$stmt = sqlsrv_query($conn, $sql);
?>

<!-- HTML hiển thị sản phẩm -->
<h2>Danh sách sản phẩm</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Tên</th>
        <th>Giá</th>
        <th>Mô tả</th>
    </tr>
    <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
    <tr>
        <td><?= $row['ProductID'] ?></td>
        <td><?= htmlspecialchars($row['ProductName']) ?></td>
        <td><?= number_format($row['Price'], 0, ',', '.') ?> VND</td>
        <td><?= htmlspecialchars($row['Description']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<?php sqlsrv_close($conn); ?>

