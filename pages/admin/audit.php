<?php
session_start();
include('../../config/dbconn.php');

// Redirect to index page if the user is not authenticated
if (!isset($_SESSION['userid'])) {
    header('Location: ../../index.php');
    exit();
}
$pageTitle = "របាយការណ៍សវនកម្ម";
$sidebar = "audit";
ob_start(); // Start output buffering
include('../../config/dbconn.php');
include('../../includes/login_check.php');
include('update_request.php');
$stmt = $dbh->query('SELECT id, RegulatorName FROM tblregulator');
$regulators = $stmt->fetchAll();

// Assuming $_SESSION['userid'] holds the current user's ID
$userId = $_SESSION['userid'];

// SQL query to fetch the HeadOfUnit based on the user's RoleId
$sql = "SELECT d.HeadOfUnit
        FROM tbldepartments AS d
        JOIN tbluser AS u ON u.RoleId = d.id
        WHERE u.id = :userid";

// Prepare the query
$query = $dbh->prepare($sql);
$query->bindParam(':userid', $userId, PDO::PARAM_INT);

// Execute the query
$query->execute();

// Fetch the result
$admindepartment = $query->fetch(PDO::FETCH_ASSOC);
// Display the HeadOfUnit
if ($admindepartment) {
  $headOfUnit = $admindepartment['HeadOfUnit'];
} else {
  echo "No Head of Unit found for this user.";
}
?>
<div class="row g-4 mb-4">
    <!-- single card  -->
    <?php
  $userId = $_SESSION['userid'];

  // SQL query to count leave requests by status
  $sqlCount = "
    SELECT
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count
    FROM tblrequests
    WHERE send_to = :userId";
  $stmtCount = $dbh->prepare($sqlCount);
  $stmtCount->bindParam(':userId', $userId, PDO::PARAM_INT);
  $stmtCount->execute();
  $counts = $stmtCount->fetch(PDO::FETCH_ASSOC);

  $pendingCount = $counts['pending_count'];
  $approvedCount = $counts['approved_count'];
  $rejectedCount = $counts['rejected_count'];
  ?>

    <div class="col-12 col-sm-12">
        <div class="card mb-4">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">
                    <div class="row gy-4 gy-sm-1">
                        <div class="col-sm-6 col-lg-4">
                            <div
                                class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
                                <div>
                                    <h3 class="mb-1"><?php echo htmlspecialchars($pendingCount); ?></h3>
                                    <p class="mb-0">Pending</p>
                                </div>
                                <span class="badge bg-label-warning rounded p-2 me-sm-4">
                                    <i class='bx bx-calendar-check bx-sm'></i>
                                </span>
                            </div>
                            <hr class="d-none d-sm-block d-lg-none me-4">
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div
                                class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
                                <div>
                                    <h3 class="mb-1"><?php echo htmlspecialchars($approvedCount); ?></h3>
                                    <p class="mb-0">Approved</p>
                                </div>
                                <span class="badge bg-label-success rounded p-2 me-lg-4">
                                    <i class='bx bx-check-double bx-sm'></i>
                                </span>
                            </div>
                            <hr class="d-none d-sm-block d-lg-none">
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="d-flex justify-content-between align-items-start pb-3 pb-sm-0 card-widget-3">
                                <div>
                                    <h3 class="mb-1"><?php echo htmlspecialchars($rejectedCount); ?></h3>
                                    <p class="mb-0">Rejected</p>
                                </div>
                                <span class="badge bg-label-danger rounded p-2 me-sm-4">
                                    <i class='bx bx-x-circle bx-sm'></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <h5 class="mb-0 mef2">សំណើបង្កើតរបាយការណ៍</h5>
    <?php
  $userId = $_SESSION['userid'];
  $status = 'pending'; // Set the status to filter

  $sql = "SELECT tblrequests.*, tbluser.Firstname, tbluser.Lastname, tbluser.Profile
        FROM tblrequests
        JOIN tbluser ON tblrequests.user_id = tbluser.id
        WHERE send_to = :userId AND tblrequests.status = :status OR tblrequests.status = 'inprocess'"; // Add condition for status
  $stmt = $dbh->prepare($sql);
  $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
  $stmt->bindParam(':status', $status, PDO::PARAM_STR); // Bind status parameter
  $stmt->execute();
  $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>

    <?php if ($notifications) : ?>
    <?php foreach ($notifications as $notification) : ?>
    <div class="col-lg-4 col-md-12 col-sm-12 mb-3">
        <div class="card mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-end">
                    <span class="badge bg-label-info">
                        <?php echo htmlspecialchars($notification['status']); ?>
                    </span>
                </div>
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-4">
                        <?php if (!empty($notification['Profile'])) : ?>
                        <img src="<?php echo htmlspecialchars($notification['Profile']); ?>" class="rounded-circle"
                            style="object-fit: cover;" alt="Profile">
                        <?php else : ?>
                        <span class="avatar-initial rounded-circle bg-label-success">
                            <?php echo htmlspecialchars($notification['Firstname'][0] . $notification['Lastname'][0]); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h6 class="mb-1 mef2 mx-0"><?php echo htmlspecialchars($notification['Title']); ?></h6>
                        <p class="mb-1"><?php echo htmlspecialchars($notification['Description']); ?></p>
                        <small class="text-muted">Requested by:
                            <?php echo htmlspecialchars($notification['Firstname'] . ' ' . $notification['Lastname']); ?></small><br>
                        <small class="text-muted">Timestamp:
                            <?php echo htmlspecialchars($notification['created_at']); ?></small>
                        <?php if (!empty($notification['Document'])) : ?>
                        <br>
                        <small class="text-muted">Document: <a
                                href="<?php echo htmlspecialchars($notification['Document']); ?>" target="_blank">View
                                Document</a></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <button class="btn btn-success btn-sm approve-btn" data-request-id="<?php echo $notification['id']; ?>"
                    data-bs-toggle="modal" data-bs-target="#approveModal" data-action="approved">Approve</button>
                <button class="btn btn-danger btn-sm reject-btn mx-2"
                    data-request-id="<?php echo $notification['id']; ?>" data-bs-toggle="modal"
                    data-bs-target="#rejectModal" data-action="rejected">Reject</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else : ?>
    <div class="col-12">
        <div class="text-center">
            <img src="../../assets/img/illustrations/empty-box.png" class="avatar avatar-xl mt-4" alt="">
            <h6 class="mt-4">No notifications available!</h6>
        </div>
    </div>
    <?php endif; ?>
    <h5 class="mef2">សំណើដែលអនុម័ត និងមិនអនុម័ត</h5>
    <?php
  $userId = $_SESSION['userid'];

  $sql = "SELECT tblrequests.*, tbluser.Firstname, tbluser.Lastname, tbluser.Email, tbluser.Profile
        FROM tblrequests
        JOIN tbluser ON tblrequests.user_id = tbluser.id
        WHERE send_to = :userId AND (tblrequests.status = 'approved' OR tblrequests.status = 'approve' OR tblrequests.status = 'rejected' OR tblrequests.status = 'proccessing' OR tblrequests.status = 'completed' OR tblrequests.status = 'inprogress')";
  $stmt = $dbh->prepare($sql);
  $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
  $stmt->execute();
  $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-datatable table-responsive">
                <table id="notificationsTable" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($notifications) : ?>
                        <?php foreach ($notifications as $index => $notification) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($index + 1); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($notification['Profile'])) : ?>
                                    <img src="<?php echo htmlspecialchars($notification['Profile']); ?>"
                                        class="rounded-circle"
                                        style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;"
                                        alt="Profile">
                                    <?php else : ?>
                                    <span class="avatar-initial rounded-circle bg-label-success"
                                        style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px;">
                                        <?php echo htmlspecialchars($notification['Firstname'][0] . $notification['Lastname'][0]); ?>
                                        <?php echo htmlspecialchars($notification['Email']); ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($notification['Firstname'] . ' ' . $notification['Lastname']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($notification['Title']); ?></td>
                            <td><?php echo htmlspecialchars($notification['created_at']); ?></td>
                            <td>
                                <span
                                    class="badge <?php echo $notification['status'] == 'approved' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo htmlspecialchars($notification['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else : ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>
                                <div class="text-center">
                                    <img src="../../assets/img/illustrations/empty-box.png"
                                        class="avatar avatar-xl mt-4" alt="">
                                    <h6 class="mt-4">មិនទាន់មានទិន្ន័យ!</h6>
                                </div>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Approve Modal -->
<div class="modal animate__animated animate__bounceIn" id="approveModal" tabindex="-1"
    aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to approve this action?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmApprove">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal animate__animated animate__bounceIn" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to reject this action?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmReject">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
var confirmApproveBtn = document.getElementById('confirmApprove');
var confirmRejectBtn = document.getElementById('confirmReject');
var requestId, action;

document.querySelectorAll('.approve-btn').forEach(item => {
    item.addEventListener('click', event => {
        action = event.target.getAttribute('data-action');
        requestId = event.target.getAttribute('data-request-id');
    });
});

document.querySelectorAll('.reject-btn').forEach(item => {
    item.addEventListener('click', event => {
        action = event.target.getAttribute('data-action');
        requestId = event.target.getAttribute('data-request-id');
    });
});

confirmApproveBtn.addEventListener('click', function() {
    var formData = new FormData();
    formData.append('id', requestId);
    formData.append('action', 'approved');
    formData.append('confirm', 'yes');

    fetch('update_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                console.log('Request approved successfully');
                location.reload(); // Reload the page or update the UI after successful request
            } else {
                console.error('Failed to approve request');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
});

confirmRejectBtn.addEventListener('click', function() {
    var formData = new FormData();
    formData.append('id', requestId);
    formData.append('action', 'rejected');
    formData.append('confirm', 'yes');

    fetch('update_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                console.log('Request rejected successfully');
                location.reload(); // Reload the page or update the UI after successful request
            } else {
                console.error('Failed to reject request');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
});
</script>


<?php $content = ob_get_clean(); ?>
<?php include('../../includes/layout.php'); ?>