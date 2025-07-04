<?php
include '../Includes/db.php';

// Featured từng loại
$featured_vot_sql = "SELECT id, name, image, price FROM products WHERE category = 'Vợt cầu lông' ORDER BY RAND() LIMIT 1";
$featured_vot = mysqli_query($link, $featured_vot_sql)->fetch_assoc();

$featured_tui_sql = "SELECT id, name, image, price FROM products WHERE category = 'Túi cầu lông' ORDER BY RAND() LIMIT 1";
$featured_tui = mysqli_query($link, $featured_tui_sql)->fetch_assoc();

$featured_giay_sql = "SELECT id, name, image, price FROM products WHERE category = 'Giày cầu lông' ORDER BY RAND() LIMIT 1";
$featured_giay = mysqli_query($link, $featured_giay_sql)->fetch_assoc();

// Lấy 8 sản phẩm bên phải từng loại
$vot_sql = "SELECT id, name, image, price FROM products WHERE category = 'Vợt cầu lông' ORDER BY id ASC LIMIT 8";
$vot_products = mysqli_query($link, $vot_sql);

$tui_sql = "SELECT id, name, image, price FROM products WHERE category = 'Túi cầu lông' ORDER BY id ASC LIMIT 8";
$tui_products = mysqli_query($link, $tui_sql);

