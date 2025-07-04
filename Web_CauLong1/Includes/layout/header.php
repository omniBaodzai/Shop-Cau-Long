<!--  include("../Includes/layout/header.php"); -->
        <meta charset="utf-8">  
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Trang chủ VNSPORTS</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="">
        <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <link rel="stylesheet" href="../assets/css/header.css">    

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
