<?php
include 'common/config.php';
session_start();

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password'])) {
    if ($user['is_verified']) {
      session_regenerate_id(true);
      $_SESSION['username'] = $user['username'];

      // Remember me がチェックされている場合
      if (!empty($_POST['remember'])) {
        // 16バイトのランダムなトークンを生成
        $token = bin2hex(random_bytes(16));
        // ユーザーテーブルに remember_token を保存
        $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
        $stmt->execute([$token, $user['id']]);
        // クッキーに remember_token を保存（30日間有効）
        setcookie("remember_token", $token, time() + (86400 * 30), "/", "", false, true);
      }

      header("Location: /harrm/");
      exit();
    } else {
      $error_message = "メールアドレスが認証されていません。メールをご確認ください。";
    }
  } else {
    $error_message = "メールアドレスまたはパスワードが違います。";
  }
}
?>
<!DOCTYPE html>
<html>

<?php
$pageTitle = 'ログイン';
include 'parts/head.php';
?>

<body class="page-login">
  <div class="login-wrap">
    <h1>株式会社いちまるよん<br><span>編集部 HDD管理</span></h1>
    <form method="post" class="form">
      <?php if (!empty($error_message)): ?>
        <div class="login-error-message">
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>
      <div class="form-content">
        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-content">
        <label for="password">パスワード</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div class="rememberme-content">
        <input type="checkbox" name="remember" id="remember">
        <label for="remember">保存する</label>
      </div>
      <button type="submit" class="login">ログイン</button>
    </form>
    <div class="register-info">
      <a href="register">新規登録はこちら</a>
    </div>
  </div>
</body>

</html>