<?php
// Include database connection
include('../../config/dbconn.php');

// Fetch ongoing requests from the database along with their attachments
$ongoingRequests = [];
try {
  $sql = "SELECT r.*, ra.file_path
          FROM tblrequest r
          LEFT JOIN tblrequest_attachments ra ON r.id = ra.request_id
          WHERE r.status != 'completed'";
  $stmt = $dbh->prepare($sql);
  $stmt->execute();
  $ongoingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "Database error: " . $e->getMessage();
}

// Fetch completed requests from the database
$completedRequests = [];
try {
  $sql = "SELECT r.*, ra.file_path
          FROM tblrequest r
          LEFT JOIN tblrequest_attachments ra ON r.id = ra.request_id
          WHERE r.status = 'completed'";
  $stmt = $dbh->prepare($sql);
  $stmt->execute();
  $completedRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "Database error: " . $e->getMessage();
}

// Function to format date
function formatDate($date)
{
  // Implement your date formatting logic here
}

// Generate HTML for ongoing requests
$ongoingRequestsHTML = '';
foreach ($ongoingRequests as $request) {
  $ongoingRequestsHTML .= '<div class="col-md-4 mb-4">';
  $ongoingRequestsHTML .= '<div class="card">';
  // Add card body content here
  $ongoingRequestsHTML .= '</div>';
  $ongoingRequestsHTML .= '</div>';
}

// Generate HTML for completed requests
$completedRequestsHTML = '';
foreach ($completedRequests as $request) {
  $completedRequestsHTML .= '<tr>';
  // Add table row content here
  $completedRequestsHTML .= '</tr>';
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode([
  'ongoingRequests' => $ongoingRequestsHTML,
  'completedRequests' => $completedRequestsHTML
]);
