<?php
session_start();
include('../../config/dbconn.php');

if (!isset($_SESSION['userid'])) {
  header('Location: ../../index.php');
  exit();
}

$userId = $_SESSION['userid'];

$stmt = $dbh->prepare("SELECT message FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($notifications);
