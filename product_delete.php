<?php
session_start();
require_once 'connectdb.php';

// 1. ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = "คุณไม่มีสิทธิ์ดำเนินการ";
    $_SESSION['message_type'] = "alert-error";
    header("Location: index.php");
    exit;
}

// 2. ตรวจสอบว่ามี ID ส่งมาหรือไม่
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id = $_GET['id'];

// 3. (แนะนำ) ค้นหา path รูปภาพก่อนลบ เพื่อลบไฟล์ออกจาก server ด้วย
$sql_find_image = "SELECT image_url FROM products WHERE id = ?";
$stmt_find = $conn->prepare($sql_find_image);
$stmt_find->bind_param("i", $id);
$stmt_find->execute();
$result = $stmt_find->get_result();
if ($row = $result->fetch_assoc()) {
    $image_path_to_delete = $row['image_url'];
}
$stmt_find->close();


// 4. ลบข้อมูลออกจาก Database
$sql_delete = "DELETE FROM products WHERE id = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("i", $id);

if ($stmt_delete->execute()) {
    $_SESSION['message'] = "ลบสินค้า (ID: $id) สำเร็จ!";
    $_SESSION['message_type'] = "alert-success";

    // 5. ลบไฟล์รูปภาพออกจาก server
    if (isset($image_path_to_delete) && file_exists($image_path_to_delete)) {
        unlink($image_path_to_delete);
    }

} else {
    $_SESSION['message'] = "เกิดข้อผิดพลาดในการลบ: " . $stmt_delete->error;
    $_SESSION['message_type'] = "alert-error";
}

$stmt_delete->close();
$conn->close();

// 6. กลับไปหน้า Dashboard
header("Location: dashboard.php");
exit;
?>