<?php
    // 1. เรียก Header (ซึ่งมี session_start() และ Navbar แล้ว)
    require_once 'header.php';
    
    // 2. เชื่อมต่อฐานข้อมูล (ย้าย connectdb.php มาไว้ตรงนี้)
    require_once 'connectdb.php';

    // 3. ฟังก์ชัน display_product_section (โค้ดเดิมของคุณ)
    function display_product_section($conn, $category_name, $category_id) {
        // ... (โค้ดฟังก์ชันของคุณเหมือนเดิมทุกประการ) ...
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
                echo '<div class="product-card">';
                echo '  <div class="wishlist-icon">♡</div>';
                // ===== 🟢 (แก้ไขตรงนี้) =====
                // 1. ทำให้ "รูปภาพ" เป็นลิงก์
                echo '  <a href="product_detail.php?id=' . $row['id'] . '">';
                echo '      <img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['name']) . '">';
                echo '  </a>';
                
                // 2. ทำให้ "ชื่อสินค้า" เป็นลิงก์
                echo '  <a href="product_detail.php?id=' . $row['id'] . '" class="product-name-link">';
                echo '      <h3 class="product-name">' . htmlspecialchars($row['name']) . '</h3>';
                echo '  </a>';
                // ===== 🟢 (จบส่วนแก้ไข) =====
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
            // 5. เรียกใช้ฟังก์ชัน (โค้ดเดิมของคุณ)
            display_product_section($conn, "NEW ARRIVALS", "new-arrivals");
            display_product_section($conn, "SPEED", "speed");
            display_product_section($conn, "CONTROL", "control");
            display_product_section($conn, "TOUCH", "touch");
            display_product_section($conn, "BEST SELLERS", "best-sellers");
        ?>
    </main>

    <?php
        // 6. ปิดการเชื่อมต่อฐานข้อมูล (ก่อนเรียก footer)
        $conn->close();
    ?>

<?php
    // 7. เรียก Footer (ซึ่งมี <footer>, </body>, </html>)
    require_once 'footer.php';
?>