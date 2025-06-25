<!--  include("../Index/header.php"); -->
        <meta charset="utf-8">  
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Trang chủ VNSPORTS</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="">
        <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <link rel="stylesheet" href="../assets/css/style.css">    

    <div id="header">
        <div class="container">
            <!-- Logo -->
            <a href="layout.php" class="logo">
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
                    <!-- Tài khoản -->
                    <li class="nav-right-item dropdown">
                        <a href="#" class="nav-right-link">
                            <i class="ri-user-line"></i> Tài khoản 
                            <i class="ri-arrow-down-s-line dropdown-arrow"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="./pages/dang-ky.html"><i class="ri-user-add-line"></i> Đăng ký</a></li>
                            <li><a href="./pages/dang-nhap.html"><i class="ri-login-box-line"></i> Đăng nhập</a></li>
                        </ul>
                    </li>

                    <!-- Giỏ hàng -->
                    <li class="nav-right-item dropdown">
                        <a href="#" class="nav-right-link">
                            <i class="ri-shopping-cart-line"></i> Giỏ hàng 
                            <i class="ri-arrow-down-s-line dropdown-arrow"></i>
                        </a>
                        <div class="dropdown-menu cart-dropdown">
                            <i class="ri-shopping-bag-3-line cart-icon"></i>
                            <p class="empty-cart-msg">Không có sản phẩm trong giỏ hàng</p>
                        </div>
                    </li>

                    <!-- Tra cứu đơn hàng -->
                    <li class="nav-right-item dropdown">
                        <a href="#" class="nav-right-link">
                            <i class="ri-file-list-3-line"></i> Tra cứu đơn 
                            <i class="ri-arrow-down-s-line dropdown-arrow"></i>
                        </a>
                        <ul class="dropdown-menu order-dropdown">
                            <li><a href="#"><i class="ri-search-eye-line"></i> Kiểm tra đơn</a></li>
                            <li><a href="#"><i class="ri-tools-line"></i> Bảo hành</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
