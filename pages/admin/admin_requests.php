<?php

function approveRequest($requestId, $adminId)
{
  global $dbh;

  // Update request status
  $sql = "UPDATE tblrequest SET status='approved', approved_by=:admin_id, approved_at=NOW() WHERE id=:request_id";
  $query = $dbh->prepare($sql);
  $query->bindParam(':admin_id', $adminId);
  $query->bindParam(':request_id', $requestId);
  $query->execute();

  // Notify user
  $sql = "SELECT user_id FROM tblrequest WHERE id=:request_id";
  $query = $dbh->prepare($sql);
  $query->bindParam(':request_id', $requestId);
  $query->execute();
  $userId = $query->fetchColumn();

  $notificationMessage = "Your request has been approved.";
  $sqlNotification = "INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)";
  $queryNotification = $dbh->prepare($sqlNotification);
  $queryNotification->bindParam(':user_id', $userId);
  $queryNotification->bindParam(':message', $notificationMessage);
  $queryNotification->execute();
}

function rejectRequest($requestId, $adminId)
{
  global $dbh;

  // Update request status
  $sql = "UPDATE tblrequest SET status='rejected', rejected_at=NOW() WHERE id=:request_id";
  $query = $dbh->prepare($sql);
  $query->bindParam(':request_id', $requestId);
  $query->execute();

  // Notify user
  $sql = "SELECT user_id FROM tblrequest WHERE id=:request_id";
  $query = $dbh->prepare($sql);
  $query->bindParam(':request_id', $requestId);
  $query->execute();
  $userId = $query->fetchColumn();

  $notificationMessage = "Your request has been rejected.";
  $sqlNotification = "INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)";
  $queryNotification = $dbh->prepare($sqlNotification);
  $queryNotification->bindParam(':user_id', $userId);
  $queryNotification->bindParam(':message', $notificationMessage);
  $queryNotification->execute();
}
