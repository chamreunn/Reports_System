<?php
include('../../config/dbconn.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);

  if (isset($input['request_id'], $input['index'], $input['headline'], $input['data'])) {
    $requestId = $input['request_id'];
    $index = $input['index'];
    $headline = $input['headline'];
    $data = $input['data'];

    // Fetch existing data
    $stmt = $dbh->prepare("SELECT headline, data FROM tblreport_step1 WHERE request_id = :id");
    $stmt->bindParam(':id', $requestId, PDO::PARAM_INT);
    $stmt->execute();
    $existingData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingData) {
      $existingHeadlines = explode("\n", trim($existingData['headline']));
      $existingDataLines = explode("\n", trim($existingData['data']));

      // Update the specific index
      $existingHeadlines[$index] = $headline;
      $existingDataLines[$index] = $data;

      // Join the arrays back to strings
      $newHeadlines = implode("\n", $existingHeadlines);
      $newDataLines = implode("\n", $existingDataLines);

      // Update the database
      $updateStmt = $dbh->prepare("UPDATE tblreport_step1 SET headline = :headline, data = :data WHERE request_id = :id");
      $updateStmt->bindParam(':headline', $newHeadlines);
      $updateStmt->bindParam(':data', $newDataLines);
      $updateStmt->bindParam(':id', $requestId, PDO::PARAM_INT);
      $success = $updateStmt->execute();

      if ($success) {
        echo json_encode(['success' => true]);
      } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update data.']);
      }
    } else {
      echo json_encode(['success' => false, 'message' => 'Data not found.']);
    }
  } else {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
