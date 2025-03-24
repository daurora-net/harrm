<?php
include '../common/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data['start']) && !empty($data['end'])) {
  $datetimeStart = new DateTime($data['start']);
  $datetimeEnd = new DateTime($data['end']);
  $interval = $datetimeStart->diff($datetimeEnd);
  // （同日を 1 日と数える場合）
  $duration = $interval->days + 1;
} else {
  $duration = null;
}

$checkStmt = $conn->prepare("
  SELECT return_date
  FROM hdd_rentals
  WHERE id = ?
");
$checkStmt->execute([$data['id']]);
$existingReturnDate = $checkStmt->fetchColumn();

if ($existingReturnDate) {
  // すでに返却日あり → 終了予定日は変更せず、返却日だけ更新
  $stmt = $conn->prepare("
    UPDATE hdd_rentals
    SET start = ?, return_date = ?, duration = ?, is_returned = 1
    WHERE id = ?
  ");
  $stmt->execute([$data['start'], $data['end'], $duration, $data['id']]);
} else {
  // 返却日がなければ 通常通り end を更新
  $stmt = $conn->prepare("
    UPDATE hdd_rentals
    SET start = ?, end = ?, duration = ?
    WHERE id = ?
  ");
  $stmt->execute([$data['start'], $data['end'], $duration, $data['id']]);
}

http_response_code(200);