$giay_sql = "SELECT id, name, image, price FROM products WHERE category = 'Giày cầu lông' ORDER BY id ASC LIMIT 8";
$giay_products = mysqli_query($link, $giay_sql);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Trang chủ VNSPORTS</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="">
        <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <link rel="stylesheet" href="../assets/css/style.css">

    </head>
    <body>
        <div id="khung">
            <?php include("../Includes/layout/header.php"); ?>

            <div id="Slide">
                <!-- Slide Carousel (tối giản) -->
                    <div class="banner-carousel">
                    <div class="slide">
                        <a href="#" target="_blank">
                        <img src="../assets/Picture/slide1.jpg" alt="Vợt cầu lông">
                        </a>
                    </div>
                    <div class="slide">
                        <a href="#" target="_blank">
                        <img src="../assets/Picture/slide2.jpg" alt="Qủa cầu lông">
                        </a>
                    </div>
                    <div class="slide">
                        <a href="#" target="_blank">
                        <img src="../assets/Picture/slide4.jpg" alt="Túi cầu lông">
                        </a>
                    </div>
                    <div class="slide">
                        <a href="#" target="_blank">
                        <img src="../assets/Picture/slide3.jpg" alt="Giày cầu lông">
                        </a>
                    </div>
                    </div>
            </div>
            <div class="Banner">
                 <section class="main-features">
                    <div class="main-features-container">
                    <div class="feature-box">
                        <div class="feature-icon"><i class="ri-truck-line"></i></div>
                        <div class="feature-title">Vận chuyển <span>TOÀN QUỐC</span></div>
                        <div class="feature-desc">Thanh toán khi nhận hàng</div>
                    </div>
                    <div class="feature-box">
                        <div class="feature-icon"><i class="ri-shield-check-line"></i></div>
                        <div class="feature-title">Bảo đảm <span>CHẤT LƯỢNG</span></div>
                        <div class="feature-desc">Sản phẩm bảo đảm chất lượng.</div>
                    </div>
                    <div class="feature-box">
                        <div class="feature-icon"><i class="ri-bank-card-line"></i></div>
                        <div class="feature-title">Tiến hành <span>THANH TOÁN</span></div>
                        <div class="feature-desc">Với nhiều <span>PHƯƠNG THỨC</span></div>
                    </div>
                    <div class="feature-box">
                        <div class="feature-icon"><i class="ri-refresh-line"></i></div>
                        <div class="feature-title">Đổi sản phẩm <span>MỚI</span></div>
                        <div class="feature-desc">nếu sản phẩm lỗi</div>
                    </div>
                    </div>
                </section>

            </div>
    <!-- __________________________Content VỢT CẦU LÔNG _______________________ -->        
            <div class="Banner">
                <div class="banner-ads" style="text-align: center;">
                <a href="#">
                    <img src="../assets/Picture/slide1.jpg" alt="Quảng cáo vợt cầu lông" style="max-width: 100%; height: auto; border-radius: 5px;">
                </a>
                </div>
            </div>

            <div class="Content">
                <div class="section-header">
                    <h3>VỢT CẦU LÔNG</h3>
                    <ul class="tabs">
                        <li><a href="#">Mới nhất</a></li>
                        <li><a href="#" class="active">Bán chạy</a></li>
                        <li><a href="#">Xem tất cả</a></li>
                    </ul>
                </div>

            <div class="content-row">
                    <div class="left">
                        <?php if($featured_vot): ?>
                            <a href="product.php?id=<?= $featured_vot['id'] ?>">
                                <img src="<?= htmlspecialchars($featured_vot['image']) ?>" alt="<?= htmlspecialchars($featured_vot['name']) ?>">
                            </a>
                        <?php else: ?>
                            <p>Không có sản phẩm nào.</p>
                        <?php endif; ?>
                    </div>
                    <div class="right">
                        <?php if(mysqli_num_rows($vot_products) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($vot_products)): ?>
                            <div class="product">
                                <a href="product.php?id=<?= $row['id'] ?>">
                                    <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                                    <h4><?= htmlspecialchars($row['name']) ?></h4>
                                    <p><?= number_format($row['price'], 0, ',', '.') ?>₫</p>
                                </a>
                            </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                            <p>Không có sản phẩm nào.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

    <!-- __________________________Content TÚI cầu lông _______________________ -->        
            <div class="Banner">
                <div class="banner-ads" style="text-align: center;">
                <a href="#">
                    <img src="../assets/Picture/slide4.jpg" alt="Quảng cáo vợt cầu lông" style="max-width: 100%; height: auto; border-radius: 5px;">
                </a>
                </div>
            </div>

            <div class="Content">
                <div class="section-header">
                    <h3>TÚI CẦU LÔNG</h3>
                    <ul class="tabs">
                        <li><a href="#">Mới nhất</a></li>
                        <li><a href="#" class="active">Bán chạy</a></li>
                        <li><a href="#">Xem tất cả</a></li>
                    </ul>
                </div>
                <div class="content-row">
                    <div class="left">
                        <?php if($featured_tui): ?>
                            <a href="product.php?id=<?= $featured_tui['id'] ?>">
                                <img src="<?= htmlspecialchars($featured_tui['image']) ?>" alt="<?= htmlspecialchars($featured_tui['name']) ?>">
                            </a>
                        <?php else: ?>
                            <p>Không có sản phẩm nào.</p>
                        <?php endif; ?>
                    </div>
                    <div class="right">
                        <?php if(mysqli_num_rows($tui_products) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($tui_products)): ?>
                            <div class="product">
                                <a href="product.php?id=<?= $row['id'] ?>">
                                    <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                                    <h4><?= htmlspecialchars($row['name']) ?></h4>
                                    <p><?= number_format($row['price'], 0, ',', '.') ?>₫</p>
                                </a>
                            </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                            <p>Không có sản phẩm nào.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
    <!-- __________________________Content giày cầu lông _______________________ -->        
            <div class="Banner">
                <div class="banner-ads" style="text-align: center;">
                <a href="#" target="_blank">
                    <img src="../assets/Picture/slide3.jpg" alt="Quảng cáo vợt cầu lông" style="max-width: 100%; height: auto; border-radius: 5px;">
                </a>
                </div>
            </div>
            <div class="Content">
                <div class="section-header">
                    <h3>GIÀY CẦU LÔNG</h3>
                    <ul class="tabs">
                        <li><a href="#">Mới nhất</a></li>
                        <li><a href="#" class="active">Bán chạy</a></li>
                        <li><a href="#">Xem tất cả</a></li>
                    </ul>
                </div>

                <div class="content-row">
                    <div class="left">
                        <?php if($featured_giay): ?>
                            <a href="product.php?id=<?= $featured_giay['id'] ?>">
                                <img src="<?= htmlspecialchars($featured_giay['image']) ?>" alt="<?= htmlspecialchars($featured_giay['name']) ?>">
                            </a>
                        <?php else: ?>
                            <p>Không có sản phẩm nào.</p>
                        <?php endif; ?>
                    </div>
                    <div class="right">
                        <?php if(mysqli_num_rows($giay_products) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($giay_products)): ?>
                            <div class="product">
                                <a href="product.php?id=<?= $row['id'] ?>">
                                    <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                                    <h4><?= htmlspecialchars($row['name']) ?></h4>
                                    <p><?= number_format($row['price'], 0, ',', '.') ?>₫</p>
                                </a>
                            </div>
                        <?php endwhile; ?>
                        <?php else: ?>
                            <p>Không có sản phẩm nào.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
    <!-- __________________________Footer _______________________ -->        

            <?php include("../Includes/layout/footer.php"); ?>

        
        <script>
            document.addEventListener("DOMContentLoaded", function () {
            const dropdownItems = document.querySelectorAll("#header .nav-right-item");

            dropdownItems.forEach((item) => {
                const menu = item.querySelector(".dropdown-menu");

                item.addEventListener("mouseenter", function () {
                // Hiển thị menu khi rê chuột vào
                if (menu) {
                    menu.style.display = "block";
                }
                });

                item.addEventListener("mouseleave", function () {
                // Ẩn menu khi rê chuột ra
                if (menu) {
                    menu.style.display = "none";
                }
                });
            });
            });
            document.addEventListener("DOMContentLoaded", function () {
            const searchBox = document.querySelector(".search-box");
            const input = searchBox.querySelector("input");
            const icon = searchBox.querySelector("i");

            icon.addEventListener("click", function () {
                // Chỉ xử lý ở mobile
                if (window.innerWidth <= 768) {
                if (input.style.width === "0px" || input.style.opacity === "0") {
                    input.style.width = "150px";
                    input.style.padding = "8px";
                    input.style.opacity = "1";
                    input.style.pointerEvents = "auto";
                    searchBox.style.width = "200px";
                } else {
                    input.style.width = "0";
                    input.style.padding = "0";
                    input.style.opacity = "0";
                    input.style.pointerEvents = "none";
                    searchBox.style.width = "40px";
                }
                }
            });
            });

            // Banner Carousel
            let currentSlide = 0;
            const slides = document.querySelectorAll('.banner-carousel .slide');

            function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
            }

            function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
            }

            showSlide(currentSlide);
            setInterval(nextSlide, 3000); // Chuyển slide mỗi 3 giây
    </script>


    </body>
</html>