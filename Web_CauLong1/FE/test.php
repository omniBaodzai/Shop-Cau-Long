<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../Includes/db.php';

// Featured từng loại
$featured_vot_sql = "SELECT id, name, image, price FROM products WHERE category = 'Vợt cầu lông' ORDER BY price ASC LIMIT 1";
$featured_vot = mysqli_query($link, $featured_vot_sql)->fetch_assoc();

$featured_tp_sql = "SELECT id, name, image, price FROM products WHERE category = 'Trang phục cầu lông' ORDER BY price ASC LIMIT 1";
$featured_tp = mysqli_query($link, $featured_tp_sql)->fetch_assoc();

$featured_pk_sql = "SELECT id, name, image, price FROM products WHERE category = 'Phụ kiện cầu lông' ORDER BY price ASC LIMIT 1";
$featured_pk = mysqli_query($link, $featured_pk_sql)->fetch_assoc();

$featured_giay_sql = "SELECT id, name, image, price FROM products WHERE category = 'Giày cầu lông' ORDER BY price ASC LIMIT 1";
$featured_giay = mysqli_query($link, $featured_giay_sql)->fetch_assoc();

// Lấy 8 sản phẩm bên phải từng loại
$vot_sql = "SELECT id, name, image, price FROM products WHERE category = 'Vợt cầu lông' ORDER BY id ASC LIMIT 8";
$vot_products = mysqli_query($link, $vot_sql);

$tp_sql = "SELECT id, name, image, price FROM products WHERE category = 'Trang phục cầu lông' ORDER BY id ASC LIMIT 8";
$tp_products = mysqli_query($link, $tp_sql);

$pk_sql = "SELECT id, name, image, price FROM products WHERE category = 'Phụ kiện cầu lông' ORDER BY id ASC LIMIT 8";
$pk_products = mysqli_query($link, $pk_sql);

$giay_sql = "SELECT id, name, image, price FROM products WHERE category = 'Giày cầu lông' ORDER BY id ASC LIMIT 8";
$giay_products = mysqli_query($link, $giay_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trang chủ VNSPORTS</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .Content { margin-top: 20px; }
        .Content .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .Content .section-header ul.tabs { list-style: none; display: flex; gap: 10px; }
        .Content .section-header ul.tabs li a { text-decoration: none; padding: 5px 10px; background: #eee; border-radius: 4px; }
        .Content .section-header ul.tabs li a.active, 
        .Content .section-header ul.tabs li a:hover { background: #e60000; color: #fff; }

        .content-row { display: flex; gap: 20px; }
        .content-left { flex: 1; }
        .content-left img { width: 100%; border-radius: 8px; transition: 0.3s; }
        .content-left img:hover { transform: scale(1.03); }

        .content-right { flex: 2; display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; }
        .product { background: #fff; border: 1px solid #ddd; text-align: center; padding: 10px; border-radius: 6px; transition: 0.3s; }
        .product:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.2); transform: translateY(-5px); }
        .product img { width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 4px; }
        .product h4 { font-size: 14px; margin: 8px 0; }
        .product p { color: #e60000; font-weight: bold; }
        
            #header {
                display: flex;
                position: sticky;
                top: 0;
                z-index: 999;
                align-items: center;
                flex-wrap: wrap; /* Cho phép các phần tử con xuống dòng nếu thiếu chỗ */
                justify-content: space-between;
                padding: 15px 20px;
                background:rgb(255, 255, 255);
                border-bottom: 1px solid #ddd;
                font-family: Arial, sans-serif;
                }
            #header .logo {
                display: flex;
                align-items: center;
                font-weight: bold;
                font-size: 20px;
                text-decoration: none;
                color: #000;
                }
            #header .logo img {
                width: 40px;
                margin-right: 10px;
                }
            #header .container {
                width: 90%;
                max-width: 1200px;
                margin: 0 auto;
                display: flex;                /* thêm dòng này */
                align-items: center;          /* căn giữa theo chiều cao */
                justify-content: space-between; /* căn đều 2 bên */
                gap: 20px;                    /* cách đều các khối */
                }
            /* === Nav === */
            #header .nav-right {
                display: flex;
                justify-content: flex-end; 
                align-items: center;
                gap: 20px;
                }
            #header .nav-right-list {
                display: flex; /* li xếp ngang */
                list-style: none; /* bỏ dấu chấm đầu dòng */
                padding: 0;
                margin: 0;
            }
            #header .nav-right-item {
                position: relative;
            }
            #header .nav-right-item {
                position: relative; /* Đảm bảo dropdown-menu neo đúng vị trí */
            }

            /* Khi hover vào nav-right-item thì dropdown-menu xuất hiện */
            #header .nav-right-item:hover .dropdown-menu {
                display: block;
            }

            #header .nav-right-link {
                display: flex;
                list-style: none;
                align-items: center;
                text-decoration: none; /* bỏ gạch liên kết */
                color: #000;
                font-size: 16px;
                padding: 10px 15px;
                transition: background-color 0.3s ease;
            }
            #header .nav-right-link:hover {
                color: #00c0ff;
            }
            #header .dropdown-menu {
                position: absolute;
                top: 100%; /* Hiển thị dưới item */
                right: 0;
                background-color: #fff;
                border-radius: 5px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                display: none;      /*mặc đinh ẩn menu*/
                min-width: 200px;
                z-index: 1000; /* Ưu tiên hiển thị trên các phần tử khác */
                padding: 10px 0;
                list-style: none; /* bỏ dấu chấm đầu dòng */
            }
            #header .dropdown-menu a {
                display: block;
                padding: 10px 20px;
                color: #333;
                text-decoration: none;
                transition: background 0.2s ease;
            }
            #header .dropdown-menu a:hover {
                background-color: #f1f1f1;
                color: #00c0ff;
            }
            #header .cart-dropdown {
                text-align: center;
                padding: 20px;
            }
            #header .cart-icon {
                font-size: 24px;
                color: #00c0ff;
            }
            #header .cart-count {
                background-color:rgb(179, 207, 238); /* Màu nền cho số lượng sản phẩm */
                color:rgb(196, 64, 24); /* Màu chữ */
                font-size: 12px;
                font-weight: bold;
                border-radius: 50%; /* Bo tròn */
                padding: 2px 6px; /* Khoảng cách bên trong */
                margin-left: 5px; /* Khoảng cách giữa biểu tượng và số lượng */
            }

    </style>
