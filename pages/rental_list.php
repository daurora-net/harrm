<?php
include '../common/db.php';

$filter_returned = isset($_GET['filter_returned']) ? $_GET['filter_returned'] : '';

// 表示件数の取得（デフォルトは30件）
$perPage = isset($_GET['perPage']) ? intval($_GET['perPage']) : 30;
if ($perPage <= 0) {
  $perPage = 30;
}

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) {
  $page = 1;
}

$sql = "SELECT hr.id, hr.title, hr.manager, hr.start, hr.end, hr.location, hr.cable, hr.is_returned, hr.return_date, hr.duration, hr.notes, hr.created_by, hr.resource_id, r.name as hdd_name, r.capacity as hdd_capacity 
        FROM hdd_rentals hr
        JOIN hdd_resources r ON hr.resource_id = r.id
        WHERE hr.deleted_at IS NULL";

// 「未返却」指定
if ($filter_returned === '0') {
  $sql .= " AND hr.is_returned = 0";
}

$countSql = "SELECT COUNT(*) FROM hdd_rentals hr JOIN hdd_resources r ON hr.resource_id = r.id WHERE hr.deleted_at IS NULL";
if ($filter_returned === '0') {
  $countSql .= " AND hr.is_returned = 0";
}
$stmtCount = $conn->prepare($countSql);
$stmtCount->execute();
$totalItems = $stmtCount->fetchColumn();

$totalPages = ceil($totalItems / $perPage);
if ($totalPages < 1) {
  $totalPages = 1;
}
if ($page > $totalPages) {
  $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

$sql .= " LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_SESSION['reloaded'])) {
  unset($_SESSION['reloaded']);
  // 必要な場合はアラート
  // echo "<script>
  //           history.replaceState(null, null, 'rental_list');
  //           alert('更新しました。');
  //           window.location.reload();
  //         </script>";
}
$stmtRole = $conn->prepare("SELECT role FROM users WHERE username = ?");
$stmtRole->execute([$_SESSION['username']]);
$currentUserRole = $stmtRole->fetchColumn();

// role=1,2のみアクセス可能
if (!in_array($currentUserRole, [1, 2])) {
  header("Location: /harrm/billing_list");
  exit();
}
?>

<!DOCTYPE html>
<html>

<?php
$pageTitle = 'スケジュール';
include '../parts/head.php';
?>

