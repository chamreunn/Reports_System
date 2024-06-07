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
$status = $_GET['status'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Get the form data
  $action = $_POST['action'];
  $reportId = $_POST['report_id'];
  $userId = $_SESSION['userid'];

  try {
    // Check if the report exists and get its current status
    $stmt = $dbh->prepare("SELECT * FROM tblreports WHERE id = :report_id");
    $stmt->bindParam(':report_id', $reportId, PDO::PARAM_INT);
    $stmt->execute();
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($report) {
      if ($action == 'approve_step1') {
        // Approve Step 1
        $stmt = $dbh->prepare("UPDATE tblreports SET step1_approved = 1, step2_approved = 0, step3_approved = 0 WHERE id = :report_id");
        $stmt->bindParam(':report_id', $reportId, PDO::PARAM_INT);
        $stmt->execute();
      } elseif ($action == 'request_step2') {
        // Request Step 2
        $attachmentStep2 = $_FILES['attachment_step2']['name'];
        $targetDir = "../../uploads/tblreports/";
        $targetFilePath = $targetDir . basename($attachmentStep2);
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        $allowedTypes = array('pdf', 'doc', 'docx');
        if (in_array($fileType, $allowedTypes)) {
          if (move_uploaded_file($_FILES["attachment_step2"]["tmp_name"], $targetFilePath)) {
            $stmt = $dbh->prepare("UPDATE tblreports SET attachment_step2 = :attachment, step2_approved = 0 WHERE id = :report_id");
            $stmt->bindParam(':attachment', $targetFilePath);
            $stmt->bindParam(':report_id', $reportId, PDO::PARAM_INT);
            $stmt->execute();
          }
        }
      } elseif ($action == 'approve_step2') {
        // Approve Step 2
        $stmt = $dbh->prepare("UPDATE tblreports SET step2_approved = 1, step3_approved = 0 WHERE id = :report_id");
        $stmt->bindParam(':report_id', $reportId, PDO::PARAM_INT);
        $stmt->execute();
      } elseif ($action == 'request_step3') {
        // Request Step 3
        $attachmentStep3 = $_FILES['attachment_step3']['name'];
        $targetDir = "../../uploads/tblreports/";
        $targetFilePath = $targetDir . basename($attachmentStep3);
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        $allowedTypes = array('pdf', 'doc', 'docx');
        if (in_array($fileType, $allowedTypes)) {
          if (move_uploaded_file($_FILES["attachment_step3"]["tmp_name"], $targetFilePath)) {
            $stmt = $dbh->prepare("UPDATE tblreports SET attachment_step3 = :attachment, step3_approved = 0 WHERE id = :report_id");
            $stmt->bindParam(':attachment', $targetFilePath);
            $stmt->bindParam(':report_id', $reportId, PDO::PARAM_INT);
            $stmt->execute();
          }
        }
      } elseif ($action == 'approve_step3') {
        // Approve Step 3
        $stmt = $dbh->prepare("UPDATE tblreports SET step3_approved = 1 WHERE id = :report_id");
        $stmt->bindParam(':report_id', $reportId, PDO::PARAM_INT);
        $stmt->execute();
      }
    }
  } catch (PDOException $e) {
    // Handle database connection error
    $message = "Database error: " . $e->getMessage();
  }
}