</head>
<body>
<div id="khung">
    <div id="header">
        <div class="container">
            <!-- Logo -->
            <a href="Index.php" class="logo">
                <img src="../assets/Picture/Logo.jpg" alt="Logo Yêu Cầu Lông" />
                YeuCauLong
            </a>

            <!-- Search Bar -->
            <div class="search-box">
                <input type="text" placeholder="Tìm kiếm sản phẩm..." />
                <i class="ri-search-line"></i>
            </div>
                        

            <!-- Navigation Right -->
            <nav class="nav-right">
                <ul class="nav-right-list">
                    <!-- Dropdown - Tài khoản -->
                    <li class="nav-right-item dropdown">
                        <?php
                        
                        if (isset($_SESSION['user_name'])) {
                            // Hiển thị tên người dùng và menu "Trang Cá Nhân" và "Đăng Xuất"
                            echo '<a href="#" class="nav-right-link">';
                            echo '<i class="ri-user-line"></i> ' . htmlspecialchars($_SESSION['user_name']);
                            echo '<i class="ri-arrow-down-s-line dropdown-arrow"></i>';
                            echo '</a>';
                            echo '<ul class="dropdown-menu">';
                            echo '<li><a href="../Custorm/pages/thanh-vien.php" class="dropdown-link">';
                            echo '<i class="ri-user-line"></i> Trang Cá Nhân';
                            echo '</a></li>';
                            echo '<li><a href="../Custorm/pages/dang-xuat.php" class="dropdown-link">';
                            echo '<i class="ri-logout-box-line"></i> Đăng Xuất';
                            echo '</a></li>';
                            echo '</ul>';
                        } else {
                            // Hiển thị menu "Đăng nhập" và "Đăng ký"
                            echo '<a href="../Custorm/pages/dang-ky.php" class="nav-right-link">';
                            echo '<i class="ri-user-line"></i> Tài khoản';
                            echo '<i class="ri-arrow-down-s-line dropdown-arrow"></i>';
                            echo '</a>';
                            echo '<ul class="dropdown-menu">';
                            echo '<li><a href="../Custorm/pages/dang-ky.php" class="dropdown-link">';
                            echo '<i class="ri-user-add-line"></i> Đăng ký';
                            echo '</a></li>';
                            echo '<li><a href="../Custorm/pages/dang-nhap.php" class="dropdown-link">';
                            echo '<i class="ri-login-box-line"></i> Đăng nhập';
                            echo '</a></li>';
                            echo '</ul>';
                        }
                        ?>
                    </li>

                    <!-- Dropdown - Giỏ hàng -->

                    <li class="nav-right-item dropdown">
                        <a href="../Custorm/pages/gio-hang.php" class="nav-right-link">
                            <i class="ri-shopping-cart-line"></i> Giỏ hàng
                            <?php
                            // Kiểm tra giỏ hàng trong session
                            if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
                                echo '<span class="cart-count">' . count($_SESSION['cart']) . '</span>'; // Hiển thị số lượng sản phẩm
                            } else {
                                echo '<span class="cart-count">0</span>'; // Hiển thị 0 nếu không có sản phẩm
                            }
                            ?>
                            <i class="ri-arrow-down-s-line dropdown-arrow"></i>
                        </a>
                        <div class="dropdown-menu cart-dropdown">
                            <i class="ri-shopping-bag-3-line cart-icon"></i>
                            <?php
                            if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
                                echo '<ul class="cart-items">';
                                foreach ($_SESSION['cart'] as $item) {
                                    echo '<li>' . htmlspecialchars($item['product_name']) . ' x ' . $item['quantity'] . '</li>';

                                }
                                echo '</ul>';
                            } else {
                                echo '<p class="empty-cart-msg">Không có sản phẩm trong giỏ hàng</p>';
                            }
                            ?>
                        </div>
                    </li>

                    <!-- Link - Tra cứu đơn hàng -->
                    <li class="nav-right-item dropdown">
                        <a href="#" class="nav-right-link">
                        <i class="ri-file-list-3-line"></i>
                        Tra cứu đơn hàng
                        <i class="ri-arrow-down-s-line dropdown-arrow"></i>
                        </a>
                        <ul class="dropdown-menu order-dropdown">
                        <li>
                            
                            <a href="../Custorm/pages/tra-cuu-don-hang.php" class="dropdown-link">
                            <i class="ri-search-eye-line"></i> Kiểm tra đơn hàng
                            </a>
                        </li>
                        <li>
                            <a href="../Custorm/pages/kiem-tra-bao-hanh.php" class="dropdown-link">
                            <i class="ri-tools-line"></i> Kiểm tra bảo hành
                            </a>
                        </li>
                        </ul>
                    </li>
                    </ul>
                </nav>
        </div>
    </div>

