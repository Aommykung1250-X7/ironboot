<?php
// login_process.php
session_start();
include 'connectdb.php'; // เรียกใช้ไฟล์เชื่อมต่อ DB

// 1. ตรวจสอบว่าเป็นการส่งแบบ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 2. รับค่า
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // 3. ตรวจสอบ Validation
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "กรุณากรอก Username และ Password";
        header("Location: login.php");
        exit;
    }

    // 4. ค้นหา User (เหมือนเดิม)
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        // 5. พบ User
        $user = mysqli_fetch_assoc($result);

        // 6. ตรวจสอบรหัสผ่านแบบ Plain Text
        if ($password === $user['password']) {
            
            // 7. รหัสผ่านถูกต้อง! สร้าง Session
            session_regenerate_id(true); 
            
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; 
            
            unset($_SESSION['error']);
            
            // =============================================
            // *** 🟢 START: (Request) แก้ไข Redirect ***
            // =============================================
            
            // เช็ก role แล้วส่งไปคนละหน้า
            if ($_SESSION['role'] === 'admin') {
                header("Location: dashboard.php"); // Admin ไปหน้า Dashboard
            } else {
                header("Location: index.php"); // User ทั่วไปไปหน้า Index
            }
            exit;
            
            // =============================================
            // *** 🟢 END: (Request) แก้ไข Redirect ***
            // =============================================

        } else {
            // 9. รหัสผ่านไม่ถูกต้อง
            $_SESSION['error'] = "Username หรือ Password ไม่ถูกต้อง";
            header("Location: login.php");
            exit;
        }
    } else {
        // 10. ไม่พบ User
        $_SESSION['error'] = "Username หรือ Password ไม่ถูกต้อง";
        header("Location: login.php");
        exit;
    }

} else {
    // ถ้าไม่ได้เข้ามาแบบ POST ให้เด้งกลับ
    header("Location: login.php");
    exit;
}
?>