// Fetch all reports for the admin to review
try {
  $stmt = $dbh->prepare("
      SELECT r.*, u.Honorific, u.FirstName, u.LastName, u.RoleId, u.Profile, ro.RoleName
      FROM tblreports r
      JOIN tbluser u ON r.user_id = u.id
      JOIN tblrole ro ON u.RoleId = ro.id
  ");
  $stmt->execute();
  $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Fetch action history
  $historyStmt = $dbh->prepare("
    SELECT a.*, u.Honorific, u.FirstName, u.LastName, r.report_title, ro.RoleName
    FROM tblreport_actions a
    JOIN tbluser u ON a.admin_id = u.id
    JOIN tblreports r ON a.report_id = r.id
    JOIN tblrole ro ON u.RoleId = ro.id
    ORDER BY a.action_time DESC
    ");
  $historyStmt->execute();
  $actions = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $message = "Database error: " . $e->getMessage();
}


ob_start(); // Start output buffering
include('../../config/dbconn.php');
include('../../includes/login_check.php');
?>
<!-- Your HTML content continues here -->

<div class="row">
    <!-- Dashboard content -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h2 class="">Admin Dashboard</h2>
                <?php if ($status === 'success') : ?>
                <div class="alert alert-success" role="alert">Action completed successfully and the user has been
                    notified.</div>
                <?php elseif ($status === 'error') : ?>
                <div class="alert alert-danger" role="alert">An error occurred while processing the action.</div>
                <?php endif; ?>
                <div class="d-flex flex-wrap">
                    <?php foreach ($reports as $report) : ?>
                    <div class="border rounded-3 shadow-sm m-2" style="width: 18rem;">
                        <!-- User Avatar -->
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="avatar avatar-lg">
                                <img src="<?php echo htmlspecialchars($report['Profile']); ?>" alt="Avatar"
                                    class="rounded-circle mt-2" style="object-fit: cover;">
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- User Name -->
                            <h5 class="card-title text-center mef2 mb-1">
                                <?php echo htmlspecialchars($report['Honorific'] . ' ' . $report['FirstName'] . ' ' . $report['LastName']); ?>
                            </h5>
                            <!-- User Position -->
                            <p class="card-text text-center mt-0"><?php echo htmlspecialchars($report['RoleName']); ?>
                            </p>

                            <!-- Report Title -->
                            <h5 class="card-title"><?php echo htmlspecialchars($report['report_title']); ?></h5>

                            <!-- Step 1 Approval Status -->
                            <p class="card-text">Step 1 Approved:
                                <?php echo $report['step1_approved'] ? '<span class="badge bg-label-success">Approved</span>' : '<span class="badge bg-label-warning">Pending</span>'; ?>
                            </p>
                            <!-- Step 2 Approval Status -->
                            <p class="card-text">Step 2:
                                <?php echo $report['step1_approved'] && !$report['step2_approved'] ? '<span class="badge bg-label-info">Processing</span>' : ($report['step2_approved'] ? '<span class="badge bg-label-success">Approved</span>' : '<span class="badge bg-label-warning">Pending</span>'); ?>
                            </p>
                            <!-- Step 3 Approval Status -->
                            <p class="card-text">Step 3:
                                <?php echo $report['step2_approved'] && !$report['step3_approved'] ? '<span class="badge bg-label-info">Processing</span>' : ($report['step3_approved'] ? '<span class="badge bg-label-success">Approved</span>' : '<span class="badge bg-label-warning">Pending</span>'); ?>
                            </p>
                            <!-- Attachment Link -->
                            <?php if (!empty($report['attachment_step1'])) : ?>
                            <p class="card-text">Attachment: <a class="text-primary fw-bold"
                                    href="<?php echo htmlspecialchars($report['attachment_step1']); ?>"
                                    target="_blank">View Document</a></p>
                            <?php endif; ?>
                            <!-- Request Date -->
                            <p class="card-text">Request Date:
                                <?php echo date('d-M-Y', strtotime($report['created_at'])); ?></p>

                            <!-- Form for approval/rejection Step 1 -->
                            <form method="post">
                                <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                <?php if (!$report['step1_approved']) : ?>
                                <div class="d-flex w-100 align-items-center justify-content-start">
                                    <button class="btn btn-sm btn-success me-2" type="submit" name="action"
                                        value="approve_step1">Approve Step 1</button>
                                    <button class="btn btn-sm btn-danger" type="button" data-bs-toggle="modal"
                                        data-bs-target="#rejectModal<?php echo $report['id']; ?>">Reject Step 1</button>
                                </div>
                                <?php endif; ?>
                            </form>

                            <!-- Form for approval/rejection Step 2 -->
                            <?php if ($report['step1_approved'] && !$report['step2_approved'] && $report['attachment_step2']) : ?>
                            <form method="post">
                                <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                <div class="d-flex w-100 align-items-center justify-content-start mt-2">
                                    <button class="btn btn-sm btn-success me-2" type="submit" name="action"
                                        value="approve_step2">Approve Step 2</button>
                                    <button class="btn btn-sm btn-danger" type="button" data-bs-toggle="modal"
                                        data-bs-target="#rejectModalStep2<?php echo $report['id']; ?>">Reject Step
                                        2</button>
                                </div>
                            </form>
                            <?php elseif ($report['step1_approved'] && !$report['step2_approved']) : ?>
                            <p class="card-text text-warning">Awaiting attachment for Step 2...</p>
                            <?php endif; ?>

                            <!-- Form for approval/rejection Step 3 -->
                            <?php if ($report['step2_approved'] && !$report['step3_approved'] && $report['attachment_step3']) : ?>
                            <form method="post">
                                <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                <div class="d-flex w-100 align-items-center justify-content-start mt-2">
                                    <button class="btn btn-sm btn-success me-2" type="submit" name="action"
                                        value="approve_step3">Approve Step 3</button>
                                    <button class="btn btn-sm btn-danger" type="button" data-bs-toggle="modal"
                                        data-bs-target="#rejectModalStep3<?php echo $report['id']; ?>">Reject Step
                                        3</button>
                                </div>
                            </form>
                            <?php elseif ($report['step2_approved'] && !$report['step3_approved']) : ?>
                            <p class="card-text text-warning">Awaiting attachment for Step 3...</p>
                            <?php endif; ?>

                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Rejection Step 1 -->
<?php foreach ($reports as $report) : ?>
<div class="modal animate__animated animate__bounceIn" id="rejectModal<?php echo $report['id']; ?>" tabindex="-1"
    aria-labelledby="rejectModalLabel<?php echo $report['id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel<?php echo $report['id']; ?>">Reject Step 1</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                    <input type="hidden" name="reject_step1" value="">
                    <label class="form-label" for="comment">Comment:</label>
                    <textarea class="form-control" id="comment" name="comment" required></textarea>
                    <button class="btn btn-primary w-100 mt-3" type="submit">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Modal for Rejection Step 2 -->
<?php foreach ($reports as $report) : ?>
<div class="modal animate__animated animate__bounceIn" id="rejectModalStep2<?php echo $report['id']; ?>" tabindex="-1"
    aria-labelledby="rejectModalLabelStep2<?php echo $report['id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabelStep2<?php echo $report['id']; ?>">Reject Step 2</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                    <input type="hidden" name="reject_step2" value="1">
                    <label class="form-label" for="comment">Comment:</label>
                    <textarea class="form-control" id="comment" name="comment" required></textarea>
                    <button class="btn btn-primary w-100 mt-3" type="submit">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Modal for Rejection Step 3 -->
<?php foreach ($reports as $report) : ?>
<div class="modal animate__animated animate__bounceIn" id="rejectModalStep3<?php echo $report['id']; ?>" tabindex="-1"
    aria-labelledby="rejectModalLabelStep3<?php echo $report['id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabelStep3<?php echo $report['id']; ?>">Reject Step 3</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                    <input type="hidden" name="reject_step3" value="1">
                    <label class="form-label" for="comment">Comment:</label>
                    <textarea class="form-control" id="comment" name="comment" required></textarea>
                    <button class="btn btn-primary w-100 mt-3" type="submit">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Action History Table -->
<div class="row mt-4">
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="">Action History</h2>
                    <div class="card-datatable table-responsive">
                        <table id="notificationsTable" class="dt-responsive table border-top table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Report Title</th>
                                    <th scope="col">Admin</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Action</th>
                                    <th scope="col">Comment</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($actions as $action) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($action['report_title']); ?></td>
                                    <td><?php echo htmlspecialchars($action['Honorific'] . ' ' . $action['FirstName'] . ' ' . $action['LastName']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($action['RoleName']); ?></td>
                                    <td><?php echo htmlspecialchars($action['action_type']); ?></td>
                                    <td><?php echo isset($action['comment']) ? htmlspecialchars($action['comment']) : ''; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($action['action_time'])); ?></td>
                                    <td><?php echo date('H:i:s', strtotime($action['action_time'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include('../../includes/layout.php'); ?>