<!-- VỢT -->
<div class="Content">
    <div class="section-header">
        <h3>VỢT CẦU LÔNG</h3>
    </div>
    <div class="content-row">
        <div class="content-left">
            <?php if($featured_vot): ?>
                <a href="product.php?id=<?= $featured_vot['id'] ?>">
                    <img src="<?= htmlspecialchars($featured_vot['image']) ?>" alt="<?= htmlspecialchars($featured_vot['name']) ?>">
                </a>
            <?php else: ?>
                <p>Không có sản phẩm nào.</p>
            <?php endif; ?>
        </div>
        <div class="content-right">
            <?php while($row = mysqli_fetch_assoc($vot_products)): ?>
                <div class="product">
                    <a href="product.php?id=<?= $row['id'] ?>">
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        <h4><?= htmlspecialchars($row['name']) ?></h4>
                        <p><?= number_format($row['price'], 0, ',', '.') ?>₫</p>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- TRANG PHỤC -->
<div class="Content">
    <div class="section-header">
        <h3>TRANG PHỤC CẦU LÔNG</h3>
    </div>
    <div class="content-row">
        <div class="content-left">
            <?php if($featured_tp): ?>
                <a href="product.php?id=<?= $featured_tp['id'] ?>">
                    <img src="<?= htmlspecialchars($featured_tp['image']) ?>" alt="<?= htmlspecialchars($featured_tp['name']) ?>">
                </a>
            <?php else: ?>
                <p>Không có sản phẩm nào.</p>
            <?php endif; ?>
        </div>
        <div class="content-right">
            <?php while($row = mysqli_fetch_assoc($tp_products)): ?>
                <div class="product">
                    <a href="product.php?id=<?= $row['id'] ?>">
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        <h4><?= htmlspecialchars($row['name']) ?></h4>
                        <p><?= number_format($row['price'], 0, ',', '.') ?>₫</p>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- PHỤ KIỆN -->
<div class="Content">
    <div class="section-header">
        <h3>PHỤ KIỆN CẦU LÔNG</h3>
    </div>
    <div class="content-row">
        <div class="content-left">
            <?php if($featured_pk): ?>
                <a href="product.php?id=<?= $featured_pk['id'] ?>">
                    <img src="<?= htmlspecialchars($featured_pk['image']) ?>" alt="<?= htmlspecialchars($featured_pk['name']) ?>">
                </a>
            <?php else: ?>
                <p>Không có sản phẩm nào.</p>
            <?php endif; ?>
        </div>
        <div class="content-right">
            <?php while($row = mysqli_fetch_assoc($pk_products)): ?>
                <div class="product">
                    <a href="product.php?id=<?= $row['id'] ?>">
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        <h4><?= htmlspecialchars($row['name']) ?></h4>
                        <p><?= number_format($row['price'], 0, ',', '.') ?>₫</p>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- GIÀY -->
<div class="Content">
    <div class="section-header">
        <h3>GIÀY CẦU LÔNG</h3>
    </div>
    <div class="content-row">
        <div class="content-left">
            <?php if($featured_giay): ?>
                <a href="product.php?id=<?= $featured_giay['id'] ?>">
                    <img src="<?= htmlspecialchars($featured_giay['image']) ?>" alt="<?= htmlspecialchars($featured_giay['name']) ?>">
                </a>
            <?php else: ?>
                <p>Không có sản phẩm nào.</p>
            <?php endif; ?>
        </div>
        <div class="content-right">
            <?php while($row = mysqli_fetch_assoc($giay_products)): ?>
                <div class="product">
                    <a href="product.php?id=<?= $row['id'] ?>">
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        <h4><?= htmlspecialchars($row['name']) ?></h4>
                        <p><?= number_format($row['price'], 0, ',', '.') ?>₫</p>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php include("../Includes/layout/footer.php"); ?>
</div>
</body>
</html>
