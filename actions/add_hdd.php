<?php
include '../common/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $hddName = isset($_POST['hddName']) ? $_POST['hddName'] : null;
  $hddCapacity = isset($_POST['hddCapacity']) ? $_POST['hddCapacity'] : null;
  $hddNotes = isset($_POST['hddNotes']) ? $_POST['hddNotes'] : null;

  if ($hddName !== null && $hddNotes !== null) {
    try {
      $stmt = $conn->prepare("INSERT INTO hdd_resources (name, capacity, notes) VALUES (?, ?, ?)");
      $stmt->execute([$hddName, $hddCapacity, $hddNotes]);

      header("Location: ../hdd_list");
      exit();
    } catch (PDOException $e) {
      error_log("HDD追加エラー: " . $e->getMessage());
      echo "エラーが発生しました。管理者に連絡してください。";
    }
  } else {
    echo "必要なデータが送信されていません。";
  }
}
?>