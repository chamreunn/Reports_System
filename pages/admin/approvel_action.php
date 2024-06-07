<?php
session_start();
require '../../config/dbconn.php';

// Redirect to index page if the user is not authenticated
if (!isset($_SESSION['userid'])) {
  header('Location: ../../index.php');
  exit();
}

$reportId = $_POST['report_id'];
$comment = $_POST['comment'];
$action = $_POST['action'];
$adminId = $_SESSION['user_id'];

// Verify the current admin is the assigned admin for this report
$stmt = $conn->prepare("SELECT admin_id, current_step FROM tblreports WHERE id = :reportId");
$stmt->bindParam(':reportId', $reportId);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$assignedAdminId = $result['admin_id'];
$currentStep = $result['current_step'];
$stmt->close();

if ($adminId != $assignedAdminId) {
  die("You are not assigned to approve this report.");
}

// Update the report based on the action being performed
if ($action == 'approve_step1' && $currentStep == 1) {
  $stmt = $conn->prepare("UPDATE reports SET approved_step1 = 1, current_step = 2, step1_comment = :comment WHERE id = :reportId");
} elseif ($action == 'approve_step2' && $currentStep == 2) {
  $stmt = $conn->prepare("UPDATE reports SET approved_step2 = 1, current_step = 3, step2_comment = :comment WHERE id = :reportId");
} elseif ($action == 'approve_step3' && $currentStep == 3) {
  $stmt = $conn->prepare("UPDATE reports SET approved_step3 = 1, step3_comment = :comment WHERE id = :reportId");
} elseif ($action == 'reject_step1' && $currentStep == 1) {
  $stmt = $conn->prepare("UPDATE reports SET step1_comment = :comment WHERE id = :reportId");
} elseif ($action == 'reject_step2' && $currentStep == 2) {
  $stmt = $conn->prepare("UPDATE reports SET step2_comment = :comment WHERE id = :reportId");
} elseif ($action == 'reject_step3' && $currentStep == 3) {
  $stmt = $conn->prepare("UPDATE reports SET step3_comment = :comment WHERE id = :reportId");
}

if (isset($stmt)) {
  $stmt->bindParam(':comment', $comment);
  $stmt->bindParam(':reportId', $reportId);
  $stmt->execute();
  echo "Action performed successfully.";
  $stmt->close();
}