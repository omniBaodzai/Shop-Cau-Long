<?php
ob_start();
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

include '../includes/header.php'; 
include '../connect.php'; 
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css">
<link rel="stylesheet" href="../assets/css/style.css">

<style>
  main {
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc88 60%, #e0e7ff88 100%),
      url("https://tennislovers.com/wp-content/uploads/badminton-racket-and-shuttlecock.jpg")
        center/cover no-repeat;
    background-blend-mode: multiply;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 0;
  }
</style>

<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    // Check user credentials
    $sql = "SELECT id, name, password FROM users WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            // Handle "Remember Me" functionality
            if ($remember) {
                // Set cookie for username (30 days)
                setcookie('remember_user', $name, time() + (30 * 24 * 60 * 60), "/");
            } else {
                // Clear cookie if exists
                if (isset($_COOKIE['remember_user'])) {
                    setcookie('remember_user', '', time() - 3600, "/");
                }
            }

            header("Location: ../index.php");
            exit();
        } else {
            $error = "Mật khẩu không chính xác.";
        }
    } else {
        $error = "Tên người dùng không tồn tại.";
    }

    $stmt->close();
    $conn->close();
}
?>

<main>
  <div class="login-2col">
    <div class="login-left">
      <div class="welcome-content">
        <img
          src="https://cdn-icons-png.flaticon.com/512/3135/3135768.png"
          alt="Login"
          class="welcome-img"
        />
        <h2>Chào mừng trở lại <span class="brand">YeuCauLong</span></h2>
        <p>
          Đăng nhập tài khoản để nhận ưu đãi, cập nhật sản phẩm mới và trải
          nghiệm dịch vụ tốt nhất từ chúng tôi.
        </p>
        <a href="dang-ky.php" class="register-cta">
          <i class="ri-user-add-line"></i> Chưa có tài khoản? Đăng ký ngay
        </a>
      </div>
    </div>
    <div class="login-right">
      <form class="glass-form" method="POST">
        <h2>Đăng nhập</h2>
        <?php if (isset($error)): ?>
          <p class="error-msg"><?php echo $error; ?></p>
        <?php endif; ?>
        <div class="form-group">
          <i class="ri-user-line"></i>
          <input 
            type="text" 
            name="name" 
            placeholder="Tên người dùng" 
            required 
            value="<?php echo isset($_COOKIE['remember_user']) ? htmlspecialchars($_COOKIE['remember_user']) : ''; ?>"
          />
        </div>
        <div class="form-group">
          <i class="ri-lock-line"></i>
          <input type="password" name="password" placeholder="Mật khẩu" required />
        </div>
        <div class="form-options">
          <label class="remember-me">
            <input type="checkbox" name="remember" <?php echo isset($_COOKIE['remember_user']) ? 'checked' : ''; ?> /> Ghi nhớ đăng nhập
          </label>
          <a href="#" class="forgot-link">Quên mật khẩu?</a>
        </div>
        <button type="submit" class="login-btn">Đăng nhập</button>
      </form>
    </div>
  </div>
</main>

<?php include '../includes/footer.php'; ?>