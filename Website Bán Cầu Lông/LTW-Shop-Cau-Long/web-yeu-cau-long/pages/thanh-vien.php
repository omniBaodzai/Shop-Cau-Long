<?php
include '../includes/header.php';
include '../connect.php'; // Kết nối cơ sở dữ liệu

// Khởi tạo các biến với giá trị mặc định để tránh lỗi undefined variable
$user_name = '';
$user_email = '';
$user_phone = '';
$user_address = '';
$user_city = '';
$user_district = '';
$user_created_at = '';
$profile_loaded = false; // Biến cờ để kiểm tra xem thông tin người dùng đã được tải thành công chưa

// Kiểm tra xem người dùng đã đăng nhập hay chưa
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

if ($user_id > 0) {
    // Truy vấn thông tin người dùng từ bảng `users`
    $stmt = $conn->prepare("SELECT name, email, phone, address, city, district, created_at FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_name = htmlspecialchars($user['name']);
            $user_email = htmlspecialchars($user['email']);
            $user_phone = htmlspecialchars($user['phone']);
            $user_address = htmlspecialchars($user['address']);
            $user_city = htmlspecialchars($user['city']);
            $user_district = htmlspecialchars($user['district']);
            $user_created_at = htmlspecialchars($user['created_at']);
            $profile_loaded = true;
        } else {
            echo "<p class='error-msg'>Không tìm thấy thông tin người dùng.</p>";
        }
        $stmt->close();
    } else {
        echo "<p class='error-msg'>Lỗi chuẩn bị truy vấn thông tin người dùng: " . $conn->error . "</p>";
    }
} else {
    echo "<p class='error-msg'>Bạn chưa đăng nhập. Vui lòng đăng nhập để xem thông tin cá nhân.</p>";
    exit(); // Thoát nếu chưa đăng nhập
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $fullname = htmlspecialchars(trim($_POST['fullname']));
        $phone = htmlspecialchars(trim($_POST['phone']));
        $address = htmlspecialchars(trim($_POST['address']));
        $city = htmlspecialchars(trim($_POST['city']));
        $district = htmlspecialchars(trim($_POST['district']));

        if (!empty($fullname) && !empty($phone) && !empty($address) && !empty($city) && !empty($district)) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ?, city = ?, district = ? WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("sssssi", $fullname, $phone, $address, $city, $district, $user_id);

                if ($stmt->execute()) {
                    $user_name = $fullname;
                    $user_phone = $phone;
                    $user_address = $address;
                    $user_city = $city;
                    $user_district = $district;
                    echo "<p class='success-msg'>Thông tin đã được cập nhật thành công!</p>";
                } else {
                    echo "<p class='error-msg'>Có lỗi xảy ra khi cập nhật thông tin: " . $stmt->error . "</p>";
                }
                $stmt->close();
            } else {
                echo "<p class='error-msg'>Lỗi chuẩn bị truy vấn cập nhật hồ sơ: " . $conn->error . "</p>";
            }
        } else {
            echo "<p class='error-msg'>Vui lòng điền đầy đủ thông tin để cập nhật hồ sơ.</p>";
        }
    }

    if ($_POST['action'] === 'change_password') {
        $old_password = htmlspecialchars(trim($_POST['old-password']));
        $new_password = htmlspecialchars(trim($_POST['new-password']));
        $confirm_password = htmlspecialchars(trim($_POST['confirm-password']));

        if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
            echo "<p class='error-msg'>Vui lòng điền đầy đủ các trường mật khẩu.</p>";
        } elseif ($new_password !== $confirm_password) {
            echo "<p class='error-msg'>Mật khẩu mới và xác nhận mật khẩu không khớp.</p>";
        } elseif (strlen($new_password) < 6) {
            echo "<p class='error-msg'>Mật khẩu mới phải có ít nhất 6 ký tự.</p>";
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $user_db = $result->fetch_assoc();
                    if (password_verify($old_password, $user_db['password'])) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        if ($stmt_update) {
                            $stmt_update->bind_param("si", $hashed_password, $user_id);

                            if ($stmt_update->execute()) {
                                echo "<p class='success-msg'>Mật khẩu đã được thay đổi thành công!</p>";
                            } else {
                                echo "<p class='error-msg'>Có lỗi xảy ra khi đổi mật khẩu: " . $stmt_update->error . "</p>";
                            }
                            $stmt_update->close();
                        } else {
                            echo "<p class='error-msg'>Lỗi chuẩn bị truy vấn cập nhật mật khẩu: " . $conn->error . "</p>";
                        }
                    } else {
                        echo "<p class='error-msg'>Mật khẩu hiện tại không đúng.</p>";
                    }
                } else {
                    echo "<p class='error-msg'>Không tìm thấy thông tin người dùng.</p>";
                }
                $stmt->close();
            } else {
                echo "<p class='error-msg'>Lỗi chuẩn bị truy vấn mật khẩu cũ: " . $conn->error . "</p>";
            }
        }
    }
}

