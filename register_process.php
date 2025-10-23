<?php
// register_process.php
session_start();
include 'connectdb.php'; // เรียกใช้ไฟล์เชื่อมต่อ DB

// 1. ตรวจสอบว่าเป็นการส่งแบบ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. รับค่าและตัดช่องว่าง
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // 3. ตรวจสอบ Validation (เหมือนเดิม)
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
        header("Location: register.php");
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "รูปแบบอีเมลไม่ถูกต้อง";
        header("Location: register.php");
        exit;
    }
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
        header("Location: register.php");
        exit;
    }

    // 4. ตรวจสอบ Username หรือ Email ซ้ำ (เหมือนเดิม)
    $sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) > 0) {
        $_SESSION['error'] = "Username หรือ Email นี้มีผู้ใช้งานแล้ว";
        header("Location: register.php");
        exit;
    }
    mysqli_stmt_close($stmt_check);

    // 5. (!!! แก้ไข) ไม่ต้อง Hash รหัสผ่าน
    // $hashed_password = password_hash($password, PASSWORD_DEFAULT); // <-- ลบบรรทัดนี้
    
    // 6. บันทึก User ใหม่ลง Database
    $sql_insert = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt_insert = mysqli_prepare($conn, $sql_insert);
    
    // (!!! แก้ไข) ส่ง $password (ตัวแปรที่รับมา) ลงไปตรงๆ แทน $hashed_password
    mysqli_stmt_bind_param($stmt_insert, "sss", $username, $email, $password); 

    if (mysqli_stmt_execute($stmt_insert)) {
        // 7. สมัครสำเร็จ
        $_SESSION['success'] = "สมัครสมาชิกสำเร็จ! กรุณาล็อกอิน";
        header("Location: login.php");
        exit;
    } else {
        // 8. หากเกิดข้อผิดพลาด
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการสมัคร: " . mysqli_error($conn);
        header("Location: register.php");
        exit;
    }

} else {
    // ถ้าไม่ได้เข้ามาแบบ POST ให้เด้งกลับ
    header("Location: register.php");
    exit;
}
?>