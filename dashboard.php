<?php
// dashboard.php
include 'connectdb.php'; // 1. เชื่อมต่อ DB
include 'header.php';    // 2. เรียกใช้ Header

// 3. ตรวจสอบสิทธิ์ (เหมือนเดิม)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// 4. ดึงข้อมูล (เหมือนเดิม)
$products_result = mysqli_query($conn, "SELECT * FROM products ORDER BY id ASC");
$orders_result = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC");
?>

<link rel="stylesheet" href="admin_style.css">

<div class="admin-container">
    <h1>Admin Dashboard</h1>
    <p>สวัสดี, <?php echo htmlspecialchars($_SESSION['username']); ?>! นี่คือส่วนจัดการระบบหลังบ้าน</p>
    
    <?php
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
    
    <div class="admin-tabs">
        <a class="tab-link active" data-target="products">📦 จัดการคลังสินค้า</a>
        <a class="tab-link" data-target="orders">🧾 จัดการคำสั่งซื้อ</a>
    </div>

    <div class="admin-tab-content">

        <div id="products" class="tab-pane active">
            <div class="admin-card"> <h2>จัดการคลังสินค้า (Products)</h2>
                <div class="admin-actions">
                    <a href="product_add.php" class="btn-add">เพิ่มสินค้าใหม่</a>
                </div>
                <table class="admin-table"> 
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($products_result && mysqli_num_rows($products_result) > 0):
                            while ($product = mysqli_fetch_assoc($products_result)):
                        ?>
                                <tr>
                                    <td><img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                                    <td class="actions">
                                        <a href="product_edit.php?id=<?php echo $product['id']; ?>">Edit</a>
                                        <a href="product_delete.php?id=<?php echo $product['id']; ?>" class="delete" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสินค้านี้?');">Delete</a>
                                    </td>
                                </tr>
                        <?php
                            endwhile; 
                        else:
                        ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">ไม่พบข้อมูลสินค้า</td>
                            </tr>
                        <?php
                        endif; 
                        ?>
                    </tbody>
                </table>
            </div> </div>

        <div id="orders" class="tab-pane">
            <div class="admin-card"> <h2>จัดการคำสั่งซื้อ (Orders)</h2>
                <table class="admin-table"> 
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>ลูกค้า (Customer)</th>
                            <th>วันที่ (Date)</th>
                            <th>สถานะ (Status)</th>
                            <th>ยอดรวม (Total)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($orders_result && mysqli_num_rows($orders_result) > 0):
                            while ($order = mysqli_fetch_assoc($orders_result)):
                        ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <span class="status-<?php echo htmlspecialchars($order['order_status']); ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($order['total_price'], 2); ?> ฿</td>
                                    <td class="actions">
                                        <a href="admin_order_detail.php?id=<?php echo $order['id']; ?>" class="btn-view">View</a>
                                    </td>
                                </tr>
                        <?php
                            endwhile; 
                        else:
                        ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">ยังไม่มีคำสั่งซื้อ</td>
                            </tr>
                        <?php
                        endif; 
                        ?>
                    </tbody>
                </table>
            </div> </div>

    </div> </div> <script>
document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabPanes = document.querySelectorAll('.tab-pane');
    tabLinks.forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault(); 
            const targetId = link.getAttribute('data-target'); 
            tabLinks.forEach(l => l.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            link.classList.add('active');
            document.getElementById(targetId).classList.add('active');
        });
    });
    const hash = window.location.hash;
    if (hash) {
        const targetId = hash.substring(1); 
        const targetLink = document.querySelector(`.tab-link[data-target="${targetId}"]`);
        if (targetLink) {
            targetLink.click();
        }
    }
});
</script>

<?php
include 'footer.php'; // 8. เรียกใช้ Footer
?>