<?php
    session_start(); // เริ่ม Session ที่นี่ ที่เดียว
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IRONBOOTS - ร้านขายรองเท้าสตั๊ด</title>
    
    <link rel="stylesheet" href="style.css">
    
    </head>
<body>
    <div id="cart-overlay" class="cart-overlay"></div>

    <div id="side-cart" class="side-cart">
        <div class="cart-header">
            <h3>YOUR CART</h3>
            <button id="cart-close-btn" class="cart-close-btn">&times;</button>
        </div>
        <div id="side-cart-body" class="cart-body">
            </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Subtotal:</span>
                <span id="cart-total-price">0.00 ฿</span>
            </div>
            <a href="checkout.php" class="btn-checkout">Go to Checkout</a>
        </div>
    </div>

    <header>
        <div class="top-bar">
            <div class="container">
                <a href="index.php" class="logo">IRONBOOTS</a>
                <div class="auth-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span style="color: #ecf0f1; font-size: 12px; font-weight: bold; align-self: center;">
                            สวัสดี, <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </span>
                        
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <a href="dashboard.php">ADMIN</a> 
                        <?php endif; ?>
                        
                        <a href="order_history.php">ประวัติสั่งซื้อ</a> 
                        
                        <a href="logout.php">LOGOUT</a>
                        
                    <?php else: ?>
                        <a href="register.php">REGISTER</a>
                        <a href="login.php">LOGIN</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <nav class="main-nav">
            <div class="container">
                <ul>
                    <li><a href="index.php#new-arrivals">NEW ARRIVALS</a></li>
                    <li><a href="index.php#speed">SPEED</a></li>
                    <li><a href="index.php#control">CONTROL</a></li>
                    <li><a href="index.php#touch">TOUCH</a></li>
                    <li><a href="index.php#best-sellers">BEST SELLERS</a></li>
                </ul>
                <div class="nav-icons">
                    <a href="favorites.php">
                        ♡ (<span id="fav-count">0</span>)
                    </a>
                    <a href="#" id="cart-toggle-btn" class="nav-cart-btn">
                        🛒 (<span id="cart-count">0</span>)
                    </a>
                </div>
            </div>
        </nav>

        <div class="container">
            <div class="nav-separator"></div>
        </div>
    </header>