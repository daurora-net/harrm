<?php
include '../common/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $eventId = $_POST['eventId'] ?? null;

  if (isset($_POST['delete']) && $_POST['delete'] == '1') {
    try {
      $stmt = $conn->prepare("UPDATE hdd_rentals
                              SET deleted_at = NOW()
                              WHERE id = ?");
      $stmt->execute([$eventId]);

      echo "OK";
      exit;

    } catch (PDOException $e) {
      error_log("レンタル削除エラー: " . $e->getMessage());
      echo "エラーが発生しました。";
      exit();
    }

  } else {
    $title = trim($_POST['eventTitle'] ?? '');
    $manager = trim($_POST['eventManager'] ?? '');
    $start = $_POST['eventStart'] ?? null;
    $end = $_POST['eventEnd'] ?? null;
    $resource_id = $_POST['rentalHdd'] ?? null;
    $location = $_POST['rentalLocation'] ?? null;
    $cable = $_POST['rentalCable'] ?? null;
    $returnDate = $_POST['returnDate'] ?? null;
    $isReturned = !empty($returnDate) ? 1 : 0;
    $duration = $_POST['rentalDuration'] ?? null;
    $notes = trim($_POST['eventNotes'] ?? '');
    $updated_by = $_SESSION['username'] ?? 'unknown';

    if (empty($eventId) || !$title || !$manager || !$resource_id) {
      echo "必要な項目が入力されていません。";
      exit();
    }

    if (empty($returnDate)) {
      $returnDate = null;
    }

    // overlap条件: NOT (既存.end < 新.start OR 既存.start > 新.end)
    // → (既存.start <= 新.end) AND (既存.end >= 新.start) で重複
    $overlapSql = "
      SELECT COUNT(*) 
      FROM hdd_rentals
      WHERE deleted_at IS NULL
        AND resource_id = ?
        AND NOT (end < ? OR start > ?)
        AND id <> ?
    ";
    try {
      $stmtOverlap = $conn->prepare($overlapSql);
      $stmtOverlap->execute([$resource_id, $start, $end, $eventId]);
      $countOverlap = $stmtOverlap->fetchColumn();

      if ($countOverlap > 0) {
        echo "⚠️ 設定し直してください！期間またはHDDが既存の予約と重複しています";
        exit;
      }
    } catch (PDOException $e) {
      error_log("オーバーラップチェックエラー: " . $e->getMessage());
      echo "エラーが発生しました。";
      exit();
    }

    try {
      $stmt = $conn->prepare("UPDATE hdd_rentals 
                              SET title = ?, manager = ?, start = ?, end = ?, resource_id = ?,
                                  location = ?, cable = ?, is_returned = ?, return_date = ?,
                                  duration = ?, notes = ?, updated_by = ?
                              WHERE id = ?");
      $stmt->execute([
        $title,
        $manager,
        $start,
        $end,
        $resource_id,
        $location,
        $cable,
        $isReturned,
        $returnDate,
        $duration,
        $notes,
        $updated_by,
        $eventId
      ]);

      echo "OK";
      exit;
    } catch (PDOException $e) {
      error_log("レンタル編集エラー: " . $e->getMessage());
      echo "エラーが発生しました。";
      exit();
    }
  }
}