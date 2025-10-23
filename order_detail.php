<?php
require_once 'header.php'; // 1. เรียก Header
require_once 'connectdb.php'; // 2. เชื่อมต่อ DB

// 3. ตรวจสอบ: ต้อง Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: order_history.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'];

// 4. (เหมือนเดิม) ดึงออเดอร์ (เช็ก ID และ user_id)
$sql_order = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param("ii", $order_id, $user_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();

if ($result_order->num_rows == 0) {
    echo "<main class='container'><p>ไม่พบออเดอร์นี้</p></main>";
    require_once 'footer.php';
    exit;
}
$order = $result_order->fetch_assoc();
$stmt_order->close();

// 6. (เหมือนเดิม) ดึง "สินค้า" ในออเดอร์
$sql_items = "SELECT oi.*, p.name, p.image_url 
              FROM order_items oi
              JOIN products p ON oi.product_id = p.id
              WHERE oi.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
?>

<style>
    .order-detail-container { display: flex; flex-wrap: wrap; gap: 30px; margin-top: 30px; }
    .order-info, .order-items-list { flex: 1; min-width: 320px; }
    .order-info { background: #f9f9f9; padding: 25px; border: 1px solid #ddd; border-radius: 8px; }
    .order-info h3 { margin-top: 0; }
    .order-info p { margin: 5px 0; }
    .order-items-list h3 { margin-top: 0; }
    .summary-item { display: flex; gap: 15px; margin-bottom: 15px; font-size: 14px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    .summary-item img { width: 60px; height: 60px; object-fit: cover; }
    .summary-item-info { flex: 1; }
    .summary-item-info p { margin: 0; }
    
    .order-info .summary-line {
        display: flex;
        justify-content: space-between;
        margin: 5px 0;
        font-size: 16px; /* (ปรับขนาด) */
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

<main class="container">
    <h2>รายละเอียดออเดอร์ #<?php echo $order_id; ?></h2>
    <p>สถานะ: <b><?php echo ucfirst($order['order_status']); ?></b></p>
    
    <div class="order-detail-container">
        
        <div class="order-info">
            <h3>ข้อมูลการจัดส่ง</h3>
            <p><b>ชื่อผู้รับ:</b> <?php echo htmlspecialchars($order['customer_name']); ?></p>
            <p><b>เบอร์โทร:</b> <?php echo htmlspecialchars($order['phone_number']); ?></p>
            <p><b>ที่อยู่:</b><br><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            <hr>
            <h3>สรุปยอด</h3>
            
            <?php
            // สร้างตัวแปรสำหรับเก็บยอดรวม
            // (เราต้องวนลูปอ่าน $result_items อีกครั้ง หรือเก็บค่าไว้)
            // (เราจะใช้วิธี query ใหม่เพื่อความง่าย ไม่ต้องแก้โค้ดเยอะ)
            $stmt_items->execute(); // รัน query ซ้ำ
            $result_items_for_total = $stmt_items->get_result();
            
            $calculated_subtotal = 0;
            while ($item_total = $result_items_for_total->fetch_assoc()) {
                $calculated_subtotal += $item_total['price'] * $item_total['quantity'];
            }
            
            // คำนวณส่วนลด
            $calculated_discount = $calculated_subtotal - $order['total_price'];
            ?>
            
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

        <div class="order-items-list">
            <h3>รายการสินค้าในออเดอร์</h3>
            <?php while ($item = $result_items->fetch_assoc()): ?>
            <div class="summary-item">
                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="">
                <div class="summary-item-info">
                    <p><b><?php echo htmlspecialchars($item['name']); ?></b></p>
                    <p>Size: <?php echo htmlspecialchars($item['size']); ?></p>
                    <p>Qty: <?php echo $item['quantity']; ?></p>
                    <p>ราคา (ณ ตอนซื้อ): <?php echo number_format($item['price'], 2); ?> ฿</p>
                </div>
                <span><?php echo number_format($item['price'] * $item['quantity'], 2); ?> ฿</span>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</main>

<?php
$stmt_items->close();
$conn->close();
require_once 'footer.php';
?>