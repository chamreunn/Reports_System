<?php
session_start();
include('../../config/dbconn.php');

$userId = $_SESSION['userid'];

$query = $dbh->prepare("SELECT * FROM tblrequest WHERE user_id = :user_id");
$query->bindParam(':user_id', $userId);
$query->execute();
$requests = $query->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($requests);
?>
