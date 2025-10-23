<?php
session_start();
require_once 'connectdb.php';

// 1. ตรวจสอบ: ต้อง Login และต้องส่งแบบ POST
if (!isset($_SESSION['user_id']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.php");
    exit;
}

// 2. ตรวจสอบ: ตะกร้าต้องไม่ว่าง
if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

// 3. รับข้อมูลจากฟอร์ม (รับตัวแปรแบบ Text)
$user_id = $_SESSION['user_id'];
$customer_name = $_POST['customer_name'];
$phone_number = $_POST['phone_number'];
$payment_method = $_POST['payment_method'];

// 4. "รวม" ที่อยู่ทั้งหมดเป็น 1 String
$address = $_POST['address'];
$sub_district = $_POST['sub_district']; // <-- รับ Text
$district = $_POST['district'];     // <-- รับ Text
$province = $_POST['province'];     // <-- รับ Text
$zip_code = $_POST['zip_code'];       // <-- รับ Text

$full_address = "$address\n";
$full_address .= "ต. $sub_district, อ. $district\n";
$full_address .= "จ. $province $zip_code";

// 5. (เหมือนเดิม) คำนวณราคาสุทธิ "ใหม่" ที่ฝั่ง Server
$subtotal = 0;
// ... (โค้ดดึง $subtotal จาก DB เหมือนเดิม) ...
$product_ids = [];
foreach ($_SESSION['cart'] as $item) { $product_ids[] = $item['product_id']; }
$product_ids = array_unique($product_ids);
if (count($product_ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));
    $sql_price = "SELECT id, price FROM products WHERE id IN ($placeholders)";
    $stmt_price = $conn->prepare($sql_price);
    $stmt_price->bind_param($types, ...$product_ids);
    $stmt_price->execute();
    $result_price = $stmt_price->get_result();
    $products_data = [];
    while ($product = $result_price->fetch_assoc()) { $products_data[$product['id']] = $product['price']; }
    $stmt_price->close();
    foreach ($_SESSION['cart'] as $item) {
        if (isset($products_data[$item['product_id']])) {
            $subtotal += $products_data[$item['product_id']] * $item['quantity'];
        }
    }
}
// ... (โค้ดคำนวณส่วนลด เหมือนเดิม) ...
$discount_percent = 0;
if ($subtotal >= 8000) { $discount_percent = 20; }
elseif ($subtotal >= 6000) { $discount_percent = 15; }
elseif ($subtotal >= 4000) { $discount_percent = 10; }
$discount_amount = ($subtotal * $discount_percent) / 100;
$grand_total = $subtotal - $discount_amount;
// จบการคำนวณ

// 6. (เหมือนเดิม) เริ่ม Transaction
$conn->begin_transaction();

try {
    // 7. (กลับเป็นแบบเดิม) บันทึกลงตาราง 'orders'
    $sql_order = "INSERT INTO orders (user_id, total_price, payment_method, customer_name, shipping_address, phone_number) 
                  VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("idssss", 
        $user_id, 
        $grand_total, 
        $payment_method, 
        $customer_name, 
        $full_address,  // <-- ใช้ที่อยู่ที่รวมแล้ว
        $phone_number
    );
    $stmt_order->execute();
    
    $order_id = $conn->insert_id;

    // 8. บันทึก 'order_items'
    $sql_items = "INSERT INTO order_items (order_id, product_id, quantity, price, size) VALUES (?, ?, ?, ?, ?)";
    $stmt_items = $conn->prepare($sql_items);
    foreach ($_SESSION['cart'] as $item) {
        if (isset($products_data[$item['product_id']])) {
            $product_price = $products_data[$item['product_id']];
            $stmt_items->bind_param("iiids", $order_id, $item['product_id'], $item['quantity'], $product_price, $item['size']);
            $stmt_items->execute();
        }
    }

    // 9. Commit, ล้างตะกร้า, ส่งไปหน้า history
    $conn->commit();
    unset($_SESSION['cart']);
    $_SESSION['message'] = "สั่งซื้อสำเร็จ! (Order ID: $order_id)";
    $_SESSION['message_type'] = "alert-success";
    header("Location: order_history.php");
    exit;

} catch (Exception $e) {
    // 10. Rollback
    $conn->rollback();
    $_SESSION['message'] = "เกิดข้อผิดพลาดในการสั่งซื้อ: " . $e->getMessage();
    $_SESSION['message_type'] = "alert-error";
    header("Location: checkout.php");
    exit;
}
?>