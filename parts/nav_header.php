<?php
include_once __DIR__ . '/../common/db.php';

$roleColor = 'cadetblue';

// ユーザーのroleに応じた背景色の設定
if (isset($_SESSION['username'])) {
  $stmt = $conn->prepare("SELECT role FROM users WHERE username = ?");
  $stmt->execute([$_SESSION['username']]);
  $role = $stmt->fetchColumn();

  switch ($role) {
    case 1:
      // 管理者の場合
      $roleColor = '#caab4e';
      break;
    case 2:
      // 一般の場合
      $roleColor = 'cadetblue';
      break;
    case 3:
      // 料金の場合
      $roleColor = '#ab8cb6';
      break;
    default:
      $roleColor = 'cadetblue';
      break;
  }
}
?>
<div class="header-nav no-print" style="background-color: <?php echo htmlspecialchars($roleColor); ?>;">
  <div id="menu-toggle" class="open-icon">
    <i class="fa-solid fa-bars-staggered"></i>
  </div>
  <div class="header-nav-info">
    <p class="pc">id: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
    <button class="logout pc">
      <a href="logout.php">LOGOUT</a>
    </button>
  </div>
</div>