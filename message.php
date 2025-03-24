<?php
session_start();

if (isset($_SESSION['username'])) {
  header("Location: /hdd-rental/");
  exit();
}
?>

<!DOCTYPE html>
<html>

<head>
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
  <link rel="stylesheet" type="text/css" href="assets/css/reset.css">
</head>

<body>
  <div class="verification_message">
    <h2><?php echo $_SESSION['verification_message']; ?></h2><br>
    <p>認証が完了したら、このページは閉じてください。</p>
  </div>
  <script>
    // リロードがまだ行われていない場合だけリロード
    if (!sessionStorage.getItem('messageReloaded')) {
      sessionStorage.setItem('messageReloaded', 'true');
      window.location.reload();
    }
  </script>
  <script>
    // verify.phpからのメッセージを受信し、トップページにリダイレクト
    window.addEventListener('message', function (event) {
      if (event.data === 'redirect_to_top') {
        window.location.href = '/hdd-rental/';
      }
    });
  </script>
</body>

</html>