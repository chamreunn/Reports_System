<?php
session_start();
// Include database connection
include('../../config/dbconn.php');

// Redirect to index page if the user is not authenticated
if (!isset($_SESSION['userid'])) {
  header('Location: ../../index.php');
  exit();
}

// Set page-specific variables
$pageTitle = "ទំព័រដើម";
$sidebar = "audit";
$userId = $_SESSION['userid']; // Assuming the user ID is stored in the session
$_SESSION['prevPageTitle'] = $pageTitle;
// Fetch user-specific data from the database if needed
// Example: Leave statistics (Replace with actual database queries)
$leaveTaken = 10; // Replace with a query to fetch the actual number of leaves taken
$leaveApproved = 5; // Replace with a query to fetch the actual number of leaves approved
$leaveRejected = 2; // Replace with a query to fetch the actual number of leaves rejected
$leaveThisWeek = 3; // Replace with a query to fetch the actual number of leaves this week

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $reportTitle = $_POST['report_title'];
  $reportDataStep1 = $_POST['report_data_step1'];
  $step = $_POST['step'];
  $userId = $_SESSION['userid'];
  $getid = $_POST['reportId'];

  // Validate form data
  $errors = [];
  if (empty($reportTitle)) {
    $errors[] = "Report title is required";
  }

  if (empty($reportDataStep1)) {
    $errors[] = "Report data is required";
  }

  // Check if there are no errors before inserting into the database
  if (empty($errors)) {
    // File upload handling
    $targetDir = "../../uploads/tblreports/";
    $attachmentStep1 = $_FILES['attachment_step1']['name'];
    $targetFilePath = $targetDir . basename($attachmentStep1);
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Allow certain file formats
    $allowedTypes = array('pdf', 'doc', 'docx');
    if (!in_array($fileType, $allowedTypes)) {
      $errors[] = "Only PDF, DOC, DOCX files are allowed.";
    } else {
      // Upload file to server
      if (move_uploaded_file($_FILES["attachment_step1"]["tmp_name"], $targetFilePath)) {
        // Insert report data into database based on step
        try {
          if ($step == 1) {
            $stmt = $dbh->prepare("INSERT INTO tblreports (user_id, report_title, Description, attachment_step1) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $reportTitle, $reportDataStep1, $targetFilePath]);
          } elseif ($step == 2) {
            $stmt = $dbh->prepare("UPDATE tblreports SET step2_approved = 0, Description2 = ?, attachment_step2 = ?, report_title = ? WHERE id = ? AND step2_approved = 0");
            $stmt->execute([$reportDataStep1, $targetFilePath, $reportTitle, $getid]);
          } elseif ($step == 3) {
            $stmt = $dbh->prepare("UPDATE tblreports SET step3_approved = 0, Description3 = ?, attachment_step3 = ?, report_title = ? WHERE id = ? AND step3_approved = 0");
            $stmt->execute([$reportDataStep1, $targetFilePath, $reportTitle, $getid]);
          }

          // Check if the query was successful
          if ($stmt->rowCount() > 0) {
            // Success message or redirect
            $msg = "Report submitted successfully.";
          } else {
            $errors[] = "Failed to submit the report. Please try again.";
          }
        } catch (PDOException $e) {
          $errors[] = "Database error: " . $e->getMessage();
        }
      } else {
        $errors[] = "There was an error uploading the file.";
      }
    }
  }
}

