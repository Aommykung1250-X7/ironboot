<?php
session_start(); // เริ่ม session

// ลบค่า session ทั้งหมด
session_unset();

// ทำลาย session
session_destroy();

// ส่งกลับไปหน้า login
header("Location: index.php");
exit;
?>