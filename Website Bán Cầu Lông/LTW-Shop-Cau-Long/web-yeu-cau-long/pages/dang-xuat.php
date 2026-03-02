<?php
session_start(); // Bắt đầu phiên làm việc

// Xóa toàn bộ dữ liệu phiên
session_unset();
session_destroy();

// Chuyển hướng về trang index.php
header("Location: ../index.php");
exit();
?>