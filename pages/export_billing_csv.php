<?php
include '../common/db.php';

$selectedYm = isset($_GET['ym']) ? $_GET['ym'] : '';

$sqlMain = "
  SELECT
    hr.id,
    hr.title,
    hr.manager,
    r.name AS hdd_name,
    r.capacity AS hdd_capacity,
    hr.location AS location,
    hr.start AS start_date,
    hr.return_date,
    hr.duration
  FROM hdd_rentals hr
  JOIN hdd_resources r ON hr.resource_id = r.id
  WHERE hr.return_date IS NOT NULL
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

// CSV出力用ヘッダー
header('Content-Type: text/csv; charset=UTF-8');
$filename = 'HDD_billing_list';
if (!empty($selectedYm)) {
  $filename .= '_' . str_replace('-', '_', $selectedYm);
}
header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

// 出力ストリーム
$output = fopen('php://output', 'w');
// Excelでの文字化け対策
fwrite($output, "\xEF\xBB\xBF");

// ヘッダ行の書き出し
fputcsv($output, ['番組名', '担当者', 'HDD No.', 'HDD容量', '使用場所', '開始日', '返却日', '使用日数']);

// 各行の書き出し
foreach ($billingList as $row) {
  fputcsv($output, [
    $row['title'],
    $row['manager'],
    $row['hdd_name'],
    $row['hdd_capacity'],
    $row['location'],
    $row['start_date'],
    $row['return_date'],
    $row['duration']
  ]);
}

fclose($output);
exit();
?>