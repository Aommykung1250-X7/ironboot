<?php
session_start();
require_once 'connectdb.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // 1. ตะกร้าตอนนี้เป็น Array
}

$action = $_GET['action'] ?? 'get';

// ========= 1. ACTION: ADD (อัปเกรดใหม่) =========
if ($action == 'add') {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $size = $_POST['size']; // 2. รับค่า Size ที่ส่งมาจาก Form

    if ($quantity <= 0) $quantity = 1;
    if (empty($size)) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณาเลือกไซส์']);
        exit;
    }

    // 3. สร้าง Key เฉพาะสำหรับ "สินค้า + ไซส์" (เช่น '12_40.5')
    $cart_key = $product_id . '_' . $size;

    if (isset($_SESSION['cart'][$cart_key])) {
        // 4. ถ้ามี Key นี้อยู่แล้ว (สินค้าเดียวกัน ไซส์เดียวกัน) -> ให้บวกจำนวน
        $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
    } else {
        // 5. ถ้ายังไม่มี -> ให้เพิ่ม Array ใหม่เข้าไป
        $_SESSION['cart'][$cart_key] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'size' => $size
        ];
    }
    
    echo json_encode(['status' => 'success', 'message' => 'เพิ่มสินค้าลงตะกร้าแล้ว']);
    exit;
}

// ========= 2. ACTION: UPDATE (อัปเกรดใหม่) =========
if ($action == 'update') {
    // 6. ตอนนี้เราจะส่ง 'cart_key' (เช่น '12_40.5') มาแทน product_id
    $cart_key = $_POST['cart_key']; 
    $quantity = (int)$_POST['quantity'];

    if (isset($_SESSION['cart'][$cart_key])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$cart_key]); // ลบถ้าจำนวนเป็น 0
        } else {
            $_SESSION['cart'][$cart_key]['quantity'] = $quantity; // อัปเดตจำนวน
        }
    }
    exit;
}

// ========= 3. ACTION: GET (อัปเกรดใหม่) =========
if ($action == 'get') {
    $cart_html = '';
    $total_price = 0;
    $total_count = 0;

    if (empty($_SESSION['cart'])) {
        $cart_html = "<p class='cart-empty-message'>Your cart is empty.</p>";
    } else {
        // 7. ดึง ID สินค้าทั้งหมด (อาจซ้ำกันได้ถ้าคนละไซส์ แต่ไม่เป็นไร)
        $product_ids = [];
        foreach ($_SESSION['cart'] as $item) {
            $product_ids[] = $item['product_id'];
        }
        $product_ids = array_unique($product_ids); // เอา ID ที่ซ้ำออก

        if (count($product_ids) > 0) {
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $types = str_repeat('i', count($product_ids));
            
            $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$product_ids);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // 8. เก็บข้อมูลสินค้าไว้ใน $products_data เพื่อให้ดึงใช้ง่าย
            $products_data = [];
            while ($product = $result->fetch_assoc()) {
                $products_data[$product['id']] = $product;
            }
            $stmt->close();

            // 9. วนลูปจาก Session Cart (ไม่ใช่จาก DB)
            foreach ($_SESSION['cart'] as $cart_key => $item) {
                // 10. ตรวจสอบว่าสินค้ายังมีใน DB
                if (!isset($products_data[$item['product_id']])) continue; 
                
                $product = $products_data[$item['product_id']];
                $quantity = $item['quantity'];
                $size = $item['size'];
                $subtotal = $product['price'] * $quantity;
                
                $total_price += $subtotal;
                $total_count += $quantity;

                // 11. (สำคัญ) ส่ง $cart_key ไปให้ปุ่มลบและช่อง input
                $cart_html .= '
                    <div class="cart-item">
                        <img src="' . htmlspecialchars($product['image_url']) . '" alt="">
                        <div class="cart-item-info">
                            <span class="cart-item-name">' . htmlspecialchars($product['name']) . '</span>
                            <span class="cart-item-size">Size: ' . htmlspecialchars($size) . '</span> 
                            <span class="cart-item-price">' . number_format($product['price']) . ' ฿</span>
                            <div class="cart-item-qty">
                                <label>Qty:</label>
                                <input type="number" class="cart-qty-input" value="' . $quantity . '" min="1" data-key="' . $cart_key . '">
                            </div>
                        </div>
                        <button class="cart-remove-btn" data-key="' . $cart_key . '">&times;</button>
                    </div>
                ';
            }
        }
    }
    
    $conn->close();

    echo json_encode([
        'count' => $total_count,
        'html' => $cart_html,
        'total_price' => number_format($total_price, 2)
    ]);
    exit;
}
?>