try {
  // Fetch reports for the current user, including pending reports
  $stmt = $dbh->prepare("
      SELECT r.*, u.Honorific, u.FirstName, u.LastName, u.RoleId, u.Profile, ro.RoleName
      FROM tblreports r
      JOIN tbluser u ON r.user_id = u.id
      JOIN tblrole ro ON u.RoleId = ro.id
      WHERE r.user_id = :userid
      AND (r.step2_approved = 0 OR r.step3_approved = 0 OR r.step3_approved = 1 OR r.completed = 1)
  ");
  $stmt->bindParam(':userid', $userId);
  $stmt->execute();
  $approvedReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Check if any data is fetched
  if (empty($approvedReports)) {
    // No approved reports found
    $message = "No approved reports found for the current user.";
  }
} catch (PDOException $e) {
  // Handle database connection error
  $message = "Database error: " . $e->getMessage();
}
// Database connection (Assuming $dbh is your database connection variable)
try {
  $sql = "SELECT RegulatorName, ShortName FROM tblregulator";
  $query = $dbh->prepare($sql);
  $query->execute();
  $regulators = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "Database error: " . $e->getMessage();
}
ob_start();
?>
<div class="row">
  <!-- single card  -->
  <div class="col-9 col-sm-12">
    <div class="card mb-4">
      <div class="card-widget-separator-wrapper">
        <div class="card-body card-widget-separator">
          <div class="row gy-4 gy-sm-1">
            <div class="col-sm-6 col-lg-3">
              <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
                <div>
                  <h3 class="mb-1"><?php echo $leaveTaken; ?></h3>
                  <p class="mb-0">Leave Taken</p>
                </div>
                <span class="badge bg-label-warning rounded p-2 me-sm-4">
                  <i class='bx bx-calendar-check bx-sm'></i>
                </span>
              </div>
              <hr class="d-none d-sm-block d-lg-none me-4">
            </div>
            <div class="col-sm-6 col-lg-3">
              <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
                <div>
                  <h3 class="mb-1"><?php echo $leaveApproved; ?></h3>
                  <p class="mb-0">Leave Approved</p>
                </div>
                <span class="badge bg-label-success rounded p-2 me-lg-4">
                  <i class='bx bx-check-double bx-sm'></i>
                </span>
              </div>
              <hr class="d-none d-sm-block d-lg-none">
            </div>
            <div class="col-sm-6 col-lg-3">
              <div class="d-flex justify-content-between align-items-start border-end pb-3 pb-sm-0 card-widget-3">
                <div>
                  <h3 class="mb-1"><?php echo $leaveRejected; ?></h3>
                  <p class="mb-0">Leave Rejected</p>
                </div>
                <span class="badge bg-label-danger rounded p-2 me-sm-4">
                  <i class='bx bx-x-circle bx-sm'></i>
                </span>
              </div>
            </div>
            <div class="col-sm-6 col-lg-3">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <h3 class="mb-1"><?php echo $leaveThisWeek; ?></h3>
                  <p class="mb-0">Leave This Week</p>
                </div>
                <span class="badge bg-label-primary rounded p-2">
                  <i class='bx bx-calendar-event bx-sm'></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-9 col-sm-12">
    <div class="card">
      <div class="card-header align-content-center d-flex justify-content-between border-bottom mb-0">
        <h4 class="card-title mb-0">Reports Status</h4>
        <!-- Button to trigger modal -->
        <!-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requeststep1"><span class="d-none d-sm-block">Create Report</span>
          <i class="bx bx-plus d-block d-sm-none"></i>
        </button> -->
        <!-- Dropdown Button -->
        <button type="button" class="btn btn-primary dropdown-toggle show" data-bs-toggle="dropdown" aria-expanded="false">
          Make A Request
        </button>
        <!-- Dropdown Menu -->
        <ul class="dropdown-menu">
          <?php if (!empty($regulators)) : ?>
            <?php foreach ($regulators as $regulator) : ?>
              <li>
                <a class="dropdown-item" href="create_reports_page?rep=<?php echo htmlentities($regulator['RegulatorName']) ?>
                &&shortname=<?php echo htmlentities($regulator['ShortName']) ?>">
                  <?php echo htmlentities($regulator['RegulatorName']); ?>
                </a>
              </li>
            <?php endforeach; ?>
          <?php else : ?>
            <li><a class="dropdown-item" href="javascript:void(0);">No regulators found</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="col-12 d-flex flex-wrap mt-3 mb-3">
        <?php if (empty($approvedReports)) : ?>
          <div class="d-flex align-items-center justify-content-center">
            <div class="text-center">
              <img src="../../assets/img/illustrations/empty-box.png" class="avatar avatar-xl mt-4" alt="">
              <h6 class="mt-4">No data available!</h6>
            </div>
          </div>

        <?php else : ?>
          <?php foreach ($approvedReports as $approvedReport) : ?>
            <div class="card col-12 col-xl-3 col-sm-12 mx-2">
              <!-- User Avatar -->
              <div class="d-flex align-items-center justify-content-center">
                <div class="avatar avatar-lg">
                  <img src="<?php echo htmlspecialchars($approvedReport['Profile']); ?>" alt="Avatar" class="rounded-circle mt-2" style="object-fit: cover;">
                </div>
              </div>
              <div class="card-body">
                <!-- User Name -->
                <h5 class="card-title text-center mef2 mb-1">
                  <?php echo htmlspecialchars($approvedReport['Honorific'] . ' ' . $approvedReport['FirstName'] . ' ' . $approvedReport['LastName']); ?>
                </h5>
                <!-- User Position -->
                <p class="card-text text-center mt-0">
                  <?php echo htmlspecialchars($approvedReport['RoleName']); ?>
                </p>

                <!-- Report Title -->
                <h5 class="card-title">
                  <?php echo htmlspecialchars($approvedReport['report_title']); ?>
                </h5>

                <!-- Approval Status -->
                <?php if ($approvedReport['completed'] == 1) : ?>
                  <p class="card-text">Step 1 Approved:<?php echo $approvedReport['id']; ?>
                    <?php echo $approvedReport['step1_approved'] ? '<span class="badge bg-label-success">Approved</span>' : '<span class="badge bg-label-warning">Pending</span>'; ?>
                  </p>
                  <!-- Step 2 Approval Status -->
                  <p class="card-text">Step 2:
                    <?php echo $approvedReport['step1_approved'] && !$approvedReport['step2_approved'] ? '<span class="badge bg-label-info">Processing</span>' : ($approvedReport['step2_approved'] ? '<span class="badge bg-label-success">Approved</span>' : '<span class="badge bg-label-warning">Pending</span>'); ?>
                  </p>
                  <!-- Step 3 Approval Status -->
                  <p class="card-text">Step 3:
                    <?php echo $approvedReport['step2_approved'] && !$approvedReport['step3_approved'] ? '<span class="badge bg-label-info">Processing</span>' : ($approvedReport['step3_approved'] ? '<span class="badge bg-label-success">Approved</span>' : '<span class="badge bg-label-warning">Pending</span>'); ?>
                  </p>
                  <p class="card-text">Attachment: <a class="text-primary fw-bold" href="<?php echo htmlspecialchars($approvedReport['attachment_step1']); ?>" target="_blank">View Document</a></p>
                  <p class="card-text">Request Date: <span class="text-primary"><?php echo htmlspecialchars(date('m-D-Y h:i A', strtotime($approvedReport['created_at']))); ?></span></p>
                  <p class="card-text">Completed Date: <span class="text-primary"><?php echo htmlspecialchars(date('m-D-Y h:i A', strtotime($approvedReport['updated_at']))); ?></span></p>
                  <p class="card-text">Description:
                    <textarea rows="4" cols="5" id="" class="form-control" disabled><?php echo htmlspecialchars($approvedReport['Description']); ?></textarea>
                  </p>
                  <a href="view_completed_report.php?id=<?php echo $approvedReport['id']; ?>" class="btn btn-label-primary w-100">View Report</a>
                <?php else : ?>
                  <!-- start condition -->

                  <!-- if has attactment but not approve to show peddning -->
                  <?php if ($approvedReport['attachment_step1']) : ?>
                    <p class="card-text">Step 1 Approved:<?php echo $approvedReport['id']; ?>
                      <?php echo $approvedReport['step1_approved'] ? '<span class="badge bg-label-success">Approved</span>' : '<span class="badge bg-label-warning">Pending</span>'; ?>
                    </p>
                    <!-- Step 2 Approval Status -->
                    <p class="card-text">Step 2:
                      <?php echo $approvedReport['step1_approved'] && !$approvedReport['step2_approved'] ? '<span class="badge bg-label-info">Processing</span>' : ($approvedReport['step2_approved'] ? '<span class="badge bg-label-success">Approved</span>' : '<span class="badge bg-label-warning">Pending</span>'); ?>
                    </p>
                    <!-- Step 3 Approval Status -->
                    <p class="card-text">Step 3:
                      <?php echo $approvedReport['step2_approved'] && !$approvedReport['step3_approved'] ? '<span class="badge bg-label-info">Processing</span>' : ($approvedReport['step3_approved'] ? '<span class="badge bg-label-success">Approved</span>' : '<span class="badge bg-label-warning">Pending</span>'); ?>
                    </p>
                    <p class="card-text">Attachment: <a class="text-primary fw-bold" href="<?php echo htmlspecialchars($approvedReport['attachment_step1']); ?>" target="_blank">View Document</a></p>
                    <p class="card-text">Request Date: <span class="text-primary"><?php echo htmlspecialchars(date('m-D-Y h:i A', strtotime($approvedReport['created_at']))); ?></span></p>
                    <p class="card-text">Description:
                      <textarea rows="4" cols="5" id="" class="form-control" disabled><?php echo htmlspecialchars($approvedReport['Description']); ?></textarea>
                    </p>
                    <!-- else if step1_approv == 1 to show edit report for step one and create request for step two -->
                    <?php if ($approvedReport['step1_approved']) : ?>
                      <!-- if it has report step 1 -->
                      <?php if (($approvedReport['report_data_step1'])) : ?>
                        <!-- what i do for step 2 -->
                        <?php if ($approvedReport['attachment_step2']) : ?>
                          <?php if ($approvedReport['step2_approved']) : ?>
                            <!-- what i'll do for step 3 -->
                            <?php if ($approvedReport['report_data_step2']) : ?>
                              <!-- what i'll do to completed -->
                              <?php if ($approvedReport['attachment_step3']) : ?>

                                <?php if ($approvedReport['step3_approved']) : ?>

                                  <a href="create_report_step3.php?id=<?php echo $approvedReport['id']; ?>" class="btn btn-primary w-100">Create Report Step 3</a>

                                <?php else : ?>
                                  <button type="button" class="btn btn-secondary w-100" disabled>Create Report Step 3</button>
                                <?php endif; ?>
                              <?php else : ?>
                                <a href="view_report_step2.php?id=<?php echo $approvedReport['id']; ?>" class="btn btn-info w-100 mb-1">Edit Report</a>
                                <button type="button" class="btn btn-secondary w-100" data-bs-toggle="modal" data-id="<?php echo $approvedReport['id']; ?>" data-bs-target="#requeststep3<?php echo $approvedReport['id']; ?>">Request Step 3</button>
                              <?php endif; ?>

                            <?php else : ?>
                              <a href="create_report_step2.php?id=<?php echo $approvedReport['id']; ?>" class="btn btn-primary w-100">Create Report Step 2</a>
                            <?php endif; ?>
                          <?php else : ?>
                            <button type="button" class="btn btn-secondary w-100" disabled>Create Report Step 2</button>
                          <?php endif; ?>
                        <?php else : ?>
                          <a href="view_report_step1.php?id=<?php echo $approvedReport['id']; ?>" class="btn btn-info w-100 mb-1">Edit Report</a>
                          <button type="button" class="btn btn-secondary w-100" data-bs-toggle="modal" data-id="<?php echo $approvedReport['id']; ?>" data-bs-target="#requeststep2<?php echo $approvedReport['id']; ?>">Request Step 2</button>
                        <?php endif; ?>

                      <?php else : ?>
                        <a href="create_report_step1.php?id=<?php echo $approvedReport['id']; ?>" class="btn btn-primary w-100">Create Report Step 1</a>
                      <?php endif; ?>

                    <?php else : ?>
                      <button type="button" class="btn btn-secondary w-100" disabled>Create Report Step 1</button>
                    <?php endif; ?>
                  <?php endif; ?>
                  <!-- end condition -->
                <?php endif; ?>
              </div>
            </div>
            <!-- modal -->

            <!-- Modal for creating a new report - Step 2 -->
            <div class="modal animate__animated animate__bounceIn" id="requeststep2<?php echo $approvedReport['id']; ?>" tabindex="-1" aria-labelledby="createReportModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <!-- Modal Header -->
                  <div class="modal-header">
                    <h5 class="modal-title mef2" id="createReportModalLabel">បង្កើតសំណើ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <!-- Modal Body -->
                  <div class="modal-body">
                    <!-- Form for creating a new report - Step 2 -->
                    <form id="createReportForm" method="post" enctype="multipart/form-data">
                      <input type="text" name="reportId" value="<?php echo $approvedReport['id']; ?>">
                      <input type="hidden" name="step" value="2">
                      <label class="form-label" for="report_title">ឈ្មោះសំណើ:</label><small class="text-danger fw-bold">*</small><br>
                      <input class="form-control" type="text" value="សេចក្តីព្រាងបឋមរបាយការណ៍សវនកម្ម" id="report_title" name="report_title" required><br>
                      <label class="form-label" for="report_data_step1">បរិយាយអំពីសំណើ:</label><br><?php echo $approvedReport['id']; ?>
                      <textarea class="form-control" id="report_data_step1" name="report_data_step1"></textarea><br>
                      <label class="form-label" for="attachment_step1">ឯកសារភ្ជាប់:</label><small class="text-danger fw-bold">*</small><br>
                      <input class="form-control" type="file" id="attachment_step1" name="attachment_step1" required><br>
                      <button class="btn btn-primary w-100" type="submit">បញ្ជូន</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal for creating a new report - Step 3 -->
            <div class="modal animate__animated animate__bounceIn" id="requeststep3<?php echo $approvedReport['id']; ?>" tabindex="-1" aria-labelledby="createReportModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <!-- Modal Header -->
                  <div class="modal-header">
                    <h5 class="modal-title mef2" i d="createReportModalLabel">បង្កើតសំណើ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <!-- Modal Body -->
                  <div class="modal-body">
                    <!-- Form for creating a new report - Step 3 -->
                    <form id="createReportForm" method="post" enctype="multipart/form-data">
                      <input type="hidden" name="reportId" value="<?php echo $approvedReport['id']; ?>">
                      <input type="hidden" name="step" value="3">
                      <label class="form-label" for="report_title">ឈ្មោះសំណើ:</label><small class="text-danger fw-bold">*</small><br>
                      <input class="form-control" type="text" value="របាយការណ៍សវនកម្ម" id="report_title" name="report_title" required><br>
                      <label class="form-label" for="report_data_step1">បរិយាយអំពីសំណើ:</label><br><?php echo $approvedReport['id']; ?>
                      <textarea class="form-control" id="report_data_step1" name="report_data_step1"></textarea><br>
                      <label class="form-label" for="attachment_step1">ឯកសារភ្ជាប់:</label><small class="text-danger fw-bold">*</small><br>
                      <input class="form-control" type="file" id="attachment_step1" name="attachment_step1" required><br>
                      <button class="btn btn-primary w-100" type="submit">បញ្ជូន</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>

          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<!-- Modal for creating a new report - Step 1 -->
<div class="modal animate__animated animate__bounceIn" id="requeststep1" tabindex="-1" aria-labelledby="createReportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title mef2" id="createReportModalLabel">បង្កើតសំណើ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">

        <form id="createReportForm" method="post" enctype="multipart/form-data">
          <input type="hidden" name="step" value="1">
          <input type="text" name="reportId" value="">
          <label class="form-label" for="report_title">ឈ្មោះសំណើ:</label><small class="text-danger fw-bold">*</small><br>
          <input class="form-control" type="text" value="សេចក្តីព្រាងរបាយការណ៍សវនកម្ម" id="report_title" name="report_title" required><br>
          <label class="form-label" for="report_data_step1">បរិយាយអំពីសំណើ:</label><br>
          <textarea class="form-control" id="report_data_step1" name="report_data_step1"></textarea><br>
          <label class="form-label" for="attachment_step1">ឯកសារភ្ជាប់:</label><small class="text-danger fw-bold">*</small><br>
          <input class="form-control" type="file" id="attachment_step1" name="attachment_step1" required><br>
          <button class="btn btn-primary w-100" type="submit">បញ្ជូន</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal for creating a new report - Step 2 -->
<!-- <div class="modal animate__animated animate__bounceIn" id="requeststep2" tabindex="-1" aria-labelledby="createReportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title mef2" id="createReportModalLabel">បង្កើតសំណើ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="createReportForm" method="post" enctype="multipart/form-data">
          <input type="hidden" name="step" value="2">
          <label class="form-label" for="report_title">ឈ្មោះសំណើ:</label><small class="text-danger fw-bold">*</small><br>
          <input class="form-control" type="text" value="សេចក្តីព្រាងបឋមរបាយការណ៍សវនកម្ម" id="report_title" name="report_title" required><br>
          <label class="form-label" for="report_data_step1">បរិយាយអំពីសំណើ:</label><br><?php echo $approvedReport['id']; ?>
          <textarea class="form-control" id="report_data_step1" name="report_data_step1"></textarea><br>
          <label class="form-label" for="attachment_step1">ឯកសារភ្ជាប់:</label><small class="text-danger fw-bold">*</small><br>
          <input class="form-control" type="file" id="attachment_step1" name="attachment_step1" required><br>
          <button class="btn btn-primary w-100" type="submit">បញ្ជូន</button>
        </form>
      </div>
    </div>
  </div>
</div> -->

<!-- Modal for creating a new report - Step 3 -->
<!-- <div class="modal animate__animated animate__bounceIn" id="requeststep3" tabindex="-1" aria-labelledby="createReportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title mef2" i d="createReportModalLabel">បង្កើតសំណើ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="createReportForm" method="post" enctype="multipart/form-data">
          <input type="hidden" name="step" value="3">
          <label class="form-label" for="report_title">ឈ្មោះសំណើ:</label><small class="text-danger fw-bold">*</small><br>
          <input class="form-control" type="text" value="របាយការណ៍សវនកម្ម" id="report_title" name="report_title" required><br>
          <label class="form-label" for="report_data_step1">បរិយាយអំពីសំណើ:</label><br><?php echo $approvedReport['id']; ?>
          <textarea class="form-control" id="report_data_step1" name="report_data_step1"></textarea><br>
          <label class="form-label" for="attachment_step1">ឯកសារភ្ជាប់:</label><small class="text-danger fw-bold">*</small><br>
          <input class="form-control" type="file" id="attachment_step1" name="attachment_step1" required><br>
          <button class="btn btn-primary w-100" type="submit">បញ្ជូន</button>
        </form>
      </div>
    </div>
  </div>
</div> -->





<?php $content = ob_get_clean(); ?>


<?php include('../../includes/layout.php'); ?>
