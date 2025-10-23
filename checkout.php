<?php
require_once 'header.php'; // 1. เรียก Header
require_once 'connectdb.php'; // 2. เชื่อมต่อ DB

// 3. ตรวจสอบ: ถ้ายังไม่ Login, ส่งไปหน้า Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 4. ตรวจสอบ: ถ้าตะกร้าว่าง, ส่งกลับไปหน้าแรก
if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

// 5. (เหมือนเดิม) ฟังก์ชันดึงข้อมูลสินค้าและคำนวณราคาทั้งหมด (Subtotal)
function get_cart_details($conn, $cart) {
    // ... (โค้ดฟังก์ชัน get_cart_details เหมือนเดิมทุกประการ) ...
    $cart_details = ['items' => [], 'subtotal' => 0];
    $product_ids = [];
    foreach ($cart as $item) { $product_ids[] = $item['product_id']; }
    $product_ids = array_unique($product_ids);
    if (count($product_ids) == 0) return $cart_details;
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));
    $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$product_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $products_data = [];
    while ($product = $result->fetch_assoc()) { $products_data[$product['id']] = $product; }
    $stmt->close();
    foreach ($cart as $cart_key => $item) {
        if (!isset($products_data[$item['product_id']])) continue;
        $product = $products_data[$item['product_id']];
        $item_total = $product['price'] * $item['quantity'];
        $cart_details['items'][] = ['key' => $cart_key, 'id' => $product['id'], 'name' => $product['name'], 'image_url' => $product['image_url'], 'price' => $product['price'], 'quantity' => $item['quantity'], 'size' => $item['size'], 'item_total' => $item_total];
        $cart_details['subtotal'] += $item_total;
    }
    return $cart_details;
}

// 6. (เหมือนเดิม) ดึงข้อมูลตะกร้า
$cart_data = get_cart_details($conn, $_SESSION['cart']);
$subtotal = $cart_data['subtotal'];

// 7. (เหมือนเดิม) คำนวณส่วนลด
$discount_percent = 0; $discount_amount = 0;
$discount_label = "-";
if ($subtotal >= 8000) { $discount_percent = 20; }
elseif ($subtotal >= 6000) { $discount_percent = 15; }
elseif ($subtotal >= 4000) { $discount_percent = 10; }

if ($discount_percent > 0) {
    $discount_amount = ($subtotal * $discount_percent) / 100;
    $discount_label = "PROMOTION -".$discount_percent."%";
}
$grand_total = $subtotal - $discount_amount;
?>

<style>
    .checkout-layout { display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px; margin-top: 30px; margin-bottom: 40px; }
    @media (max-width: 900px) { .checkout-layout { grid-template-columns: 1fr; } .checkout-summary { grid-row: 1; } }
    .checkout-delivery h2 { margin-top: 0; font-size: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    .delivery-tabs { display: flex; gap: 10px; margin-bottom: 25px; margin-top: 15px;}
    .delivery-tab { padding: 10px 20px; border: 1px solid #ccc; background-color: #f9f9f9; color: #555; border-radius: 4px; cursor: pointer; font-weight: bold; }
    .delivery-tab.active { background-color: #2c3e50; color: white; border-color: #2c3e50; }
    .shipping-form h3 { font-size: 18px; margin-bottom: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
    .form-group input[type="text"], .form-group input[type="tel"], .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 15px; }
    .form-group textarea { min-height: 100px; resize: vertical; }
    .form-row { display: flex; gap: 15px; }
    .form-row .form-group { flex: 1; }
    .btn-checkout { background-color: #2c3e50; color: white; padding: 14px 25px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; width: 100%; text-transform: uppercase; }
    .summary-box { background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; padding: 25px; position: sticky; top: 20px; }
    .summary-box h2 { margin-top: 0; font-size: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
    .summary-item { display: grid; grid-template-columns: auto 1fr auto; gap: 15px; align-items: center; margin-bottom: 15px; font-size: 14px; }
    .summary-item img { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
    .summary-item-info p { margin: 0; }
    .summary-item-info .item-name { font-weight: bold; }
    .summary-item-info .item-meta { font-size: 12px; color: #666; }
    .summary-item .item-price { font-weight: bold; }
    .summary-totals { border-top: 1px solid #ddd; padding-top: 15px; font-size: 14px; }
    .summary-totals div { display: flex; justify-content: space-between; margin-bottom: 8px; }
    .summary-totals .discount { color: #e74c3c; font-weight: bold; }
    .summary-totals .order-total { font-size: 18px; font-weight: bold; margin-top: 10px; }
</style>

<main class="container">
    <form action="place_order.php" method="POST" id="checkout-form">
        <div class="checkout-layout">
            
            <div class="checkout-delivery">
                <h2>Delivery</h2>
                
                <div class="delivery-tabs">
                    <div class="delivery-tab active" data-value="Cash on Delivery">Cash on Delivery</div>
                </div>
                <input type="hidden" name="payment_method" id="payment_method" value="Cash on Delivery">

                <div class="shipping-form">
                    <h3>Shipping Address</h3>
                    <div class="form-group">
                        <label for="phone_number">Phone Number:</label>
                        <input type="tel" id="phone_number" name="phone_number" required>
                    </div>
                    <div class="form-group">
                        <label for="customer_name">Name:</label>
                        <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Address:</label>
                        <textarea id="address" name="address" placeholder="บ้านเลขที่, ถนน, หมู่บ้าน, อาคาร" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="country">Country:</label>
                        <input type="text" id="country" name="country" value="Thailand" readonly style="background-color:#eee;">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="province">Province / State:</label>
                            <input type="text" id="province" name="province" required>
                        </div>
                        <div class="form-group">
                            <label for="district">District / Amphoe:</label>
                            <input type="text" id="district" name="district" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sub_district">Sub-district / Tambon:</label>
                            <input type="text" id="sub_district" name="sub_district" required>
                        </div>
                        <div class="form-group">
                            <label for="zip_code">Zip/Postal Code:</label>
                            <input type="text" id="zip_code" name="zip_code" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-checkout">CHECKOUT</button>
                </div>
            </div>
            
            <div class="summary-box">
                <h2>Order Summary</h2>
                
                <?php foreach ($cart_data['items'] as $item): ?>
                <div class="summary-item">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="">
                    <div class="summary-item-info">
                        <p class="item-name"><?php echo htmlspecialchars($item['name']); ?></p>
                        <p class="item-meta">SIZE: <?php echo htmlspecialchars($item['size']); ?> (x<?php echo $item['quantity']; ?>)</p>
                    </div>
                    <span class="item-price"><?php echo number_format($item['item_total'], 2); ?> ฿</span>
                </div>
                <?php endforeach; ?>
                
                <div class="summary-totals">
                    <div>
                        <span>Product Price :</span>
                        <span><?php echo number_format($subtotal, 2); ?> ฿</span>
                    </div>
                    <div class="discount">
                        <span>Discount : (<?php echo $discount_label; ?>)</span>
                        <span>- <?php echo number_format($discount_amount, 2); ?> ฿</span>
                    </div>
                    <div class="order-total">
                        <span>ORDER TOTAL :</span>
                        <span><?php echo number_format($grand_total, 2); ?> ฿</span>
                    </div>
                </div>
            </div>
            
        </div>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.delivery-tab');
    const paymentInput = document.getElementById('payment_method');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            paymentInput.value = this.dataset.value;
        });
    });
});
</script>

<?php
$conn->close();
require_once 'footer.php'; // 12. เรียก Footer
?>