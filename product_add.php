<?php
require_once 'connectdb.php';
require_once 'header.php'; // เรียกใช้ Header

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// 1. ตรวจสอบเมื่อมีการ POST Form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2. รับค่าจากฟอร์ม
    $name = $_POST['name'];
    $description = $_POST['description']; // สมมติว่ามีช่อง description
    $price = $_POST['price'];
    $category = $_POST['category'];
    $brand = $_POST['brand'];
    
    $image_url = ''; // เตรียมตัวแปรไว้เก็บ path รูป

    // 3. จัดการการอัปโหลดไฟล์รูปภาพ
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "images/"; // โฟลเดอร์ที่เราจะเก็บรูป (ต้องสร้างไว้)
        $file_name = basename($_FILES["product_image"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // (Optional) ตรวจสอบว่าเป็นไฟล์รูปภาพจริงหรือไม่
        $check = getimagesize($_FILES["product_image"]["tmp_name"]);
        if($check !== false) {
            // ย้ายไฟล์ที่อัปโหลดไปเก็บใน $target_dir
            if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $image_url = $target_file; // ถ้าสำเร็จ ให้ $image_url คือ path รูป
            } else {
                $_SESSION['message'] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์รูปภาพ";
                $_SESSION['message_type'] = "alert-error";
            }
        } else {
            $_SESSION['message'] = "ไฟล์ที่อัปโหลดไม่ใช่รูปภาพ";
            $_SESSION['message_type'] = "alert-error";
        }
    }

    // 4. บันทึกข้อมูลลง Database (ถ้า $image_url ไม่ว่าง)
    if (!empty($image_url)) {
        $sql = "INSERT INTO products (name, description, price, category, brand, image_url) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        // "ssds_ss" = string, string, double, string, string, string
        $stmt->bind_param("ssdsss", $name, $description, $price, $category, $brand, $image_url);

        if ($stmt->execute()) {
            $_SESSION['message'] = "เพิ่มสินค้าใหม่สำเร็จ!";
            $_SESSION['message_type'] = "alert-success";
        } else {
            $_SESSION['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt->error;
            $_SESSION['message_type'] = "alert-error";
        }
        $stmt->close();
    }
    
    $conn->close();
    header("Location: dashboard.php"); // กลับไปหน้า Dashboard
    exit;
}
?>

<link rel="stylesheet" href="admin_form.css">

<div class="form-container">
    <h2>เพิ่มสินค้าใหม่</h2>
    
    <form action="product_add.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">ชื่อสินค้า (Name)</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="description">คำอธิบาย (Description)</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        
        <div class="form-group">
            <label for="price">ราคา (Price)</label>
            <input type="number" id="price" name="price" step="0.01" required>
        </div>
        
        <div class="form-group">
            <label for="category">หมวดหมู่ (Category)</label>
            <select id="category" name="category" required>
                <option value="NEW ARRIVALS">NEW ARRIVALS</option>
                <option value="SPEED">SPEED</option>
                <option value="CONTROL">CONTROL</option>
                <option value_value="TOUCH">TOUCH</option>
                <option value="BEST SELLERS">BEST SELLERS</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="brand">แบรนด์ (Brand)</label>
            <input type="text" id="brand" name="brand" value="IRONBOOTS" required>
        </div>

        <div class="form-group">
            <label for="product_image">รูปภาพสินค้า (Image)</label>
            <input type="file" id="product_image" name="product_image" required accept="image/*">
        </div>
        
        <button type="submit" class="btn-submit">บันทึกสินค้า</button>
        <a href="dashboard.php" class="btn-cancel">ยกเลิก</a>
    </form>
</div>

<?php
require_once 'footer.php'; // เรียกใช้ Footer
?>