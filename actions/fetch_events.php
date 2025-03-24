<?php
include '../common/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
  $stmt = $conn->prepare("
        SELECT
          hr.id, hr.title, hr.manager,
          hr.start, hr.end,
          hr.location, hr.cable,
          hr.is_returned, hr.return_date,
          hr.duration, hr.notes,
          hr.resource_id
        FROM hdd_rentals hr
        WHERE hr.deleted_at IS NULL
    ");
  $stmt->execute();
  $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $formatted_events = [];

  foreach ($events as $event) {
    // DB上の終了予定日
    $dbEnd = $event['end'];
    // DBに保存されている返却日
    $dbReturn = $event['return_date'];

    // 「返却日が入力されていれば返却日優先、返却日が無ければ終了予定日」
    $displayEnd = !empty($dbReturn) ? $dbReturn : $dbEnd;

    // FullCalendarはall-dayイベントを「endの前日まで」塗りつぶすため+1日する
    $calendarEnd = null;
    if (!empty($displayEnd)) {
      $calendarEnd = date('Y-m-d', strtotime($displayEnd . ' +1 day'));
    }

    $formatted_events[] = [
      'id' => $event['id'],
      'title' => $event['title'],
      'start' => $event['start'],
      'end' => $calendarEnd,
      'resourceId' => $event['resource_id'],

      'extendedProps' => [
        'manager' => $event['manager'],
        'is_returned' => (int) $event['is_returned'],
        'return_date' => $dbReturn,
        'location' => $event['location'],
        'cable' => $event['cable'],
        'notes' => $event['notes'],
        'duration' => $event['duration'],

        'real_end' => $dbEnd
      ]
    ];
  }

  echo json_encode($formatted_events);

} catch (PDOException $e) {
  error_log("fetch_events エラー: " . $e->getMessage());
  echo json_encode([]);
}