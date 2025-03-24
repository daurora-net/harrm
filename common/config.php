<?php
$servername = "mysql12007.xserver.jp";
$username = "daurora_hdd";
$password = "hddrental";
$dbname = "daurora_hddrental";

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}