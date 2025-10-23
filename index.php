<?php
    require_once 'header.php'; // 1. เรียก Header (มี session_start())
    require_once 'connectdb.php'; // 2. เชื่อมต่อ DB

    // 🟢 (เพิ่มใหม่ 1/4) ดึงรายการ Favorite ของ User (ถ้า Login)
    $user_favorites = []; // 1. สร้าง Array ว่างไว้ก่อน
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // 2. ดึง ID สินค้าที่ User คนนี้กด Favorite
        $fav_sql = "SELECT product_id FROM favorites WHERE user_id = ?";
        $fav_stmt = $conn->prepare($fav_sql);
        $fav_stmt->bind_param("i", $user_id);
        $fav_stmt->execute();
        $fav_result = $fav_stmt->get_result();
        
        // 3. ยัด ID สินค้าทั้งหมดลงใน Array
        while ($fav_row = $fav_result->fetch_assoc()) {
            $user_favorites[] = $fav_row['product_id'];
        }
        $fav_stmt->close();
    }
    // (ตอนนี้ $user_favorites อาจจะมีค่าเป็น [12, 15, 2])
    
    
    // 🟢 (แก้ไข 2/4) อัปเกรดฟังก์ชัน
    // (เพิ่ม $user_favorites เข้าไปเป็น Parameter)
    function display_product_section($conn, $category_name, $category_id, $user_favorites) {
        $sql = "SELECT * FROM products WHERE category = ? ORDER BY id ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo '<section class="product-section" id="' . htmlspecialchars($category_id) . '">'; 
            echo '  <div class="container">';
            echo '    <h2 class="section-title">' . htmlspecialchars($category_name) . '</h2>';
            echo '    <div class="product-grid">';

            while($row = $result->fetch_assoc()) {
                
                // 🟢 (แก้ไข 3/4) ตรวจสอบและเปลี่ยนปุ่ม ♡
                $product_id = $row['id'];
                
                // 1. เช็กว่า ID สินค้านี้ อยู่ใน Array $user_favorites หรือไม่?
                $is_favorited = in_array($product_id, $user_favorites);
                
                // 2. ถ้าอยู่ ให้ใส่ class 'active' และเปลี่ยนไอคอน
                $active_class = $is_favorited ? 'active' : '';
                $icon_char = $is_favorited ? '♥' : '♡'; // (หัวใจทึบ / หัวใจกลวง)

                echo '<div class="product-card">';
                
                // 3. (สำคัญ) เปลี่ยนจาก <div> เป็น <button>
                echo '  <button class="wishlist-btn ' . $active_class . '" data-product-id="' . $product_id . '">';
                echo        $icon_char;
                echo '  </button>';
                
                // (โค้ดแสดงสินค้าส่วนที่เหลือ เหมือนเดิม)
                echo '  <a href="product_detail.php?id=' . $row['id'] . '">';
                echo '      <img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['name']) . '">';
                echo '  </a>';
                echo '  <a href="product_detail.php?id=' . $row['id'] . '" class="product-name-link">';
                echo '      <h3 class="product-name">' . htmlspecialchars($row['name']) . '</h3>';
                echo '  </a>';
                echo '  <p class="product-price">' . number_format($row['price'], 2) . ' ฿</p>';
                echo '</div>';
            }

            echo '    </div>'; 
            echo '  </div>'; 
            echo '</section>';
        }
        $stmt->close();
    }
?>

    <main>
        <section class="promo-section">
            <div class="container">
                <img src="images/promo_banner.jpg" alt="Promotion Banner" class="promo-banner-img"> 
                <div class="promo-info">
                    <h2>IRONBOOTS PROMOTION</h2>
                    <p>Get discounts up to 20%! Shop now and experience the difference.</p>
                    <a href="#" class="shop-now-link">SHOP NOW</a>
                </div>
            </div>
        </section>
        <?php
            // 🟢 (แก้ไข 4/4) ส่ง $user_favorites (Array) เข้าไปในฟังก์ชัน
            display_product_section($conn, "NEW ARRIVALS", "new-arrivals", $user_favorites);
            display_product_section($conn, "SPEED", "speed", $user_favorites);
            display_product_section($conn, "CONTROL", "control", $user_favorites);
            display_product_section($conn, "TOUCH", "touch", $user_favorites);
            display_product_section($conn, "BEST SELLERS", "best-sellers", $user_favorites);
        ?>
    </main>

    <?php
        $conn->close();
    ?>

<?php
    require_once 'footer.php';
?>