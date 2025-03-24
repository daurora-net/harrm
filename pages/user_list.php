<?php
include '../common/db.php';

$stmtRole = $conn->prepare("SELECT role FROM users WHERE username = ?");
$stmtRole->execute([$_SESSION['username']]);
$currentUserRole = $stmtRole->fetchColumn();

// role=1のみアクセス可能
if ($currentUserRole != 1) {
  header("Location: /");
  exit();
}

// 【データ取得】deleted_at が NULL のユーザーのみ取得
$stmt = $conn->prepare("SELECT * FROM users WHERE deleted_at IS NULL");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<?php
$pageTitle = 'ユーザー';
include '../parts/head.php';
?>

<body>
  <?php
  $activePage = 'user_list';
  include '../parts/nav_menu.php';
  ?>
  <main>
    <?php
    include '../parts/nav_header.php';
    ?>

    <div class="container">
      <h2 class="sp">USER</h2>
      <div class="user-list table-container table-scroll">
        <table class="table-sort">
          <thead>
            <tr>
              <th></th>
              <th onclick="sortTable(this, 1)">ID <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 2)">ユーザー名 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 3)">メールアドレス <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 4)">認証 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 5)">権限 <i class="fa-solid fa-sort no-print"></i></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <tr>
                <td>
                  <button class="edit-btn edit-user-btn" data-id="<?php echo htmlspecialchars($user['id']); ?>"
                    data-username="<?php echo htmlspecialchars($user['username']); ?>">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </button>
                </td>
                <td class="text-center"><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td class="text-center"><?php echo $user['is_verified'] ? '✔︎' : ''; ?></td>
                <td>
                  <?php
                  $roles = [
                    1 => '管理者',
                    2 => '一般',
                    3 => '料金'
                  ];
                  echo htmlspecialchars($roles[$user['role']]);
                  ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>

        </table>
      </div>
    </div>

    <?php
    // 編集モーダル
    include '../modals/edit_user_modal.php';
    ?>
  </main>

</body>

</html>