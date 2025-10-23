<?php
    require_once 'header.php'; // 1. เรียก Header
    require_once 'connectdb.php'; // 2. เชื่อมต่อ DB

    // 3. ตรวจสอบและรับ ID
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo "<div class='container'><p>ไม่พบสินค้า</p></div>";
        require_once 'footer.php';
        exit;
    }
    $product_id = $_GET['id'];

    // 4. ค้นหาสินค้า
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1):
        $product = $result->fetch_assoc();
?>

    <main class="container">
        <div class="product-detail-layout">
            
            <div class="product-detail-left">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <p class="image-caption">Ironbots Precision: Perfect Every Pass</p>
            </div>
            
            <div class="product-detail-right">
                
                <h1 class="product-detail-name"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="stock-status in-stock">STOCK STATUS : IN STOCK</div>
                
                <span class="product-detail-price"><?php echo number_format($product['price'], 2); ?> ฿</span>

                <form id="add-to-cart-form" class="cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    
                    <div class="size-selector">
                        <label>SIZE :</label>
                        <div class="size-options">
                            <button type="button" class="size-btn" data-size="40.5">40.5</button>
                            <button type="button" class="size-btn" data-size="41">41</button>
                            <button type="button" class="size-btn" data-size="41.5">41.5</button>
                            <button type="button" class="size-btn" data-size="42">42</button>
                            <button type="button" class="size-btn" data-size="42.5">42.5</button>
                            <button type="button" class="size-btn" data-size="43">43</button>
                            <button type="button" class="size-btn" data-size="43.5">43.5</button>
                            <button type="button" class="size-btn" data-size="44">44</button>
                            <button type="button" class="size-btn" data-size="44.5">44.5</button>
                            <button type="button" class="size-btn" data-size="45">45</button>
                        </div>
                        <input type="hidden" name="size" id="selected-size" value="">
                    </div>
                    
                    <a href="#size-chart" class="size-chart-link">Size Chart</a>

                    <div class="quantity-selector">
                        <label>Quantity :</label>
                        <button type="button" class="qty-btn" data-action="decrease">-</button>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" readonly>
                        <button type="button" class="qty-btn" data-action="increase">+</button>
                    </div>

                    <button type="submit" class="btn-add-to-cart">ADD TO CART</button>
                    <span id="add-to-cart-feedback" style="margin-left: 10px; color: green;"></span>
                </form>

            </div>
        </div>
        
        <div id="size-chart" class="size-chart-section">
            <h2>Size Chart</h2>
            <table class="size-chart-table">
                <thead>
                    <tr>
                        <th>US</th>
                        <th>UK</th>
                        <th>EU</th>
                        <th>JP (mm)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>7</td><td>6</td><td>40</td><td>250</td></tr>
                    <tr><td>7.5</td><td>6.5</td><td>40.5</td><td>255</td></tr>
                    <tr><td>8</td><td>7</td><td>41</td><td>260</td></tr>
                    <tr><td>8.5</td><td>7.5</td><td>41.5</td><td>265</td></tr>
                    <tr><td>9</td><td>8</td><td>42</td><td>270</td></tr>
                    <tr><td>9.5</td><td>8.5</td><td>42.5</td><td>275</td></tr>
                    <tr><td>10</td><td>9</td><td>43</td><td>280</td></tr>
                    <tr><td>10.5</td><td>9.5</td><td>43.5</td><td>285</td></tr>
                    <tr><td>11</td><td>10</td><td>44</td><td>290</td></tr>
                    <tr><td>11.5</td><td>10.5</td><td>44.5</td><td>295</td></tr>
                    <tr><td>12</td><td>11</td><td>45</td><td>300</td></tr>
                </tbody>
            </table>
        </div>

    </main>

<?php
    else:
        // 8. ถ้าไม่เจอสินค้า
        echo "<div class='container'><p>ไม่พบสินค้าที่คุณค้นหา (ID: $product_id)</p></div>";
    endif;

    $stmt->close();
    $conn->close();
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // === 1. จัดการตัวเลือก Size ===
    const sizeOptions = document.querySelector('.size-options');
    const selectedSizeInput = document.getElementById('selected-size');

    if (sizeOptions) {
        sizeOptions.addEventListener('click', function(e) {
            if (e.target.classList.contains('size-btn')) {
                // ลบ .selected ออกจากปุ่มอื่น
                sizeOptions.querySelectorAll('.size-btn').forEach(btn => {
                    btn.classList.remove('selected');
                });
                // เพิ่ม .selected ให้ปุ่มที่คลิก
                e.target.classList.add('selected');
                // อัปเดตค่าใน input ที่ซ่อนไว้
                selectedSizeInput.value = e.target.dataset.size;
            }
        });
    }

    // === 2. จัดการปุ่ม +/- Quantity ===
    const qtyInput = document.getElementById('quantity');
    document.querySelector('.quantity-selector').addEventListener('click', function(e) {
        if (e.target.classList.contains('qty-btn')) {
            let currentQty = parseInt(qtyInput.value);
            const action = e.target.dataset.action;

            if (action === 'increase') {
                qtyInput.value = currentQty + 1;
            } else if (action === 'decrease' && currentQty > 1) {
                qtyInput.value = currentQty - 1;
            }
        }
    });

    // === 3. (Bonus) ทำให้ลิงก์ Size Chart เลื่อนแบบ Smooth ===
    const sizeChartLink = document.querySelector('.size-chart-link');
    if (sizeChartLink) {
        sizeChartLink.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(e.target.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    }

    // === 4. (Bonus) ตรวจสอบว่าเลือก Size หรือยัง ก่อนกด Add to cart ===
    const addToCartForm = document.getElementById('add-to-cart-form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            if (selectedSizeInput.value === "") {
                e.preventDefault(); // หยุดการส่งฟอร์ม
                alert("กรุณาเลือกไซส์ (Please select a size)");
            }
            // ถ้าเลือกแล้ว AJAX (จาก footer.php) จะทำงานตามปกติ
        });
    }
});
</script>

<?php
    require_once 'footer.php'; // 10. เรียก Footer
?>