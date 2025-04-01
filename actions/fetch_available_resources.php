<?php
include '../common/db.php';

// GETパラメータから現在編集中のレンタルIDを取得（無ければ0）
$currentRentalId = isset($_GET['current_rental_id']) ? (int) $_GET['current_rental_id'] : 0;
$startParam = isset($_GET['start']) ? $_GET['start'] : '';
$endParam = isset($_GET['end']) ? $_GET['end'] : '';

// 現在編集中レンタルのリソースID（deleted_at IS NULL）
$sqlRes = "
  SELECT resource_id 
  FROM hdd_rentals 
  WHERE id = ? 
    AND deleted_at IS NULL
";
$stmtRes = $conn->prepare($sqlRes);
$stmtRes->execute([$currentRentalId]);
$resRow = $stmtRes->fetch(PDO::FETCH_ASSOC);
$currentResourceId = $resRow ? (int) $resRow['resource_id'] : 0;

// start/end が指定されていれば、日時重複チェックで除外
if (!empty($startParam) && !empty($endParam)) {
  $query = "
    SELECT r.id, r.name
    FROM hdd_resources r
    WHERE r.deleted_at IS NULL
      AND (
        r.id = :currentResourceId
        OR r.id NOT IN (
          SELECT resource_id
          FROM hdd_rentals
          WHERE deleted_at IS NULL
            AND is_returned = 0
            AND NOT (end < :start OR start > :end)
        )
      )
  ";
  $stmt = $conn->prepare($query);
  $stmt->bindValue(':currentResourceId', $currentResourceId, PDO::PARAM_INT);
  $stmt->bindValue(':start', $startParam, PDO::PARAM_STR);
  $stmt->bindValue(':end', $endParam, PDO::PARAM_STR);
  $stmt->execute();
} else {
  $query = "SELECT r.id, r.name FROM hdd_resources r WHERE r.deleted_at IS NULL";
  $stmt = $conn->prepare($query);
  $stmt->execute();
}

$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($resources);