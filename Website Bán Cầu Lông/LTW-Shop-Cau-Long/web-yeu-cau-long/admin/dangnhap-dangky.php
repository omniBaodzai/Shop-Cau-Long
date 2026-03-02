<?php
session_start();

include '../connect.php'; // Đảm bảo đường dẫn này đúng với file kết nối CSDL của bạn

// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['admin_logged_in']) && !$showRegister) {
    header('Location: admin.php');
    exit();
}
$error = '';
$success = '';
$showRegister = isset($_GET['register']);

// Handle registration
if ($showRegister && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $response = ["success" => false, "message" => ""];
    
    // Kiểm tra các trường bắt buộc
    if ($username === '' || $password === '' || $confirm === '' || $email === '' || $phone === '') {
        $response['message'] = 'Vui lòng nhập đầy đủ thông tin!';
    } elseif ($password !== $confirm) {
        $response['message'] = 'Mật khẩu xác nhận không khớp!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Địa chỉ email không hợp lệ!';
    } elseif (!preg_match('/^\+?[0-9]{9,15}$/', $phone)) {
        $response['message'] = 'Số điện thoại không hợp lệ! Vui lòng nhập số từ 9-15 chữ số.';
    } else {
        // Kiểm tra tính duy nhất của username, email, và phone
        $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ? OR email = ? OR phone = ?");
        $stmt->bind_param("sss", $username, $email, $phone);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $response['message'] = 'Tên đăng nhập, email hoặc số điện thoại đã tồn tại!';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("INSERT INTO admin (username, password, email, phone) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("ssss", $username, $hash, $email, $phone);
            if ($stmt2->execute()) {
                $response['success'] = true;
                $response['message'] = 'Đăng ký thành công! Bạn có thể đăng nhập.';
            } else {
                $response['message'] = 'Lỗi khi đăng ký, vui lòng thử lại!';
            }
            $stmt2->close();
        }
        $stmt->close();
    }
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    if ($response['success']) {
        header('Location: dangnhap-dangky.php?registered=1');
        exit;
    } else {
        $error = $response['message'];
    }
}

