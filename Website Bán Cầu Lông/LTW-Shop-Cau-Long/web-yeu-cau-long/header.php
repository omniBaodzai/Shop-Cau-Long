<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Chỉ bắt đầu session nếu chưa được bắt đầu
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Cầu Lông</title>
    <!-- Các tệp CSS hoặc JS bổ sung có thể được thêm ở đây nếu cần -->
    <link rel="stylesheet" href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/assets/css/header.css">
    <script src="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/assets/js/script.js"></script>
<header class="header">
      <!-- Logo -->
      <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/index.php" class="logo">
        <img src="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/assets/images/Badminton.png" alt="Logo Yêu Cầu Lông" />
        YeuCauLong
      </a>
      <!-- Navigation Left -->
      <nav class="nav-left">
        <ul class="nav-left-list">
            <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/index.php" class="nav-left-link">Trang chủ</a>
          <li class="nav-left-item dropdown mega-dropdown">
            <a href="#" class="nav-left-link">
              Sản phẩm <i class="ri-arrow-down-s-line dropdown-arrow"></i>
            </a>
            <div class="dropdown-menu mega-menu">
                <div class="mega-menu-column">
                    <div class="mega-menu-title">
                        <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Vợt Cầu Lông">VỢT CẦU LÔNG</a>
                    </div>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Vợt Cầu Lông&brand=VNB" class="mega-menu-link">Vợt cầu lông VNB</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Vợt Cầu Lông&brand=Victor" class="mega-menu-link">Vợt cầu lông Victor</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Vợt Cầu Lông&brand=Lining" class="mega-menu-link">Vợt cầu lông Lining</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Vợt Cầu Lông&brand=Apacs" class="mega-menu-link">Vợt cầu lông Apacs</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Vợt Cầu Lông&brand=Thruster" class="mega-menu-link">Vợt cầu lông Thruster</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Vợt Cầu Lông&brand=Fleet" class="mega-menu-link">Vợt cầu lông Fleet</a>
                </div>
                <div class="mega-menu-column">
                    <div class="mega-menu-title">
                        <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Giày Cầu Lông">GIÀY CẦU LÔNG</a>
                    </div>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Giày Cầu Lông&brand=Victor" class="mega-menu-link">Giày cầu lông Victor</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Giày Cầu Lông&brand=Lining" class="mega-menu-link">Giày cầu lông Lining</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Giày Cầu Lông&brand=Kawasaki" class="mega-menu-link">Giày cầu lông Kawasaki</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Giày Cầu Lông&brand=Taro" class="mega-menu-link">Giày cầu lông Taro</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Giày Cầu Lông&brand=Kumpoo" class="mega-menu-link">Giày cầu lông Kumpoo</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Giày Cầu Lông&brand=Yonex" class="mega-menu-link">Giày cầu lông Yonex</a>
                </div>

                <div class="mega-menu-column">
                    <div class="mega-menu-title">
                        <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Áo Cầu Lông">ÁO CẦU LÔNG</a>
                    </div>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Áo Cầu Lông&brand=Yonex" class="mega-menu-link">Áo cầu lông Yonex</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Áo Cầu Lông&brand=Victor" class="mega-menu-link">Áo cầu lông Victor</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Áo Cầu Lông&brand=Lining" class="mega-menu-link">Áo cầu lông Lining</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Áo Cầu Lông&brand=Kamito" class="mega-menu-link">Áo cầu lông Kamito</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Áo Cầu Lông&brand=DonexPro" class="mega-menu-link">Áo cầu lông DonexPro</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Áo Cầu Lông&brand=Alien Armour" class="mega-menu-link">Áo cầu lông Alien Armour</a>
                </div>

                <div class="mega-menu-column">
                    <div class="mega-menu-title">
                        <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Quần Cầu Lông">QUẦN CẦU LÔNG</a>
                    </div>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Quần Cầu Lông&brand=Kamito" class="mega-menu-link">Quần cầu lông Kamito</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Quần Cầu Lông&brand=Yonex" class="mega-menu-link">Quần cầu lông Yonex</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Quần Cầu Lông&brand=Taro" class="mega-menu-link">Quần cầu lông Taro</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Quần Cầu Lông&brand=SFD" class="mega-menu-link">Quần cầu lông SFD</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Quần Cầu Lông&brand=Donex" class="mega-menu-link">Quần cầu lông Donex</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Quần Cầu Lông&brand=Apacs" class="mega-menu-link">Quần cầu lông Apacs</a>
                </div>

                <div class="mega-menu-column">
                    <div class="mega-menu-title">
                        <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Váy Cầu Lông">VÁY CẦU LÔNG</a>
                    </div>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Váy Cầu Lông&brand=Victec" class="mega-menu-link">Váy cầu lông Victec</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Váy Cầu Lông&brand=Kamito" class="mega-menu-link">Váy cầu lông Kamito</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Váy Cầu Lông&brand=Taro" class="mega-menu-link">Váy cầu lông Taro</a>                    
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Váy Cầu Lông&brand=VNB" class="mega-menu-link">Váy cầu lông VNB</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Váy Cầu Lông&brand=Donex" class="mega-menu-link">Váy cầu lông Donex</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Váy Cầu Lông&brand=Victor" class="mega-menu-link">Váy cầu lông Victor</a>
                </div>

                <div class="mega-menu-column">
                    <div class="mega-menu-title">
                        <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Ống Cầu Lông">ỐNG CẦU LÔNG</a>
                    </div>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Ống Cầu Lông&brand=VBCS" class="mega-menu-link">Ống cầu lông VBCS</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Ống Cầu Lông&brand=Taro" class="mega-menu-link">Ống cầu lông Taro</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Ống Cầu Lông&brand=Yonex" class="mega-menu-link">Ống cầu lông Yonex</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Ống Cầu Lông&brand=Lining" class="mega-menu-link">Ống cầu lông Lining</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Ống Cầu Lông&brand=Victor" class="mega-menu-link">Ống cầu lông Victor</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Ống Cầu Lông&brand=Apacs" class="mega-menu-link">Ống cầu lông Apacs</a>

                </div>

                <div class="mega-menu-column">
                    <div class="mega-menu-title">
                        <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Phụ Kiện Cầu Lông">PHỤ KIỆN CẦU LÔNG</a>
                    </div>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Phụ Kiện Cầu Lông&brand=Kawasaki" class="mega-menu-link">Phụ kiện cầu lông Kawasaki</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Phụ Kiện Cầu Lông&brand=Taro" class="mega-menu-link">Phụ kiện cầu lông Taro</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Phụ Kiện Cầu Lông&brand=Victor" class="mega-menu-link">Phụ kiện cầu lông Victor</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Phụ Kiện Cầu Lông&brand=Yonex" class="mega-menu-link">Phụ kiện cầu lông Yonex</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Phụ Kiện Cầu Lông&brand=Lining" class="mega-menu-link">Phụ kiện cầu lông Lining</a>
                    <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/products.php?category=Phụ Kiện Cầu Lông&brand=Kamito" class="mega-menu-link">Phụ kiện cầu lông Kamito</a>
                </div>
            </div>
            </div>
          </li>
          <li><a href="https://thethao247.vn/cau-long-c44/" class="nav-left-link">Tin tức</a></li>
        </ul>
      </nav>

      <!-- Search Bar -->
      <div class="search-box">
          <form action="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/tim-kiem.php" method="GET" autocomplete="off">
              <input type="text" name="q" id="main-search-input" placeholder="Tìm kiếm sản phẩm..." required />
              <button type="submit"><i class="ri-search-line"></i></button>
          </form>
          <div id="search-suggestions" class="search-suggestions"></div>
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
                echo '<li><a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/thanh-vien.php" class="dropdown-link">';
                echo '<i class="ri-user-line"></i> Trang Cá Nhân';
                echo '</a></li>';
                echo '<li><a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/dang-xuat.php" class="dropdown-link">';
                echo '<i class="ri-logout-box-line"></i> Đăng Xuất';
                echo '</a></li>';
                echo '</ul>';
            } else {
                // Hiển thị menu "Đăng nhập" và "Đăng ký"
                echo '<a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/dang-ky.php" class="nav-right-link">';
                echo '<i class="ri-user-line"></i> Tài khoản';
                echo '<i class="ri-arrow-down-s-line dropdown-arrow"></i>';
                echo '</a>';
                echo '<ul class="dropdown-menu">';
                echo '<li><a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/dang-ky.php" class="dropdown-link">';
                echo '<i class="ri-user-add-line"></i> Đăng ký';
                echo '</a></li>';
                echo '<li><a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/dang-nhap.php" class="dropdown-link">';
                echo '<i class="ri-login-box-line"></i> Đăng nhập';
                echo '</a></li>';
                echo '</ul>';
            }
            ?>
        </li>

          <!-- Dropdown - Giỏ hàng -->

          <li class="nav-right-item dropdown">
              <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/gio-hang.php" class="nav-right-link">
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
                
                <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/tra-cuu-don-hang.php" class="dropdown-link">
                  <i class="ri-search-eye-line"></i> Kiểm tra đơn hàng
                </a>
              </li>
              <li>
                <a href="http://localhost/LTW-Shop-Cau-Long/web-yeu-cau-long/pages/kiem-tra-bao-hanh.php" class="dropdown-link">
                  <i class="ri-tools-line"></i> Kiểm tra bảo hành
                </a>
              </li>
            </ul>
          </li>
        </ul>
      </nav>
    </header>
</body>
</html>   
