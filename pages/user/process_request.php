<?php
session_start();
include('../../config/dbconn.php');

if (!isset($_SESSION['userid'])) {
  header('Location: ../../index.php');
  exit();
}
// translate
include('../../includes/translate.php');

$userId = $_SESSION['userid'];
$shortname = $_POST['shortname'];
$regulator = $_POST['regulator'];
$requestName = $_POST['request_name'];
$description = $_POST['description'];
$step = 1; // Initial step

try {
  // Insert request into the tblrequest table
  $sql = "INSERT INTO tblrequest (user_id, shortname, Regulator, request_name_1, description_1, step, status)
  VALUES (:user_id, :shortname, :regulator, :request_name, :description, :step, 'pending')";
  $query = $dbh->prepare($sql);
  $query->bindParam(':user_id', $userId);
  $query->bindParam(':shortname', $shortname);
  $query->bindParam(':regulator', $regulator);
  $query->bindParam(':request_name', $requestName);
  $query->bindParam(':description', $description);
  $query->bindParam(':step', $step);
  $query->execute();

  $requestId = $dbh->lastInsertId();

  // Handle file uploads
  if (!empty($_FILES['files']['name'][0])) {
    foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
      $fileName = uniqid() . '_' . basename($_FILES['files']['name'][$key]);
      $filePath = '../../uploads/' . $fileName;
      move_uploaded_file($tmpName, $filePath);

      // Insert file path into tblrequest_attachments
      $sql = "INSERT INTO tblrequest_attachments (request_id, file_path) VALUES (:request_id, :file_path)";
      $query = $dbh->prepare($sql);
      $query->bindParam(':request_id', $requestId);
      $query->bindParam(':file_path', $filePath);
      $query->execute();
    }
  }

  sleep(1);
  $msg = urlencode(translate("Request submitted successfully."));
  header("Location: dashboard.php?status=success&msg=" . $msg);
  exit();
} catch (PDOException $e) {
  // Set error message
  $error = "Error submitting request: " . $e->getMessage();
}