$conn->close();
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    .member-main {
        flex-grow: 1;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 20px;
        background: linear-gradient(135deg, #f0f4f8 0%, #e0e7ff 100%);
    }

    .member-container {
        display: flex;
        gap: 30px;
        width: 100%;
        max-width: 1300px;
        background-color: #ffffff;
        border-radius: 15px;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
        padding: 30px;
        flex-wrap: wrap;
    }

    .member-sidebar {
        flex: 0 0 300px;
        background: linear-gradient(180deg, #f8fafc 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
    }

    .member-tabs {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .tab-btn {
        padding: 15px 20px;
        border: none;
        background-color: transparent;
        border-radius: 10px;
        cursor: pointer;
        text-align: left;
        font-size: 17px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 15px;
        transition: all 0.3s ease;
        color: #495057;
        border-left: 6px solid transparent;
    }

    .tab-btn i {
        font-size: 22px;
        color: #4682b4;
    }

    .tab-btn:hover {
        background-color: #e9ecef;
        color: #2c5282;
        border-left-color: #a3bffa;
        transform: translateX(5px);
    }

    .tab-btn.active {
        background: linear-gradient(90deg, #e6f0fa 0%, #d1e0ff 100%);
        color: #2c5282;
        font-weight: 600;
        border-left-color: #4682b4;
        box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .tab-btn.active i {
        color: #2c5282;
    }

    .tab-content {
        flex: 1;
        padding: 30px;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        color: #333;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
        min-height: 450px;
    }

    .tab-pane {
        display: none;
        width: 100%;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
    }

    .tab-pane.active {
        display: flex;
    }

    h2 {
        font-size: 28px;
        margin-bottom: 25px;
        color: #2c5282;
        text-align: center;
        position: relative;
        padding-bottom: 12px;
    }

    h2::after {
        content: '';
        position: absolute;
        left: 50%;
        bottom: 0;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, #4682b4 0%, #a3bffa 100%);
        border-radius: 2px;
    }

    .member-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 25px;
        padding: 25px;
        background-color: #fafcff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        width: 100%;
        max-width: 650px;
    }

    .member-avatar-wrap {
        position: relative;
        width: 140px;
        height: 140px;
        border-radius: 50%;
        background-color: #f1f5f9;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 0 0 6px rgba(70, 130, 180, 0.15);
        transition: transform 0.3s ease;
    }

    .member-avatar-wrap:hover {
        transform: scale(1.05);
    }

    .member-avatar {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #ffffff;
    }

    .avatar-edit {
        position: absolute;
        bottom: 0;
        right: 0;
        background: linear-gradient(45deg, #a3bffa, #4682b4);
        border-radius: 50%;
        padding: 10px;
        cursor: pointer;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s ease;
    }

    .avatar-edit:hover {
        transform: scale(1.15);
    }

    .avatar-edit label {
        color: #fff;
        font-size: 22px;
        margin: 0;
    }

    .member-info {
        text-align: center;
        width: 100%;
    }

    .member-name {
        font-size: 30px;
        color: #2c5282;
        margin-top: 15px;
        margin-bottom: 20px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .member-detail-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
        text-align: left;
        width: 100%;
        max-width: 450px;
    }

    .member-detail {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 16px;
        color: #4a5568;
        background-color: #f8fafc;
        padding: 12px 18px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        transition: transform 0.2s ease;
    }

    .member-detail:hover {
        transform: translateX(5px);
    }

    .member-detail i {
        color: #4682b4;
        font-size: 20px;
    }

    .member-detail span {
        flex-grow: 1;
        color: #2d3748;
    }

    .edit-card, .change-password-card {
        padding: 30px;
        background-color: #fafcff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        width: 100%;
        max-width: 550px;
    }

    .edit-form, .change-password-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .edit-group, .change-password-group {
        margin-bottom: 0;
    }

    .edit-group label, .change-password-group label {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        font-weight: 600;
        color: #2c5282;
        font-size: 16px;
    }

    .edit-group label i, .change-password-group label i {
        font-size: 20px;
        color: #4682b4;
    }

    .edit-group input, .change-password-group input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 16px;
        box-sizing: border-box;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .edit-group input:focus, .change-password-group input:focus {
        border-color: #4682b4;
        box-shadow: 0 0 0 3px rgba(70, 130, 180, 0.15);
        outline: none;
    }

    .edit-actions-row, .change-password-actions {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 25px;
        flex-wrap: wrap;
    }

    .edit-btn, .change-btn {
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .edit-btn.save, .change-btn.save {
        background: linear-gradient(90deg, #2c5282 0%, #4682b4 100%);
        color: #fff;
    }

    .edit-btn.save:hover, .change-btn.save:hover {
        background: linear-gradient(90deg, #4682b4 0%, #2c5282 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    .edit-btn.cancel, .change-btn.cancel {
        background-color: #e9ecef;
        color: #4a5568;
    }

    .edit-btn.cancel:hover, .change-btn.cancel:hover {
        background-color: #d1d5db;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    .success-msg {
        color: #2f855a;
        background-color: #d3f9e6;
        border: 1px solid #a7e2d1;
        padding: 12px 20px;
        border-radius: 8px;
        margin: 20px auto;
        text-align: center;
        font-weight: 500;
        width: 100%;
        max-width: 600px;
        animation: fadeIn 0.5s ease;
    }

    .error-msg {
        color: #c53030;
        background-color: #fee2e2;
        border: 1px solid #f5b7b1;
        padding: 12px 20px;
        border-radius: 8px;
        margin: 20px auto;
        text-align: center;
        font-weight: 500;
        width: 100%;
        max-width: 600px;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 1024px) {
        .member-container {
            flex-direction: column;
            gap: 25px;
            padding: 20px;
        }

        .member-sidebar {
            flex: 0 0 auto;
            width: 100%;
            padding: 20px;
        }

        .member-tabs {
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }

        .tab-btn {
            flex: 1 1 auto;
            text-align: center;
            justify-content: center;
            padding: 12px 15px;
        }

        .tab-content {
            padding: 20px;
            min-height: auto;
        }

        .member-card, .edit-card, .change-password-card {
            max-width: 100%;
        }
    }

    @media (max-width: 576px) {
        .member-container {
            padding: 15px;
        }

        .tab-btn {
            font-size: 15px;
            padding: 10px 12px;
            gap: 10px;
        }

        .tab-btn i {
            font-size: 18px;
        }

        h2 {
            font-size: 22px;
        }

        .member-avatar-wrap {
            width: 100px;
            height: 100px;
        }

        .member-name {
            font-size: 24px;
        }

        .edit-btn, .change-btn {
            font-size: 15px;
            padding: 10px 20px;
        }

        .edit-actions-row, .change-password-actions {
            flex-direction: column;
            align-items: center;
        }
    }
</style>

<main class="member-main">
    <div class="member-container">
        <aside class="member-sidebar">
            <nav class="member-tabs">
                <button class="tab-btn active" onclick="showTab('profile')">
                    <i class="ri-user-line"></i> Thông tin thành viên
                </button>
                <button class="tab-btn" onclick="showTab('edit')">
                    <i class="ri-edit-line"></i> Chỉnh sửa thông tin
                </button>
                <button class="tab-btn" onclick="showTab('change-password')">
                    <i class="ri-lock-password-line"></i> Đổi mật khẩu
                </button>
            </nav>
        </aside>

        <section class="tab-content">
            <div class="tab-pane active" id="profile">
                <section class="member-card">
                    <h2>Thông tin thành viên</h2>
                    <div class="member-avatar-wrap">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Avatar" class="member-avatar" />
                        <div class="avatar-edit">
                            <label for="avatar-upload" title="Đổi ảnh đại diện">
                                <i class="ri-camera-line"></i>
                            </label>
                            <input type="file" id="avatar-upload" style="display: none" />
                        </div>
                    </div>
                    <div class="member-info">
                        <h3 class="member-name"><?= $user_name ?></h3>
                        <div class="member-detail-list">
                            <div class="member-detail">
                                <i class="ri-mail-line"></i> Email: <span><?= $user_email ?></span>
                            </div>
                            <div class="member-detail">
                                <i class="ri-phone-line"></i> SĐT: <span><?= $user_phone ?></span>
                            </div>
                            <div class="member-detail">
                                <i class="ri-calendar-line"></i> Ngày tham gia: <span><?= date('d/m/Y', strtotime($user_created_at)) ?></span>
                            </div>
                            <div class="member-detail">
                                <i class="ri-map-pin-line"></i> Địa chỉ: <span><?= $user_address ?>, <?= $user_district ?>, <?= $user_city ?></span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="tab-pane" id="edit">
                <section class="edit-card">
                    <form class="edit-form" method="POST" autocomplete="off" onsubmit="return validateForm('edit')">
                        <h2><i class="ri-edit-line"></i> Chỉnh sửa thông tin</h2>
                        <div class="edit-group">
                            <label for="fullname"><i class="ri-user-3-line"></i> Họ và tên</label>
                            <input type="text" id="fullname" name="fullname" value="<?= $user_name ?>" required>
                        </div>
                        <div class="edit-group">
                            <label for="phone"><i class="ri-phone-line"></i> Số điện thoại</label>
                            <input type="tel" id="phone" name="phone" value="<?= $user_phone ?>" pattern="[0-9]{10}" required>
                        </div>
                        <div class="edit-group">
                            <label for="address"><i class="ri-map-pin-line"></i> Địa chỉ</label>
                            <input type="text" id="address" name="address" value="<?= $user_address ?>" required>
                        </div>
                        <div class="edit-group">
                            <label for="city"><i class="ri-building-line"></i> Thành phố</label>
                            <input type="text" id="city" name="city" value="<?= $user_city ?>" required>
                        </div>
                        <div class="edit-group">
                            <label for="district"><i class="ri-community-line"></i> Quận/Huyện</label>
                            <input type="text" id="district" name="district" value="<?= $user_district ?>" required>
                        </div>
                        <div class="edit-actions-row">
                            <button type="submit" class="edit-btn save" name="action" value="update_profile">
                                <i class="ri-save-3-line"></i> Lưu thay đổi
                            </button>
                            <button type="button" class="edit-btn cancel" onclick="showTab('profile')">
                                <i class="ri-arrow-go-back-line"></i> Hủy
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <div class="tab-pane" id="change-password">
                <section class="change-password-card">
                    <form class="change-password-form" method="POST" autocomplete="off" onsubmit="return validateForm('password')">
                        <h2><i class="ri-lock-password-line"></i> Đổi mật khẩu</h2>
                        <div class="change-password-group">
                            <label for="old-password"><i class="ri-lock-2-line"></i> Mật khẩu hiện tại</label>
                            <input type="password" id="old-password" name="old-password" required>
                        </div>
                        <div class="change-password-group">
                            <label for="new-password"><i class="ri-key-2-line"></i> Mật khẩu mới</label>
                            <input type="password" id="new-password" name="new-password" required>
                        </div>
                        <div class="change-password-group">
                            <label for="confirm-password"><i class="ri-key-line"></i> Xác nhận mật khẩu mới</label>
                            <input type="password" id="confirm-password" name="confirm-password" required>
                        </div>
                        <div class="change-password-actions">
                            <button type="submit" class="change-btn save" name="action" value="change_password">
                                <i class="ri-save-3-line"></i> Lưu thay đổi
                            </button>
                            <button type="button" class="change-btn cancel" onclick="showTab('profile')">
                                <i class="ri-arrow-go-back-line"></i> Hủy
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </div>
</main>

<script>
    function showTab(tabId) {
        const tabs = document.querySelectorAll('.tab-pane');
        tabs.forEach(tab => tab.classList.remove('active'));

        const selectedTab = document.getElementById(tabId);
        if (selectedTab) selectedTab.classList.add('active');

        const buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(button => button.classList.remove('active'));

        const activeButton = document.querySelector(`[onclick="showTab('${tabId}')"]`);
        if (activeButton) activeButton.classList.add('active');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const successMsg = document.querySelector('.success-msg');
        const errorMsg = document.querySelector('.error-msg');
        showTab('profile');
    });

    function validateForm(type) {
        if (type === 'edit') {
            const phone = document.getElementById('phone').value;
            if (!/^[0-9]{10}$/.test(phone)) {
                alert('Số điện thoại phải là 10 chữ số.');
                return false;
            }
        } else if (type === 'password') {
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            if (newPassword.length < 6) {
                alert('Mật khẩu mới phải có ít nhất 6 ký tự.');
                return false;
            }
            if (newPassword !== confirmPassword) {
                alert('Mật khẩu mới và xác nhận không khớp.');
                return false;
            }
        }
        return true;
    }

    document.getElementById('avatar-upload').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const avatarImg = document.querySelector('.member-avatar');
                if (avatarImg) avatarImg.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>

<?php include '../includes/footer.php'; ?>