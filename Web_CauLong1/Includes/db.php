<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "VNSPORTS";

$link = mysqli_connect("localhost", "root", "", "VNSPORTS") 
    or die("Không thể kết nối đến cơ sở dữ liệu: " . mysqli_connect_error());
mysqli_select_db($link, "VNSPORTS") 
    or die("Không thể chọn cơ sở dữ liệu: " . mysqli_error($link));
mysqli_set_charset($link, "utf8");
?>
