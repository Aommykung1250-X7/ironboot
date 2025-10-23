<?php
session_start();
require_once 'connectdb.php';

// 1. ตรวจสอบ: ต้อง Login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'login_required']);
    exit;
}
$user_id = $_SESSION['user_id'];

// 2. แยก Action
// (ถ้าไม่ส่ง 'action' มา, ให้ถือว่าเป็น 'toggle' (เพิ่ม/ลบ))
$action = $_REQUEST['action'] ?? 'toggle'; 

// ===================================================
// ACTION: GET COUNT (แบบเดียวกับตะกร้า)
// (JavaScript จะเรียกอันนี้เพื่ออัปเดตตัวเลข)
// ===================================================
if ($action == 'get_count') {
    try {
        $sql_count = "SELECT COUNT(id) AS fav_count FROM favorites WHERE user_id = ?";
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param("i", $user_id);
        $stmt_count->execute();
        $count = $stmt_count->get_result()->fetch_assoc()['fav_count'];
        
        // 4. ส่ง "จำนวน" กลับไป
        echo json_encode(['status' => 'success', 'count' => $count]);
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// ===================================================
// (เดิม) ACTION: TOGGLE (เพิ่ม/ลบ)
// (JavaScript จะเรียกอันนี้ตอนกด ♡)
// ===================================================
if ($action == 'toggle') {
    $product_id = (int)$_POST['product_id'];
    if ($product_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product']);
        exit;
    }

    try {
        // 5. เช็กว่าเคย Favorite หรือยัง
        $sql_check = "SELECT id FROM favorites WHERE user_id = ? AND product_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ii", $user_id, $product_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            // 6. ถ้าเคย -> ลบออก
            $sql_delete = "DELETE FROM favorites WHERE user_id = ? AND product_id = ?";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->bind_param("ii", $user_id, $product_id);
            $stmt_delete->execute();
            // 7. (สำคัญ) ส่งกลับไปแค่ว่า "ลบแล้ว" (ไม่ต้องนับ)
            echo json_encode(['status' => 'success', 'action' => 'removed']);
        } else {
            // 8. ถ้ายังไม่เคย -> เพิ่มใหม่
            $sql_insert = "INSERT INTO favorites (user_id, product_id) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ii", $user_id, $product_id);
            $stmt_insert->execute();
            // 9. (สำคัญ) ส่งกลับไปแค่ว่า "เพิ่มแล้ว" (ไม่ต้องนับ)
            echo json_encode(['status' => 'success', 'action' => 'added']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
$conn->close();
?>