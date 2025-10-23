<?php
    require_once 'header.php'; // 1. เรียก Header (มี session_start())
    require_once 'connectdb.php'; // 2. เชื่อมต่อ DB

    // 3. (สำคัญ) ตรวจสอบ: ถ้ายังไม่ Login, ส่งไปหน้า Login
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = "กรุณา Login เพื่อดูสินค้ารายการโปรด";
        $_SESSION['message_type'] = "alert-error"; // (สร้าง Alert)
        header("Location: login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // 4. (Query หลัก) ดึง "สินค้า" (จากตาราง products)
    //    โดย "เชื่อม" (JOIN) กับตาราง favorites
    //    เฉพาะที่ "user_id" ตรงกับเรา
    $sql = "SELECT p.* FROM products p
            JOIN favorites f ON p.id = f.product_id
            WHERE f.user_id = ?
            ORDER BY f.created_at DESC"; // (เรียงตามที่กดล่าสุด)

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

?>
<style>
    /* (เพิ่ม) CSS สำหรับจัดหน้า */
    .favorite-page-title {
        font-size: 28px;
        font-weight: bold;
        text-align: center;
        margin-top: 15px;
        margin-bottom: 40px;
        color: #333;
        position: relative;
        padding-bottom: 15px;
    }
    .favorite-page-title::after {
        content: '';
        position: absolute;
        left: 50%;
        bottom: 0;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background-color: #e74c3c; /* สีแดง (สีเดียวกับหัวใจ) */
        border-radius: 2px;
    }
    .no-favorites-message {
        text-align: center;
        font-size: 18px;
        color: #777;
        margin-top: 50px;
    }
</style>

<main class="container">
    <h1 class="favorite-page-title">สินค้าที่ชอบ</h1>

    <?php if ($result->num_rows > 0): ?>
        
        <div class="product-grid">
            
            <?php while($row = $result->fetch_assoc()): ?>
                
                <div class="product-card">
                    <button class="wishlist-btn active" data-product-id="<?php echo $row['id']; ?>">
                        ♥
                    </button>
                    
                    <a href="product_detail.php?id=<?php echo $row['id']; ?>">
                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    </a>
                    <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="product-name-link">
                        <h3 class="product-name"><?php echo htmlspecialchars($row['name']); ?></h3>
                    </a>
                    <p class="product-price"><?php echo number_format($row['price'], 2); ?> ฿</p>
                </div>

            <?php endwhile; ?>
            
        </div>

    <?php else: ?>
        
        <p class="no-favorites-message">คุณยังไม่มีสินค้ารายการโปรด</p>
        
    <?php endif; ?>

</main>

<?php
    $stmt->close();
    $conn->close();
    require_once 'footer.php'; // (สำคัญ) footer.php มี JS ที่ทำให้ปุ่ม ♡ คลิกได้
?>