// Handle login
if (!$showRegister && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $remember = isset($_POST['remember']) ? true : false;
    $response = ["success" => false, "message" => ""];
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($username === '' || $password === '') {
        $response['message'] = 'Vui lòng nhập đầy đủ thông tin!';
    } else {
        $stmt = $conn->prepare("SELECT password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;

                // Xử lý cookie cho "Ghi nhớ đăng nhập"
                if ($remember) {
                    // Lưu tên đăng nhập vào cookie, tồn tại 30 ngày, với HttpOnly, bỏ Secure khi chạy localhost
                    setcookie('remember_admin_user', $username, time() + (30 * 24 * 60 * 60), "/", "", false, true);
                } else {
                    // Xóa cookie nếu không tích "Ghi nhớ đăng nhập"
                    if (isset($_COOKIE['remember_admin_user'])) {
                        setcookie('remember_admin_user', '', time() - 3600, "/", "", false, true);
                    }
                }

                $response['success'] = true;
                $response['message'] = 'Đăng nhập thành công!';
                $response['redirect'] = 'admin.php';
            } else {
                $response['message'] = 'Tên đăng nhập hoặc mật khẩu không đúng!';
            }
        } else {
            $response['message'] = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        }
        $stmt->close();
    }

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($response['success']) {
        header('Location: admin.php');
        exit;
    } else {
        $error = $response['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        if ($showRegister) echo 'Đăng ký Admin - Cầu Lông ProShop';
        else echo 'Đăng nhập Admin - Cầu Lông ProShop';
        ?>
    </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background: #1a2b5b;
        /* Thay overflow: hidden bằng overflow-y: auto để hiển thị thanh cuộn dọc khi nội dung vượt quá màn hình */
        overflow-y: auto;
        position: relative;
    }

    /* Tùy chỉnh thanh cuộn cho body */
    body::-webkit-scrollbar {
        width: 12px;
    }

    body::-webkit-scrollbar-track {
        background: #1a2b5b;
    }

    body::-webkit-scrollbar-thumb {
        background: linear-gradient(45deg, #f7a400, #ffcc00);
        border-radius: 6px;
        border: 3px solid #1a2b5b;
    }

    body::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(45deg, #cc8800, #ffcc00);
    }

    #particles {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }

    .container {
        background: rgba(255, 255, 255, 0.95);
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        width: 100%;
        max-width: 480px;
        position: relative;
        transform: perspective(1000px) rotateY(0deg);
        transition: transform 0.5s;
        animation: cardAppear 1s ease-out;
        margin: 32px 0;
        /* Xóa max-height và overflow-y để không có thanh cuộn bên trong container */
    }

    @keyframes cardAppear {
        from { opacity: 0; transform: perspective(1000px) rotateY(30deg) translateY(50px); }
        to { opacity: 1; transform: perspective(1000px) rotateY(0deg) translateY(0); }
    }

    .container:hover {
        transform: perspective(1000px) rotateY(0deg);
    }

    .container h2 {
        text-align: center;
        color: #1a2b5b;
        margin-bottom: 30px;
        font-size: 28px;
        font-weight: 700;
        letter-spacing: 1px;
    }

    .logo {
        text-align: center;
        margin-bottom: 20px;
    }

    .logo img {
        height: 90px;
        width: auto;
        animation: bounceIn 0.8s ease-out;
    }

    @keyframes bounceIn {
        0% { transform: scale(0); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }

    .input-group {
        position: relative;
        margin-bottom: 25px;
    }

    .input-group input {
        width: 100%;
        padding: 12px 15px 12px 45px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        font-size: 16px;
        outline: none;
        background: #f9f9f9;
        transition: all 0.3s ease;
    }

    .input-group input:focus {
        border-color: #f7a400;
        box-shadow: 0 0 12px rgba(247, 164, 0, 0.3);
        background: #fff;
    }

    .input-group label {
        position: absolute;
        top: 50%;
        left: 45px;
        transform: translateY(-50%);
        color: #999;
        font-size: 16px;
        pointer-events: none;
        transition: all 0.3s ease;
    }

    .input-group input:focus + label,
    .input-group input:not(:placeholder-shown) + label {
        top: -10px;
        left: 10px;
        font-size: 12px;
        color: #f7a400;
        background: #fff;
        padding: 0 5px;
    }

    .input-group .input-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #a3a3a3;
        font-size: 1.1rem;
    }

    .btn {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(45deg, #f7a400, #ffcc00);
        color: #fff;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
        margin-top: 8px;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(247, 164, 0, 0.4);
    }

    .btn:active {
        transform: translateY(0);
    }

    .btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s;
    }

    .btn:hover::before {
        left: 100%;
    }

    .error-message, .success-message {
        display: block;
        border-radius: 8px;
        margin: 0 0 18px 0;
        padding: 12px 18px;
        font-size: 15px;
        font-weight: 600;
        box-shadow: 0 4px 16px #0001;
        text-align: center;
        position: relative;
        opacity: 0;
        transform: translateY(10px);
        animation: fadeInMsg 0.5s forwards;
    }

    .error-message {
        background: #fdecea;
        color: #e74c3c;
        border: 1.5px solid #f5c6cb;
    }

    .success-message {
        background: #eafaf1;
        color: #27ae60;
        border: 1.5px solid #b7e4c7;
    }

    .error-message i, .success-message i {
        margin-right: 7px;
    }

    @keyframes fadeInMsg {
        to { opacity: 1; transform: translateY(0); }
    }

    .link {
        text-align: center;
        margin-top: 20px;
    }

    .link a {
        color: #f7a400;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }

    .link a:hover {
        color: #cc8800;
        text-decoration: underline;
    }

    .loading {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 50px;
        height: 50px;
        border: 5px solid rgba(247, 164, 0, 0.2);
        border-top: 5px solid #f7a400;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        0% { transform: translate(-50%, -50%) rotate(0deg); }
        100% { transform: translate(-50%, -50%) rotate(360deg); }
    }

    @media (max-width: 480px) {
        .container {
            padding: 25px;
            max-width: 95%;
        }

        .container h2 {
            font-size: 24px;
        }

        .logo img {
            height: 80px;
        }
    }

    .swal2-popup.custom-swal2-popup {
        max-width: 340px !important;
        font-size: 1rem !important;
        padding: 1.5em 1.2em 1.2em !important;
        border-radius: 18px !important;
    }

    .swal2-title {
        font-size: 1.15em !important;
        margin-bottom: 0.5em !important;
        margin-top: 0.2em !important;
    }

    .swal2-icon {
        margin-bottom: 0.7em !important;
    }

    .swal2-html-container {
        font-size: 1em !important;
        margin-bottom: 0.5em !important;
    }

    .remember-me {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        font-size: 14px;
        color: #333;
    }

    .remember-me input {
        margin-right: 8px;
    }
</style>
</head>
<body>
    <canvas id="particles"></canvas>
    <div class="container">
        <div class="logo">
            <img src="../assets/images/Badminton.png" alt="Logo Cầu Lông" style="height: 90px; width: auto; display: block; margin: 0 auto;">
        </div>
        <h2>
            <?php
            if ($showRegister) echo 'Đăng ký Admin';
            else echo 'Đăng nhập Admin';
            ?>
        </h2>
        <?php if ($error): ?>
            <div class="error-message"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($showRegister): ?>
            <form method="post" id="registerForm" autocomplete="off">
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="reg-username" name="username" placeholder=" " required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    <label for="reg-username">Tên đăng nhập</label>
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="reg-email" name="email" placeholder=" " required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    <label for="reg-email">Email</label>
                </div>
                <div class="input-group">
                    <i class="fas fa-phone input-icon"></i>
                    <input type="tel" id="reg-phone" name="phone" placeholder=" " required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    <label for="reg-phone">Số điện thoại</label>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="reg-password" name="password" placeholder=" " required>
                    <label for="reg-password">Mật khẩu</label>
                </div>
                <div class="input-group">
                    <i class="fas fa-check input-icon"></i>
                    <input type="password" id="reg-confirm" name="confirm" placeholder=" " required>
                    <label for="reg-confirm">Xác nhận mật khẩu</label>
                </div>
                <button type="submit" class="btn">Đăng ký</button>
                <div class="link">
                    <a href="dangnhap-dangky.php">Đã có tài khoản? Đăng nhập</a>
                </div>
            </form>
        <?php else: ?>
            <?php if (isset($_GET['registered'])): ?>
                <div class="success-message" id="registerSuccessMsg"><i class="fas fa-check-circle"></i> Đăng ký thành công! Bạn có thể đăng nhập.</div>
            <?php endif; ?>
            <?php if (isset($_GET['logout'])): ?>
                <div class="success-message" id="logoutSuccessMsg"><i class="fas fa-check-circle"></i> Đăng xuất thành công! Hẹn gặp lại bạn!</div>
            <?php endif; ?>
            <form method="post" id="loginForm" autocomplete="off">
                <div class="input-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="username" name="username" placeholder=" " required value="<?= isset($_COOKIE['remember_admin_user']) ? htmlspecialchars($_COOKIE['remember_admin_user']) : htmlspecialchars($_POST['username'] ?? '') ?>">
                    <label for="username">Tên đăng nhập</label>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" placeholder=" " required>
                    <label for="password">Mật khẩu</label>
                </div>
                <div class="remember-me">
                    <input type="checkbox" name="remember" id="remember" <?= isset($_POST['remember']) ? 'checked' : '' ?>>
                    <label for="remember">Ghi nhớ đăng nhập</label>
                </div>
                <button type="submit" class="btn">Đăng nhập</button>
                <div class="link">
                    <a href="?register=1">Đăng ký admin mới</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <script>
        // Particle Background
        const canvas = document.getElementById('particles');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const particlesArray = [];
        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 3 + 1;
                this.speedX = Math.random() * 0.5 - 0.25;
                this.speedY = Math.random() * 0.5 - 0.25;
                this.color = `rgba(247, 164, 0, ${Math.random() * 0.5 + 0.2})`;
            }
            update() {
                this.x += this.speedX;
                this.y += this.speedY;
                if (this.size > 0.2) this.size -= 0.01;
                if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
                if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
            }
            draw() {
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function handleParticles() {
            for (let i = 0; i < particlesArray.length; i++) {
                particlesArray[i].update();
                particlesArray[i].draw();
                if (particlesArray[i].size <= 0.2) {
                    particlesArray.splice(i, 1);
                    i--;
                }
            }
        }

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (particlesArray.length < 100) {
                particlesArray.push(new Particle());
            }
            handleParticles();
            requestAnimationFrame(animateParticles);
        }
        animateParticles();

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });

        // AJAX register handling
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const username = document.getElementById('reg-username').value;
                const email = document.getElementById('reg-email').value;
                const phone = document.getElementById('reg-phone').value;
                const password = document.getElementById('reg-password').value;
                const confirm = document.getElementById('reg-confirm').value;
                const formData = new FormData();
                formData.append('username', username);
                formData.append('email', email);
                formData.append('phone', phone);
                formData.append('password', password);
                formData.append('confirm', confirm);

                fetch('dangnhap-dangky.php?register=1', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: new URLSearchParams(formData)
                })
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        confetti({
                            particleCount: 120,
                            spread: 80,
                            origin: { y: 0.6 }
                        });
                        Swal.fire({
                            icon: 'success',
                            title: 'Đăng ký thành công!',
                            text: data.message,
                            confirmButtonColor: '#27ae60',
                            timer: 1500,
                            timerProgressBar: true,
                            background: '#eafaf1',
                            customClass: { icon: 'swal2-icon-success-big', popup: 'custom-swal2-popup' },
                            showClass: { popup: 'animate__animated animate__fadeInDown' },
                            hideClass: { popup: 'animate__animated animate__fadeOutUp' }
                        }).then(() => {
                            window.location.href = 'dangnhap-dangky.php?registered=1';
                        });
                    } else {
                        let errorTitle = 'Lỗi';
                        let errorBg = '#fdecea';
                        let errorIcon = 'error';
                        let errorIconClass = '';
                        let errorShowClass = { popup: 'animate__animated animate__shakeX' };
                        let errorHideClass = { popup: 'animate__animated animate__fadeOutUp' };
                        if (data.message.includes('tồn tại') || data.message.includes('khớp') || data.message.includes('hợp lệ')) {
                            errorTitle = data.message.includes('tồn tại') ? 'Thông tin đã tồn tại' : 
                                        (data.message.includes('khớp') ? 'Mật khẩu chưa khớp' : 'Dữ liệu không hợp lệ');
                            errorBg = '#fffbe6';
                            errorIcon = 'warning';
                            errorIconClass = 'swal2-icon-success-big';
                        }
                        Swal.fire({
                            icon: errorIcon,
                            title: errorTitle,
                            text: data.message,
                            confirmButtonColor: '#e67e22',
                            background: errorBg,
                            customClass: { icon: errorIconClass, popup: 'custom-swal2-popup' },
                            showClass: errorShowClass,
                            hideClass: errorHideClass
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: 'Không thể kết nối máy chủ!',
                        confirmButtonColor: '#e74c3c'
                    });
                });
            });
        }

        // AJAX login handling
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const username = document.getElementById('username').value;
                const password = document.getElementById('password').value;
                const remember = document.getElementById('remember').checked;
                const formData = new FormData();
                formData.append('username', username);
                formData.append('password', password);
                formData.append('remember', remember);

                fetch('dangnhap-dangky.php', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: new URLSearchParams(formData)
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error(`HTTP error! Status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Đăng nhập thành công!' : 'Lỗi',
                        text: data.message,
                        confirmButtonColor: data.success ? '#27ae60' : '#e74c3c',
                        timer: data.success ? 1500 : undefined,
                        timerProgressBar: data.success,
                        customClass: { 
                            icon: data.success ? 'swal2-icon-success-big' : '',
                            popup: 'custom-swal2-popup' 
                        },
                        showClass: { popup: data.success ? 'animate__animated animate__fadeInDown' : 'animate__animated animate__shakeX' },
                        hideClass: { popup: 'animate__animated animate__fadeOutUp' }
                    }).then(() => {
                        if (data.success && data.redirect) {
                            window.location.href = data.redirect;
                        }
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi kết nối',
                        text: 'Không thể kết nối máy chủ! Vui lòng thử lại.',
                        confirmButtonColor: '#e74c3c',
                        customClass: { popup: 'custom-swal2-popup' }
                    });
                });
            });
        }

        // Handle ?registered=1 and ?logout=1
        window.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 is not loaded');
            }
            if (window.location.search.includes('registered=1')) {
                const msgDiv = document.getElementById('registerSuccessMsg');
                if (msgDiv) msgDiv.style.display = 'none';
                Swal.fire({
                    icon: 'success',
                    title: 'Đăng ký thành công!',
                    text: 'Bạn có thể đăng nhập.',
                    confirmButtonColor: '#27ae60',
                    customClass: { icon: 'swal2-icon-success-big', popup: 'custom-swal2-popup' }
                });
            }
            if (window.location.search.includes('logout=1')) {
                const msgDiv = document.getElementById('logoutSuccessMsg');
                if (msgDiv) msgDiv.style.display = 'none';
                Swal.fire({
                    icon: 'success',
                    title: 'Đăng xuất thành công!',
                    text: 'Hẹn gặp lại bạn!',
                    confirmButtonColor: '#27ae60',
                    customClass: { popup: 'custom-swal2-popup' }
                });
            }
        });
    </script>
</body>
</html>