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

if (!empty($existingReturnDate)) {
  // 既に返却日が設定されている場合は、ドラッグ＆ドロップ時に返却日だけを更新し、終了予定日はそのまま保持する
  // ※既存の終了予定日を取得して、新たな開始日との期間で再計算する
  $stmtExisting = $conn->prepare("SELECT end FROM hdd_rentals WHERE id = ?");
  $stmtExisting->execute([$data['id']]);
  $existingEnd = $stmtExisting->fetchColumn();
  
  $datetimeStartNew = new DateTime($data['start']);
  $datetimeExistingEnd = new DateTime($existingEnd);
  $interval = $datetimeStartNew->diff($datetimeExistingEnd);
  $newDuration = $interval->days + 1;
  
  $stmt = $conn->prepare("
    UPDATE hdd_rentals
    SET start = ?, return_date = ?, duration = ?, is_returned = 1
    WHERE id = ?
  ");
  // ドラッグ時、新たな返却日は $data['end']、終了予定日はそのまま
  $stmt->execute([$data['start'], $data['end'], $newDuration, $data['id']]);
  } else if (isset($data['return_date'])) {
  // モーダルで返却日が入力された場合はその値で更新
  $stmt = $conn->prepare("
    UPDATE hdd_rentals
    SET start = ?, return_date = ?, duration = ?, is_returned = 1
    WHERE id = ?
  ");
  $stmt->execute([$data['start'], $data['return_date'], $duration, $data['id']]);
  } else {
  // 返却日が設定されていない場合は、終了予定日を更新する
  $stmt = $conn->prepare("
    UPDATE hdd_rentals
    SET start = ?, end = ?, duration = ?
    WHERE id = ?
  ");
  $stmt->execute([$data['start'], $data['end'], $duration, $data['id']]);
}

http_response_code(200);