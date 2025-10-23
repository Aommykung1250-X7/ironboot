<?php
$servername = "localhost"; // หรือ IP ของเซิร์ฟเวอร์ DB ของคุณ
$username = "root";       // Username ของ DB
$password = "";           // Password ของ DB
$dbname = "ironboot";     // ชื่อ Database ที่คุณบอก

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตั้งค่า charset เป็น utf8 เพื่อรองรับภาษาไทย
$conn->set_charset("utf8");
?>

