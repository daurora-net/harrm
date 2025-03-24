<?php
include 'common/config.php';
require __DIR__ . '/vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
  $email = $_POST['email'];
  // ランダムな認証コードを生成
  $verification_code = bin2hex(random_bytes(16));
  // 現在時刻から1時間後を設定
  $verification_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

  // ユーザー情報をデータベースに挿入
  $sql = "INSERT INTO users (username, password, email, verification_code, verification_expiry) VALUES (?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->execute([$username, $password, $email, $verification_code, $verification_expiry]);

  // 認証メールを送信
  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'daurora.net@gmail.com';
    $mail->Password = 'vglq oqfs dvbs kamx';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // 文字化け解消
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    // ---------------------------------------------
    //  メール内容
    // ---------------------------------------------
    // Recipients
    $mail->setFrom('daurora.net@gmail.com', 'harrm');
    $mail->addAddress($email, $username);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'メールアドレス認証';
    $mail->Body = "harrmシステムに新規ユーザー登録されます。<br>以下のリンクをクリックして、認証を完了してください。<br><br><a href='https://xs421360.xsrv.jp/harrm/verify.php?code=$verification_code'>こちらをクリック</a>";
    // ---------------------------------------------


    // ---------------------------------------------
    //  ブラウザ表示
    // ---------------------------------------------
    $mail->send();
    $_SESSION['verification_message'] = 'メール認証用リンクを送信しました。メールを確認し、リンクをクリックしてアカウントを有効化してください。';
    header("Location: /harrm/message.php");
    exit();
  } catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }
  // ---------------------------------------------
}
?>

<!DOCTYPE html>
<html>

<?php
$pageTitle = '新規登録';
include 'parts/head.php';
?>

<body class="page-login">
  <div class="login-wrap">
    <h1 class="text-center">株式会社いちまるよん<br><span>編集部 HDD管理</span></h1>
    <form method="post" class="form">
      <div class="form-content">
        <label for="username">ログイン名</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-content">
        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-content">
        <label for="password">パスワード</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="login">新規登録</button>
    </form>
    <div class="register-info">
      <a href="login">ログインはこちら</a>
    </div>
  </div>
</body>

</html>