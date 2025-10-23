<?php
require_once 'header.php'; // 1. เรียก Header
require_once 'connectdb.php'; // 2. เชื่อมต่อ DB

// 3. ตรวจสอบ: ต้อง Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 4. ดึงออเดอร์ "เฉพาะ" ของ User คนนี้
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<style>
    .history-table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
    }
    .history-table th, .history-table td {
        border: 1px solid #ddd;
        padding: 10px 12px;
        text-align: left;
    }
    .history-table th { background-color: #f2f2f2; }
    .status-pending { color: #f39c12; font-weight: bold; }
    .status-completed { color: #28a745; font-weight: bold; }
    .btn-view-order {
        background-color: #3498db;
        color: white;
        padding: 5px 10px;
        text-decoration: none;
        border-radius: 4px;
        font-size: 14px;
    }
</style>

<main class="container">
    <h2>ประวัติการสั่งซื้อ</h2>

    <?php
    // 6. (Bonus) แสดง Flash Message ถ้าเพิ่งสั่งเสร็จ
    if (isset($_SESSION['message'])):
    ?>
        <div class="alert <?php echo $_SESSION['message_type']; ?>" style="margin-top: 15px;">
            <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
        </div>
    <?php
    endif;
    ?>

    <table class="history-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>วันที่สั่งซื้อ</th>
                <th>ยอดรวม</th>
                <th>สถานะ</th>
                <th>รายละเอียด</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                        <td><?php echo number_format($order['total_price'], 2); ?> ฿</td>
                        <td>
                            <span class="status-<?php echo $order['order_status']; ?>">
                                <?php echo ucfirst($order['order_status']); // Pending, Completed ?>
                            </span>
                        </td>
                        <td>
                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn-view-order">ดูรายละเอียด</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">คุณยังไม่มีประวัติการสั่งซื้อ</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<?php
$stmt->close();
$conn->close();
require_once 'footer.php';
?>