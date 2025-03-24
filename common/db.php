<?php
session_start();
require __DIR__ . '/config.php';

// セッションが無い場合、remember_token クッキーで自動ログイン
if (!isset($_SESSION['username']) && isset($_COOKIE['remember_token'])) {
  $stmt = $conn->prepare("SELECT username FROM users WHERE remember_token = ?");
  $stmt->execute([$_COOKIE['remember_token']]);
  if ($stmt->rowCount() > 0) {
    $_SESSION['username'] = $stmt->fetchColumn();
  }
}

if (!isset($_SESSION['username'])) {
  header("Location: login");
  exit();
}

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->rowCount() === 0) {
  // ユーザーが存在しない場合はセッションを破棄してログイン画面にリダイレクト
  session_unset();
  session_destroy();
  header("Location: login");
  exit();
}