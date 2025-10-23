<?php
require_once 'header.php'; // 1. เรียก Header
require_once 'connectdb.php'; // 2. เชื่อมต่อ DB

// 3. ตรวจสอบสิทธิ์ (เหมือนเดิม)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
if (!isset($_GET['id'])) {
    header("Location: dashboard.php#orders");
    exit;
}
$order_id = $_GET['id'];

// 5. อัปเดตสถานะ (เหมือนเดิม)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    // ... (โค้ดอัปเดตสถานะ เหมือนเดิม) ...
    $new_status = $_POST['order_status'];
    $sql_update = "UPDATE orders SET order_status = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $new_status, $order_id);
    if ($stmt_update->execute()) {
        $_SESSION['message'] = "อัปเดตสถานะออเดอร์ #$order_id เป็น $new_status สำเร็จ!";
        $_SESSION['message_type'] = "alert-success";
    } else {
        $_SESSION['message'] = "เกิดข้อผิดพลาดในการอัปเดต";
        $_SESSION['message_type'] = "alert-error";
    }
    $stmt_update->close();
    header("Location: admin_order_detail.php?id=" . $order_id); 
    exit;
}

// 6. ดึงข้อมูลออเดอร์ (เหมือนเดิม)
$sql_order = "SELECT * FROM orders WHERE id = ?";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param("i", $order_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();
if ($result_order->num_rows == 0) {
    echo "<main class='container'><p>ไม่พบออเดอร์นี้</p></main>";
    require_once 'footer.php';
    exit;
}
$order = $result_order->fetch_assoc();
$stmt_order->close();

// 8. ดึงข้อมูลสินค้า (เหมือนเดิม)
$sql_items = "SELECT oi.*, p.name, p.image_url 
              FROM order_items oi
              JOIN products p ON oi.product_id = p.id
              WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
?>

<link rel="stylesheet" href="admin_style.css">
<style>
    .order-info .summary-line {
        display: flex;
        justify-content: space-between;
        margin: 5px 0;
    }
    .order-info .summary-line.discount {
        color: #e74c3c; /* สีแดงสำหรับส่วนลด */
    }
    .order-info .summary-line.total {
        font-size: 1.2em;
        font-weight: bold;
        margin-top: 10px;
        color: #333;
    }
</style>

<main class="admin-container"> 
    <a href="dashboard.php#orders" style="text-decoration: none; margin-bottom: 15px; display: inline-block; color: #007bff; font-weight: bold;">&larr; กลับไปหน้า Dashboard</a>
    <h1>รายละเอียดออเดอร์</h1>

    <?php
    // (Flash Message - เหมือนเดิม)
    if (isset($_SESSION['message'])):
    ?>
        <div class="alert <?php echo $_SESSION['message_type']; ?>">
            <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
        </div>
    <?php
    endif;
    ?>

    <div class="order-detail-layout">
        
        <div class="admin-card order-info">
            <h3>ข้อมูลลูกค้าและการจัดส่ง</h3>
            <p><b>ชื่อผู้รับ:</b> <?php echo htmlspecialchars($order['customer_name']); ?></p>
            <p><b>เบอร์โทร:</b> <?php echo htmlspecialchars($order['phone_number']); ?></p>
            <p><b>ที่อยู่:</b><br><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            <hr>
            
            <div class="status-update-form">
                <h3>อัปเดตสถานะออเดอร์</h3>
                <form action="admin_order_detail.php?id=<?php echo $order_id; ?>" method="POST">
                    <select name="order_status">
                        <option value="pending" <?php if($order['order_status'] == 'pending') echo 'selected'; ?>>Pending</option>
                        <option value="processing" <?php if($order['order_status'] == 'processing') echo 'selected'; ?>>Processing</option>
                        <option value="shipped" <?php if($order['order_status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                        <option value="completed" <?php if($order['order_status'] == 'completed') echo 'selected'; ?>>Completed</option>
                        <option value="cancelled" <?php if($order['order_status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status">อัปเดต</button>
                </form>
            </div>
        </div>

        <div class="admin-card order-items-list">
            <h3>รายการสินค้าในออเดอร์ (<?php echo $result_items->num_rows; ?> รายการ)</h3>
            
            <?php 
                // 🟢 (แก้ไข 1/3) สร้างตัวแปรสำหรับเก็บยอดรวม
                $calculated_subtotal = 0; 
            ?>
            
            <?php while ($item = $result_items->fetch_assoc()): ?>
            <?php
                // 🟢 (แก้ไข 2/3) คำนวณยอดรวมของไอเทมนี้ และบวกเข้า Subtotal
                $item_total = $item['price'] * $item['quantity'];
                $calculated_subtotal += $item_total;
            ?>
            <div class="order-item"> 
                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="">
                <div class="order-item-info">
                    <p class="item-name"><?php echo htmlspecialchars($item['name']); ?></p>
                    <p class="item-meta">Size: <?php echo htmlspecialchars($item['size']); ?></p>
                    <p class="item-meta">Qty: <?php echo $item['quantity']; ?></p>
                    <p class="item-meta">ราคา (ณ ตอนซื้อ): <?php echo number_format($item['price'], 2); ?> ฿</p>
                </div>
                <span class="item-total"><?php echo number_format($item_total, 2); ?> ฿</span>
            </div>
            <?php endwhile; ?>
            
            <?php
                // 🟢 (แก้ไข 3/3) คำนวณส่วนลด
                $calculated_discount = $calculated_subtotal - $order['total_price'];
            ?>
            
            <hr>
            <div class="order-summary">
                <div class="summary-line">
                    <span>ราคาก่อนลด (Subtotal):</span>
                    <span><?php echo number_format($calculated_subtotal, 2); ?> ฿</span>
                </div>
                <div class="summary-line discount">
                    <span>ส่วนลด (Discount):</span>
                    <span>- <?php echo number_format($calculated_discount, 2); ?> ฿</span>
                </div>
                <div class="summary-line total">
                    <span>ยอดสุทธิ (Grand Total):</span>
                    <span><?php echo number_format($order['total_price'], 2); ?> ฿</span>
                </div>
            </div>
            
        </div>
    </div>
</main>

<?php
$stmt_items->close();
$conn->close();
require_once 'footer.php';
?>