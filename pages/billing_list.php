<?php
// ---------------------------------------------
// billing_listページ
// ---------------------------------------------
include '../common/db.php';

$stmtRole = $conn->prepare("SELECT role FROM users WHERE username = ?");
$stmtRole->execute([$_SESSION['username']]);
$currentUserRole = $stmtRole->fetchColumn();

// role=3のみアクセス可能
if ($currentUserRole != 3) {
  header("Location: /");
  exit();
}

$selectedYm = isset($_GET['ym']) ? $_GET['ym'] : date('Y-m', strtotime('first day of last month'));

$sqlMonths = "
  SELECT DISTINCT DATE_FORMAT(return_date, '%Y-%m') AS ym
  FROM hdd_rentals
  WHERE return_date IS NOT NULL AND deleted_at IS NULL
  ORDER BY ym ASC
";
$stmtMonths = $conn->prepare($sqlMonths);
$stmtMonths->execute();
$monthList = $stmtMonths->fetchAll(PDO::FETCH_COLUMN);

$sqlMain = "
  SELECT
    hr.id,
    hr.title,
    hr.manager,
    hr.start AS start_date,
    r.name AS hdd_name,
    r.capacity AS hdd_capacity,
    hr.location AS location,
    hr.return_date,
    hr.duration
  FROM hdd_rentals hr
  JOIN hdd_resources r ON hr.resource_id = r.id
  WHERE hr.return_date IS NOT NULL
  AND hr.deleted_at IS NULL
";

if (!empty($selectedYm)) {
  list($year, $month) = explode('-', $selectedYm);
  $sqlMain .= "
    AND YEAR(hr.return_date)  = :year
    AND MONTH(hr.return_date) = :month
  ";
}
$sqlMain .= " ORDER BY hr.return_date ASC";

$stmtMain = $conn->prepare($sqlMain);

if (!empty($selectedYm)) {
  $stmtMain->bindValue(':year', (int) $year, PDO::PARAM_INT);
  $stmtMain->bindValue(':month', (int) $month, PDO::PARAM_INT);
}

$stmtMain->execute();
$billingList = $stmtMain->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<?php
$pageTitle = '料金';
include '../parts/head.php';
?>

<body>
  <?php
  $activePage = 'billing_list';
  include '../parts/nav_menu.php';
  ?>
  <main>
    <?php
    include '../parts/nav_header.php';
    ?>

    <div class="container print">
      <h2 class="sp no-print">BILLING</h2>
      <div class="header-container">
        <!-- ソートボックス -->
        <form method="get" action="" class="flex">
          <div class="custom-select-wrapper w-150px">
            <input type="text" id="billingMonthInput" name="ym" value="<?php echo htmlspecialchars($selectedYm); ?>"
              onchange="this.form.submit();">
          </div>
          <div class="no-print pc flex ml-20">
            <button onclick="window.print(); return false;" class="print-btn">印刷</button>
            <button type="button"
              onclick="window.location.href='pages/export_billing_csv.php?ym=<?php echo urlencode($selectedYm); ?>'"
              class="csv-btn">CSV出力</button>
          </div>
          <!-- テーブル検索機能 -->
          <div class="no-print table-search w-150px ml-10">
            <input type="text" id="billingTableSearchInput" placeholder="&#xf002;">
          </div>
        </form>
      </div>

      <!-- テーブル表示 -->
      <div class="billing-list table-container table-scroll">
        <table class="table-sort">
          <thead>
            <tr>
              <th onclick="sortTable(this, 0)">ID <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 1)">番組名 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 2)">担当者 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 3)">HDD No. <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 4)">容量 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 5)">使用場所 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 6)">開始日 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 7)">返却日 <i class="fa-solid fa-sort no-print"></i></th>
              <th onclick="sortTable(this, 8)">使用日数 <i class="fa-solid fa-sort no-print"></i></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($billingList as $row): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['manager']); ?></td>
                <td><?php echo htmlspecialchars($row['hdd_name']); ?></td>
                <td><?php echo htmlspecialchars($row['hdd_capacity']); ?></td>
                <td><?php echo htmlspecialchars($row['location']); ?></td>
                <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                <td><?php echo htmlspecialchars($row['return_date']); ?></td>
                <td class="text-right"><?php echo htmlspecialchars($row['duration']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script>
    document.getElementById("billingMonthInput").classList.add("flatpickr-billing-month-select");
    flatpickr("#billingMonthInput", {
      locale: "ja",
      clickOpens: false,
      plugins: [
        new monthSelectPlugin({
          shorthand: false,
          dateFormat: "Y-m",
          altFormat: "Y年n月"
        })
      ],
      defaultDate: "<?php echo htmlspecialchars($selectedYm); ?>",
      onReady: function (selectedDates, dateStr, instance) {
        instance.input.addEventListener("click", function (e) {
          if (instance.isOpen) {
            instance.close();
          } else {
            instance.open();
          }
        });
        if (!instance.input.value) {
          instance.input.value = "すべて";
        }
        // リセットボタン
        var resetBtn = document.createElement("button");
        resetBtn.className = "flatpickr-reset-button";
        resetBtn.textContent = "リセット";
        resetBtn.type = "button";
        resetBtn.addEventListener("click", function (e) {
          instance.clear();
          instance.input.value = "";
          instance.input.form.submit();
        });
        instance.calendarContainer.appendChild(resetBtn);
      },
      onChange: function (selectedDates, dateStr, instance) {
        instance.input.form.submit();
      }
    });
  </script>
</body>

</html>