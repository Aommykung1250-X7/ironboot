<?php
require_once 'connectdb.php';
require_once 'header.php'; // เรียกใช้ Header

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$product_id = $_GET['id']; // 1. รับ ID สินค้าจาก URL

// 2. จัดการเมื่อมีการ POST Form (การอัปเดต)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $brand = $_POST['brand'];
    $current_image_url = $_POST['current_image_url']; // รับ path รูปเดิม
    
    $image_url = $current_image_url; // 3. ตั้งค่ารูปใหม่ให้เป็นรูปเดิมไว้ก่อน

    // 4. ตรวจสอบว่ามีการอัปโหลด "รูปใหม่" หรือไม่
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0 && $_FILES['product_image']['size'] > 0) {
        $target_dir = "images/";
        $file_name = basename($_FILES["product_image"]["name"]);
        $target_file = $target_dir . $file_name;
        
        // ย้ายไฟล์ใหม่
        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            $image_url = $target_file; // 4.1 ถ้าสำเร็จ ให้อัปเดต $image_url เป็น path ใหม่
            
            // (Optional) ลบรูปเก่าทิ้ง ถ้าไม่ซ้ำกับรูปใหม่
            if ($current_image_url != $image_url && file_exists($current_image_url)) {
                unlink($current_image_url);
            }
        }
    }
    
    // 5. อัปเดตข้อมูลลง Database
    $sql = "UPDATE products SET name = ?, description = ?, price = ?, category = ?, brand = ?, image_url = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsssi", $name, $description, $price, $category, $brand, $image_url, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "อัปเดตสินค้าสำเร็จ!";
        $_SESSION['message_type'] = "alert-success";
    } else {
        $_SESSION['message'] = "เกิดข้อผิดพลาด: " . $stmt->error;
        $_SESSION['message_type'] = "alert-error";
    }
    $stmt->close();
    $conn->close();
    header("Location: dashboard.php");
    exit;
}

// 6. ดึงข้อมูลสินค้าเดิมมาแสดง (เมื่อเข้ามาหน้านี้ครั้งแรก)
$sql_select = "SELECT * FROM products WHERE id = ?";
$stmt_select = $conn->prepare($sql_select);
$stmt_select->bind_param("i", $product_id);
$stmt_select->execute();
$result = $stmt_select->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "ไม่พบสินค้า";
    exit;
}
$stmt_select->close();
?>

<link rel="stylesheet" href="admin_form.css">

<div class="form-container">
    <h2>แก้ไขสินค้า (ID: <?php echo $product_id; ?>)</h2>
    
    <form action="product_edit.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
        
        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
        <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>">

        <div class="form-group">
            <label for="name">ชื่อสินค้า (Name)</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description">คำอธิบาย (Description)</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="price">ราคา (Price)</label>
            <input type="number" id="price" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="category">หมวดหมู่ (Category)</label>
            <select id="category" name="category" required>
                <option value="NEW ARRIVALS" <?php if($product['category'] == 'NEW ARRIVALS') echo 'selected'; ?>>NEW ARRIVALS</option>
                <option value="SPEED" <?php if($product['category'] == 'SPEED') echo 'selected'; ?>>SPEED</option>
                <option value="CONTROL" <?php if($product['category'] == 'CONTROL') echo 'selected'; ?>>CONTROL</option>
                <option value="TOUCH" <?php if($product['category'] == 'TOUCH') echo 'selected'; ?>>TOUCH</option>
                <option value="BEST SELLERS" <?php if($product['category'] == 'BEST SELLERS') echo 'selected'; ?>>BEST SELLERS</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="brand">แบรนด์ (Brand)</label>
            <input type="text" id="brand" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>" required>
        </div>

        <div class="form-group">
            <label>รูปภาพปัจจุบัน</label>
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Current Image" style="width: 100px; height: auto; margin-bottom: 10px;">
        </div>

        <div class="form-group">
            <label for="product_image">อัปโหลดรูปภาพใหม่ (หากต้องการเปลี่ยน)</label>
            <input type="file" id="product_image" name="product_image" accept="image/*">
        </div>
        
        <button type="submit" class="btn-submit">อัปเดตสินค้า</button>
        <a href="dashboard.php" class="btn-cancel">ยกเลิก</a>
    </form>
</div>

<?php
$conn->close();
require_once 'footer.php'; // เรียกใช้ Footer
?>