<body>
  <?php
  $activePage = 'rental_list';
  include '../parts/nav_menu.php';
  ?>
  <main>
    <?php include '../parts/nav_header.php'; ?>
    <div class="container">
      <h2 class="sp">SCHEDULE</h2>
      <div class="header-container">
        <div class="flex">
          <!-- レンタル追加ボタン -->
          <button id="addRentalBtn" class="add-btn"><i class="fa-solid fa-plus"></i></button>
          <!-- フィルターと表示件数選択 -->
          <form method="get" action="">
            <!-- ソートセレクトボックス -->
            <div class="custom-select-wrapper pc-w-100px">
              <select id="filter_returned" name="filter_returned" onchange="this.form.submit();">
                <option value="">すべて</option>
                <option value="0" <?php if ($filter_returned === '0')
                  echo 'selected'; ?>>未返却</option>
              </select>
            </div>
            <!-- 表示件数セレクトボックス -->
            <div class="custom-select-wrapper pc-w-100px ml-10">
              <select name="perPage" id="perPage" onchange="this.form.submit();">
                <?php
                $options = [30, 50, 70, 100];
                foreach ($options as $opt) {
                  $selected = ($opt == $perPage) ? 'selected' : '';
                  echo "<option value=\"$opt\" $selected>$opt 件</option>";
                }
                ?>
              </select>
            </div>
            <input type="hidden" name="page" value="<?php echo $page; ?>">
          </form>
          <!-- テーブル検索機能 -->
          <div class="table-search ml-10">
            <input type="text" id="rentalTableSearchInput" placeholder="&#xf002;">
          </div>
        </div>
      </div>
      <!-- レンタル一覧表示 -->
      <div class="rental-list table-container table-scroll">
        <table class="table-sort">
          <thead>
            <tr>
              <th></th>
              <th onclick="sortTable(this, 1)">ID <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 2)">番組名 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 3)">担当者 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 4)">HDD No. <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 5)">容量 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 6)">開始日 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 7)">終了予定日 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 8)">使用場所 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 9)">ケーブル <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 10)">返却済 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 11)">返却日 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 12)">使用日数 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 13)">メモ <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 14)">作成ユーザー <i class="fa-solid fa-sort no-print"></i></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rentals as $rental) { 
              $endDate = strtotime($rental['end']);
              $today = strtotime(date('Y-m-d'));
              $isOverdue = ($endDate < $today && empty($rental['return_date']));
            ?>
            <tr data-id="<?php echo htmlspecialchars($rental['id']); ?>" <?php if($isOverdue) echo 'class="overdue"'; ?>>
                <td>
                  <button class="edit-btn edit-event-btn" data-bs-toggle="modal" data-bs-target="#editEventModal"
                    data-id="<?php echo htmlspecialchars($rental['id']); ?>"
                    data-title="<?php echo htmlspecialchars($rental['title']); ?>"
                    data-manager="<?php echo htmlspecialchars($rental['manager']); ?>"
                    data-start="<?php echo htmlspecialchars($rental['start']); ?>"
                    data-end="<?php echo htmlspecialchars($rental['end']); ?>"
                    data-resource-id="<?php echo htmlspecialchars($rental['resource_id']); ?>"
                    data-location="<?php echo htmlspecialchars($rental['location']); ?>"
                    data-cable="<?php echo htmlspecialchars($rental['cable']); ?>"
                    data-is-returned="<?php echo htmlspecialchars($rental['is_returned']); ?>"
                    data-return-date="<?php echo htmlspecialchars($rental['return_date']); ?>"
                    data-notes="<?php echo htmlspecialchars($rental['notes']); ?>">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </button>
                </td>
                <td class="text-center"><?php echo htmlspecialchars($rental['id']); ?></td>
                <td><?php echo htmlspecialchars($rental['title']); ?></td>
                <td><?php echo htmlspecialchars($rental['manager']); ?></td>
                <td><?php echo htmlspecialchars($rental['hdd_name']); ?></td>
                <td><?php echo htmlspecialchars($rental['hdd_capacity']); ?></td>
                <td><?php echo htmlspecialchars($rental['start']); ?></td>
                <td><?php echo htmlspecialchars($rental['end']); ?></td>
                <td><?php echo htmlspecialchars($rental['location']); ?></td>
                <td><?php echo htmlspecialchars($rental['cable']); ?></td>
                <td class="text-center"><?php echo $rental['is_returned'] ? '✔︎' : ''; ?></td>
                <td><?php echo htmlspecialchars($rental['return_date']) ?: ''; ?></td>
                <td class="text-right">
                  <?php if ($rental['is_returned'] == 1): ?>
                    <?php echo htmlspecialchars($rental['duration']); ?>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($rental['notes']); ?></td>
                <td><?php echo htmlspecialchars($rental['created_by']); ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <!-- ページネーションリンク -->
      <ul class="pagination">
        <?php if ($page <= 1): ?>
          <li class="disabled">
            <a href="#"><i class="fas fa-angle-left"></i></a>
          </li>
        <?php else: ?>
          <li>
            <a
              href="?page=<?php echo $page - 1; ?>&perPage=<?php echo $perPage; ?>&filter_returned=<?php echo urlencode($filter_returned); ?>">
              <i class="fas fa-angle-left"></i>
            </a>
          </li>
        <?php endif; ?>

        <?php
        $maxPagesToShow = 3;
        for ($i = 1; $i <= min($totalPages, $maxPagesToShow); $i++):
          ?>
          <?php if ($i == $page): ?>
            <li class="active"><a href="#"><?php echo $i; ?></a></li>
          <?php else: ?>
            <li>
              <a
                href="?page=<?php echo $i; ?>&perPage=<?php echo $perPage; ?>&filter_returned=<?php echo urlencode($filter_returned); ?>">
                <?php echo $i; ?>
              </a>
            </li>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page >= $totalPages): ?>
          <li class="disabled">
            <a href="#"><i class="fas fa-angle-right"></i></a>
          </li>
        <?php else: ?>
          <li>
            <a
              href="?page=<?php echo $page + 1; ?>&perPage=<?php echo $perPage; ?>&filter_returned=<?php echo urlencode($filter_returned); ?>">
              <i class="fas fa-angle-right"></i>
            </a>
          </li>
        <?php endif; ?>
      </ul>
      <div class="pagination-info">
        <?php
        $startItem = $offset + 1;
        $endItem = min($totalItems, $offset + $perPage);
        ?>
        <p><?php echo $startItem; ?>-<?php echo $endItem; ?> / <?php echo $totalItems; ?></p>
      </div>


    </div>

    <?php
    // 追加モーダル
    include '../modals/add_rental_modal.php';
    ?>

    <?php
    // 編集モーダル
    include '../modals/edit_event_modal.php';
    ?>
  </main>

  <script src="../assets/js/modal.js"></script>
  <script src="../assets/js/table.js"></script>
</body>

</html>