<?php
include '../common/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $userId = $_POST['userId'] ?? null;
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $role = isset($_POST['role']) ? (int) $_POST['role'] : 2; // デフォルトを 2(一般) 

  try {
    if (isset($_POST['delete']) && $_POST['delete'] == '1') {
      $stmt = $conn->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
      $stmt->execute([$userId]);

      // ログイン中のユーザーが削除対象なら即ログアウト
      $stmt2 = $conn->prepare("SELECT id FROM users WHERE username = ?");
      $stmt2->execute([$_SESSION['username']]);
      $currentUser = $stmt2->fetch(PDO::FETCH_ASSOC);

      if ($currentUser && $currentUser['id'] == $userId) {
        session_unset();
        session_destroy();
        // remember_tokenクッキーも削除
        setcookie('remember_token', '', time() - 3600, '/');

        header("Location: /hdd-rental/login");
        exit();
      }

    } else {
      $stmt = $conn->prepare("
        UPDATE users
        SET username = ?
        WHERE id = ?
      ");
      $stmt->execute([$username, $userId]);
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();

  } catch (PDOException $e) {
    error_log("ユーザー更新エラー: " . $e->getMessage());
    echo "エラーが発生しました。";
  }
}