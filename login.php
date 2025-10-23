<?php
require_once 'connectdb.php'; // 1. เชื่อมต่อฐานข้อมูล
session_start(); // 2. เริ่ม session

// ถ้า login แล้ว ให้เด้งไปหน้า index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error_message = '';

// 3. ตรวจสอบว่ามีการ POST ข้อมูลมาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = $_POST['username'];
    $password = $_POST['password']; // รับรหัสผ่านดิบๆ

    // 4. ค้นหา user จาก username และ password (แบบไม่ hash)
    // นี่คือส่วนที่อันตราย!
    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // 5. ตรวจสอบว่าเจอ User หรือไม่
    if ($result->num_rows == 1) {
        // 6. ถ้าเจอ (Login ถูกต้อง)
        $user = $result->fetch_assoc();
        
        // 7. เก็บข้อมูล user ลงใน Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // เก็บ role (admin/user)
        
        // 8. ส่งไปหน้าหลัก
        header("Location: index.php");
        exit;
        
    } else {
        // 9. ถ้าไม่เจอ (Login ผิด)
        $error_message = "Username หรือ Password ไม่ถูกต้อง";
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IRONBOOTS</title>
    <link rel="stylesheet" href="auth_style.css"> </head>
<body>
    <div class="auth-container">
        <h2>LOGIN</h2>
        <p>เข้าสู่ระบบ IRONBOOTS</p>
        
        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="auth-button">Login</button>
        </form>
        <div class="switch-link">
            ยังไม่มีบัญชี? <a href="register.php">Register ที่นี่</a>
        </div>
    </div>
</body>
</html>