<?php
include 'common/config.php';
session_start();

if (isset($_GET['code'])) {
  $verification_code = $_GET['code'];

  // 認証コードと有効期限を確認
  $stmt = $conn->prepare("SELECT * FROM users WHERE verification_code = ? AND verification_expiry > NOW()");
  $stmt->execute([$verification_code]);
  $user = $stmt->fetch();

  if ($user) {
    // ユーザーを認証済みに更新
    $stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_code = NULL, verification_expiry = NULL WHERE verification_code = ?");
    $stmt->execute([$verification_code]);

    // セッションにユーザー情報を設定してログイン
    $_SESSION['username'] = $user['username'];

    // アラート表示後にリダイレクト
    echo "<script>
            alert('認証が完了しました');
            window.location.href = '/harrm/';
        </script>";
    exit();
  } else {
    // 認証コードが無効または期限切れ
    echo "<script>
            alert('認証コードが無効または期限切れです。');
            window.location.href = '/harrm/login.php';
        </script>";
    exit();
  }
} else {
  // 認証コードが提供されていない場合
  echo "<script>
        alert('認証コードが提供されていません。');
        window.location.href = '/harrm/login.php';
    </script>";
  exit();
}