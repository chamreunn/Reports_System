<?php
session_start();
include('../../config/dbconn.php');

// Redirect to index page if the user is not authenticated
if (!isset($_SESSION['userid'])) {
  header('Location: ../../index.php');
  exit();
}

$pageTitle = "Admin Dashboard";
$sidebar = "home";

// Check for status messages
$notification = $_GET['id'] ?? null;


// Update notification to "read"
if ($notification) {
  try {
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = :notification_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':notification_id', $notification);
    $stmt->execute();
  } catch (PDOException $e) {
    // Handle error
    echo "Error updating notification: " . $e->getMessage();
  }
}

ob_start(); // Start output buffering
include('../../config/dbconn.php');
include('../../includes/login_check.php');
?>
<!-- Your HTML content continues here -->

<div class="row">
  <!-- Request details -->
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <?php
        // Retrieve request details based on notification ID
        $requestSql = "SELECT * FROM tblrequest WHERE id = :request_id";
        $requestStmt = $dbh->prepare($requestSql);
        $requestStmt->bindParam(':request_id', $notification);
        $requestStmt->execute();
        $requestDetails = $requestStmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <h2>Request Details</h2>
        <p>Request ID: <?php echo $requestDetails['id']; ?></p>
        <!-- Display other request details as needed -->

        <!-- Buttons for approving and rejecting the request -->
        <form method="post">
          <input type="hidden" name="request_id" value="<?php echo $requestDetails['id']; ?>">
          <button class="btn btn-success" type="submit" name="approve_request">Approve</button>
          <button class="btn btn-danger" type="submit" name="reject_request">Reject</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include('../../includes/layout.php'); ?>
