<footer>
        <div class="container">
            <p>&copy; 2025 IRONBOOTS. All rights reserved.</p>
        </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const cartToggleBtn = document.getElementById('cart-toggle-btn');
    const cartCloseBtn = document.getElementById('cart-close-btn');
    const sideCart = document.getElementById('side-cart');
    const cartOverlay = document.getElementById('cart-overlay');
    const cartBody = document.getElementById('side-cart-body');
    const cartCountEl = document.getElementById('cart-count');
    const cartTotalPriceEl = document.getElementById('cart-total-price');
    const addToCartForm = document.getElementById('add-to-cart-form');
    
    // ===== 1. ฟังก์ชัน: เปิด/ปิด ตะกร้า =====
    function toggleCart(show = true) {
        if (show) {
            sideCart.classList.add('active');
            cartOverlay.classList.add('active');
        } else {
            sideCart.classList.remove('active');
            cartOverlay.classList.remove('active');
        }
    }

    // ===== 2. ฟังก์ชัน: ดึงข้อมูลตะกร้า (AJAX Get) =====
    async function fetchCart() {
        try {
            const response = await fetch('cart_action.php?action=get');
            const data = await response.json();
            // อัปเดตเนื้อหาตะกร้า
            cartBody.innerHTML = data.html;
            // อัปเดตจำนวนใน Header
            cartCountEl.innerText = data.count;
            // อัปเดตยอดรวม
            cartTotalPriceEl.innerText = data.total_price + ' ฿';
            
        } catch (error) {
            console.error('Error fetching cart:', error);
            cartBody.innerHTML = "<p>Error loading cart.</p>";
        }
    }

    // ===== 3. ฟังก์ชัน: อัปเดตจำนวน/ลบ (AJAX Update) =====
    async function updateCartItem(cartKey, quantity) { // 1. เปลี่ยนเป็น cartKey
        const formData = new FormData();
        formData.append('cart_key', cartKey); // 2. ส่งเป็น cart_key
        formData.append('quantity', quantity);

        try {
            await fetch('cart_action.php?action=update', {
                method: 'POST',
                body: formData
            });
            await fetchCart(); 
        } catch (error) {
            console.error('Error updating cart:', error);
        }
    }

    // ===== 4. การทำงานเมื่อคลิก (Event Listeners) =====
    
    // 4.1 คลิกปุ่มตะกร้า (ใน Header)
    cartToggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        toggleCart(true); // เปิดตะกร้า
    });

    // 4.2 คลิกปุ่มปิด 'X' หรือ พื้นหลังมืด
    cartCloseBtn.addEventListener('click', () => toggleCart(false));
    cartOverlay.addEventListener('click', () => toggleCart(false));

    // 4.3 (สำคัญ) เมื่อกด "เพิ่มลงตะกร้า" (ใน product_detail.php)
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', async function(e) {
            e.preventDefault(); // หยุดการ submit ปกติ
            
            const formData = new FormData(addToCartForm);
            const feedbackEl = document.getElementById('add-to-cart-feedback');

            try {
                const response = await fetch('cart_action.php?action=add', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success') {
                    feedbackEl.innerText = 'เพิ่มแล้ว!';
                    // ดึงข้อมูลตะกร้าใหม่
                    await fetchCart();
                    // เปิดตะกร้าให้ดู
                    toggleCart(true); 
                    
                    // หน่วงเวลาแล้วลบข้อความ feedback
                    setTimeout(() => { feedbackEl.innerText = ''; }, 2000);
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                feedbackEl.innerText = 'Error!';
            }
        });
    }

    // 4.4 (สำคัญ) เมื่อแก้ไขจำนวน หรือ ลบของ "ใน" ตะกร้า
    cartBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('cart-remove-btn')) {
            const cartKey = e.target.dataset.key; // 3. เปลี่ยนจาก .dataset.id เป็น .dataset.key
            updateCartItem(cartKey, 0); // ส่ง 0 เพื่อลบ
        }
    });

    cartBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('cart-qty-input')) {
            const cartKey = e.target.dataset.key; // 4. เปลี่ยนจาก .dataset.id เป็น .dataset.key
            const quantity = e.target.value;
            updateCartItem(cartKey, quantity);
        }
    });

    // ===== 5. ทำงานครั้งแรกเมื่อโหลดหน้า =====
    // ดึงข้อมูลตะกร้า (เพื่อแสดงจำนวนใน Header)
    fetchCart();

    const allWishlistButtons = document.querySelectorAll('.wishlist-btn');
    const favCountEl = document.getElementById('fav-count'); // ตัวเลขใน Navbar

    // 1. 🟢 (ใหม่) สร้างฟังก์ชัน "ดึงยอด" (แบบเดียวกับ fetchCart)
    //    (ฟังก์ชันนี้จะเรียก API action=get_count)
    async function fetchFavoriteCount() {
        if (!favCountEl) return; // (ถ้าหน้าไม่มีตัวนับ ก็ไม่ต้องทำ)

        try {
            // 1.1 ยิงไปที่ API (action=get_count)
            const response = await fetch('favorite_action.php?action=get_count');
            const data = await response.json();
            
            if (data.status === 'success') {
                // 1.2 อัปเดตตัวเลขใน Navbar
                favCountEl.innerText = data.count;
            }
        } catch (error) {
            console.error('Error fetching favorite count:', error);
        }
    }

    // 2. 🟢 (อัปเกรด) แก้ไข Click Handler (ปุ่ม ♡)
    allWishlistButtons.forEach(button => {
        button.addEventListener('click', async function() {
            
            const productId = this.dataset.productId;
            const formData = new FormData();
            formData.append('product_id', productId);
            // (เรายิงไปที่ action=toggle (default))

            try {
                // 2.1 ยิง API (action=toggle)
                const response = await fetch('favorite_action.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.status === 'success') {
                    
                    // 3. 🟢 (เปลี่ยน) เมื่อสำเร็จ -> ให้เรียกฟังก์ชัน "ดึงยอด"
                    await fetchFavoriteCount(); // 👈 นี่คือการอัปเดตตัวเลข
                    
                    // 4. (เหมือนเดิม) เปลี่ยนสีปุ่ม
                    if (data.action === 'added') {
                        this.classList.add('active');
                        this.innerHTML = '♥';
                    } else if (data.action === 'removed') {
                        this.classList.remove('active');
                        this.innerHTML = '♡';
                        if (document.body.contains(document.querySelector('.favorite-page-title'))) {
                            this.closest('.product-card').style.display = 'none';
                        }
                    }
                    
                } else if (data.message === 'login_required') {
                    alert('กรุณา Login เพื่อใช้งานระบบ Favorite ครับ');
                } else {
                    alert('เกิดข้อผิดพลาด: ' + data.message);
                }

            } catch (error) {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            }
        });
    });

    // 5. 🟢 (ใหม่) เรียก "ดึงยอด" 1 ครั้งตอนโหลดหน้า
    // (เพื่อให้ตัวเลขถูกต้องตั้งแต่แรก)
    fetchFavoriteCount();
    
});
</script>

</body>
</html>