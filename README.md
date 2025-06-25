# LTW-Shop-Cau-Long

CẤU TRÚC THƯ MỤC (CN/01/06/2025)
badminton_shop/
│
├── index.html                         # Trang chính giới thiệu website (giao diện tĩnh)
│
├── customer/                          # Trang dành cho người dùng/khách hàng
│   ├── products.php                   # Danh sách sản phẩm
│   ├── cart.php                       # Giỏ hàng của khách
│   ├── checkout.php                   # Trang thanh toán
│   ├── add_to_cart.php               # Xử lý thêm vào giỏ hàng
│   ├── remove_from_cart.php          # Xử lý xoá khỏi giỏ hàng
│   └── order_success.php             # Thông báo đặt hàng thành công (tùy chọn)
│
├── admin/                             # Khu vực quản trị (admin)
│   ├── admin_products.php             # Quản lý sản phẩm
│   ├── add_product.php                # Thêm sản phẩm
│   ├── edit_product.php               # Sửa sản phẩm
│   ├── delete_product.php             # Xoá sản phẩm
│   ├── login.php                      # Đăng nhập admin (tùy chọn)
│   └── dashboard.php                  # Trang tổng quan admin (tùy chọn)
│
├── includes/                          # Thư mục chứa các phần dùng chung
│   ├── db/
│   │   └── db_connect.php             # Kết nối CSDL (MySQL)
│   │
│   ├── functions/
│   │   └── functions.php              # Các hàm xử lý chung (giỏ hàng, lấy sản phẩm, ...)
│   │
│   ├── layout/
│   │   ├── header.php                 # Header chung
│   │   ├── nav.php                    # Menu điều hướng
│   │   └── footer.php                 # Footer chung
│
├── assets/                            # Tài nguyên giao diện
│   ├── css/
│   │   └── style.css                  # CSS chung cho toàn bộ trang
│   │
│   ├── js/
│   │   └── script.js                  # JavaScript xử lý tương tác (thêm giỏ, xoá, hiệu ứng...)
│   │
│   └── images/                        # Ảnh tĩnh của giao diện
│       ├── logo.png
│       ├── banners/
│       │   └── banner1.jpg
│       ├── products/
│       │   ├── racket1.jpg
│       │   └── shuttlecock1.jpg
│       └── icons/
│           └── cart_icon.png
│
├── uploads/                           # Ảnh sản phẩm do admin upload (được gọi từ DB)
│   ├── racket_new.jpg
│   └── shoes_new.jpg
│
├── .htaccess                          # (Khuyến nghị) Giới hạn upload, chặn truy cập mã độc
├── README.md                          # Mô tả dự án, hướng dẫn cài đặt & chạy
└── LICENSE                            # Giấy phép sử dụng mã nguồn (tuỳ chọn)
