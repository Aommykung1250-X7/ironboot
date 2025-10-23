<?php
require_once 'connectdb.php'; // 1. เชื่อมต่อฐานข้อมูล
session_start(); // เริ่ม session

$error_message = '';
$success_message = '';

// 2. ตรวจสอบว่ามีการ POST ข้อมูลมาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. รับข้อมูลจากฟอร์ม
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password']; // รับรหัสผ่านแบบดิบๆ
    $role = 'user'; // สมัครใหม่ ให้เป็น 'user' เสมอ

    // 4. ตรวจสอบว่า username หรือ email ซ้ำหรือไม่
    $check_sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $error_message = "Username หรือ Email นี้ถูกใช้งานแล้ว";
    } else {
        // 5. ถ้าไม่ซ้ำ ให้ INSERT ข้อมูล (แบบไม่ hash)
        $insert_sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_sql);
        // "ssss" = string, string, string, string
        $stmt_insert->bind_param("ssss", $username, $email, $password, $role);

        if ($stmt_insert->execute()) {
            $success_message = "สมัครสมาชิกสำเร็จ! <a href='login.php'>ไปหน้า Login</a>";
        } else {
            $error_message = "เกิดข้อผิดพลาด: " . $stmt_insert->error;
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - IRONBOOTS</title>
    <link rel="stylesheet" href="auth_style.css"> </head>
<body>
    <div class="auth-container">
        <h2>REGISTER</h2>
        <p>สมัครสมาชิก IRONBOOTS</p>

        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="auth-button">Register</button>
        </form>
        <div class="switch-link">
            มีบัญชีแล้ว? <a href="login.php">Login ที่นี่</a>
        </div>
    </div>
</body>
</html>