<?php
include 'common/db.php';

$stmtRole = $conn->prepare("SELECT role FROM users WHERE username = ?");
$stmtRole->execute([$_SESSION['username']]);
$currentUserRole = $stmtRole->fetchColumn();

// role=1,2のみアクセス可能
if (!in_array($currentUserRole, [1, 2])) {
  header("Location: /harrm/billing_list");
  exit();
}
?>
<?php
session_start();
?>
<!DOCTYPE html>
<html>

<?php
$isIndex = true;
include 'parts/head.php';
?>

<body>
  <script>
    // SPサイズの場合 /rental_list へリダイレクト
    if (window.matchMedia("(max-width: 768px)").matches) {
      window.location.href = '/harrm/rental_list';
    }
  </script>
  <?php
  $activePage = 'index';
  include 'parts/nav_menu.php';
  ?>
  <main>
    <?php
    include 'parts/nav_header.php';
    ?>
    <div class="container">
      <div id="calendar" class="header-container">
        <!-- レンタル詳細追加ボタン -->
        <button id="addRentalBtn" class="add-btn"><i class="fa-solid fa-plus"></i></button>
      </div>
    </div>
  </main>

  <?php
  // レンタル詳細追加用ポップアップモーダル
  include 'modals/add_rental_modal.php';

  // イベント編集用ポップアップモーダル
  include 'modals/edit_event_modal.php';
  ?>

</body>

</html>