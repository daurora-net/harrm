<?php
include '../common/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['rentalTitle'] ?? '');
  $manager = trim($_POST['rentalManager'] ?? '');
  $start = $_POST['rentalStart'] ?? null;
  $end = $_POST['rentalEnd'] ?? null;
  $hddId = $_POST['rentalHdd'] ?? null;
  $location = $_POST['rentalLocation'] ?? null;
  $cable = $_POST['rentalCable'] ?? null;
  $duration = $_POST['rentalDuration'] ?? null;
  $notes = trim($_POST['rentalNotes'] ?? '');
  $returnDate = $_POST['returnDate'] ?? null;
  $created_by = $_SESSION['username'] ?? 'unknown';

  // 返却日が未入力ならNULL
  if (empty($returnDate)) {
    $returnDate = null;
  }

  // 必須項目のバリデーション
  if ($title && $manager && $hddId) {
    try {
      $overlapSql = "
        SELECT COUNT(*) 
        FROM hdd_rentals
        WHERE deleted_at IS NULL
          AND resource_id = ?
          AND NOT (end < ? OR start > ?)
      ";
      $stmtOverlap = $conn->prepare($overlapSql);
      $stmtOverlap->execute([
        $hddId,
        $start,
        $end
      ]);
      $countOverlap = $stmtOverlap->fetchColumn();

      if ($countOverlap > 0) {
        echo "⚠️ 設定し直してください！期間またはHDDが既存の予約と重複しています";
        exit;
      }

      // 返却日があるかどうかで is_returned を設定
      $isReturned = !empty($returnDate) ? 1 : 0;

      $stmt = $conn->prepare("
        INSERT INTO hdd_rentals
          (title, manager, start, end, resource_id,
           location, cable, duration, notes, return_date,
           is_returned, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
      ");
      $stmt->execute([
        $title,
        $manager,
        $start,
        $end,
        $hddId,
        $location,
        $cable,
        $duration,
        $notes,
        $returnDate,
        $isReturned,
        $created_by
      ]);

      $rentalId = $conn->lastInsertId();

      echo "OK";
      exit;

    } catch (PDOException $e) {
      error_log("レンタル追加エラー: " . $e->getMessage());
      echo "エラーが発生しました。管理者に連絡してください。";
      exit();
    }
  } else {
    echo "必要な項目が入力されていません。";
    exit();
  }
}
?>