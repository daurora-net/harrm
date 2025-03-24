<?php
include '../common/db.php';

$sql = "
  SELECT DISTINCT DATE_FORMAT(start, '%Y-%m') AS ym
  FROM hdd_rentals
  WHERE deleted_at IS NULL
  ORDER BY ym ASC
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$months = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($months);
?>