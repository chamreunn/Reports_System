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
include('../../controllers/form_process.php');

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

// Fetch notifications for the current user
$userId = $_SESSION['userid'];

$sql = "SELECT tblrequests.*, tbluser.Firstname, tbluser.Lastname, tbluser.Profile
        FROM tblrequests
        JOIN tbluser ON tblrequests.user_id = tbluser.id
        WHERE tblrequests.user_id = :userId";
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sqlPendingCount = "SELECT COUNT(*) AS pending_count FROM tblrequests WHERE status = 'Pending'";
$stmtPendingCount = $dbh->prepare($sqlPendingCount);
$stmtPendingCount->execute();
$pendingCount = $stmtPendingCount->fetch(PDO::FETCH_ASSOC)['pending_count'];

$sqlApprovedCount = "SELECT COUNT(*) AS approved_count FROM tblrequests WHERE status = 'Approved'";
$stmtApprovedCount = $dbh->prepare($sqlApprovedCount);
$stmtApprovedCount->execute();
$approvedCount = $stmtApprovedCount->fetch(PDO::FETCH_ASSOC)['approved_count'];

$sqlRejectedCount = "SELECT COUNT(*) AS rejected_count FROM tblrequests WHERE status = 'Rejected'";
$stmtRejectedCount = $dbh->prepare($sqlRejectedCount);
$stmtRejectedCount->execute();
$rejectedCount = $stmtRejectedCount->fetch(PDO::FETCH_ASSOC)['rejected_count'];
?>

<div class="row">
    <!-- single card  -->
    <div class="col-12 col-sm-12">
        <div class="card mb-4">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">
                    <div class="row gy-4 gy-sm-1">
                        <div class="col-sm-6 col-lg-4">
                            <div
                                class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-3 pb-sm-0">
                                <div>
                                    <h3 class="mb-1"><?php echo $pendingCount; ?></h3>
                                    <p class="mb-0" data-i18n="Pending">Pending Status</p>
                                </div>
                                <span class="badge bg-label-warning rounded p-2 me-sm-4"
                                    data-i18n="<i class='bx bx-calendar-check bx-sm'></i>"></span>
                            </div>
                            <hr class="d-none d-sm-block d-lg-none me-4">
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div
                                class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-3 pb-sm-0">
                                <div>
                                    <h3 class="mb-1"><?php echo $approvedCount; ?></h3>
                                    <p class="mb-0" data-i18n="Approved">Approved Status</p>
                                </div>
                                <span class="badge bg-label-success rounded p-2 me-lg-4"
                                    data-i18n="<i class='bx bx-check-double bx-sm'></i>"></span>
                            </div>
                            <hr class="d-none d-sm-block d-lg-none">
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="d-flex justify-content-between align-items-start pb-3 pb-sm-0 card-widget-3">
                                <div>
                                    <h3 class="mb-1"><?php echo $rejectedCount; ?></h3>
                                    <p class="mb-0" data-i18n="Rejected">Rejected Status</p>
                                </div>
                                <span class="badge bg-label-danger rounded p-2 me-sm-4"
                                    data-i18n="<i class='bx bx-x-circle bx-sm'></i>"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- end sigle card -->
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="mb-0 mef2">របាយការណ៍សវនកម្ម</h4>
                <!-- Button trigger modal -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#onboardHorizontalImageModal">បង្កើតសំណើ <i class="bx bx-list-plus me-0 mx-2"></i>
                </button>
            </div>
        </div>
        <h6 class="mef2 mt-3">សំណើបង្កើតរបាយការណ៍</h6>
        <div class="col-md-12 mt-5 mt-md-0">
            <div class="added-cards">
                <?php if ($requests) : ?>
                <?php foreach ($requests as $request) : ?>
                <div class="cardMaster bg-lighter rounded-2 p-3 mb-3">
                    <div class="d-flex justify-content-between flex-sm-row flex-column">
                        <div class="card-information me-2">
                            <?php if (!empty($request['Profile'])) : ?>
                            <img class="mb-3 img-fluid avatar avatar-md rounded-circle"
                                src="<?php echo htmlentities($request['Profile']); ?>" alt="Profile" width="100"
                                height="100" style="object-fit: cover;">
                            <?php else : ?>
                            <span
                                class="avatar-initial rounded-circle bg-label-success"><?php echo htmlspecialchars($request['Firstname'][0] . $request['Lastname'][0]); ?></span>
                            <?php endif; ?>
                            <div class="d-flex align-items-center mb-1 flex-wrap gap-2">
                                <h6 class="mb-0 me-2">
                                    <?php echo htmlspecialchars($request['Title']); ?>
                                </h6>
                                <?php if ($request['status'] == 'Primary') : ?>
                                <span
                                    class="badge bg-label-primary"><?php echo htmlspecialchars($request['status']); ?></span>
                                <?php elseif ($request['status'] == 'approved') : ?>
                                <span
                                    class="badge bg-label-success"><?php echo htmlspecialchars($request['status']); ?></span>
                                <?php else : ?>
                                <span
                                    class="badge bg-label-info"><?php echo htmlspecialchars($request['status']); ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo htmlspecialchars($request['Document']); ?>" target="_blank"
                                class="card-number">Document: View Document</a>
                        </div>
                        <div class="d-flex flex-column text-start text-sm-end">
                            <div class="d-flex order-sm-0 order-1 w-100 mt-sm-0 mt-3">
                                <?php if ($request['status'] == 'approved') : ?>
                                <a href="report.php?id=<?php echo $request['id'] ?>"
                                    class="btn btn-label-primary me-2">Create Report</a>
                                <?php endif; ?>
                            </div>
                            <small class="mt-sm-auto mt-2 order-sm-1 order-0">Created At:
                                <?php echo htmlspecialchars($request['created_at']); ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else : ?>
                <div class="text-center">
                    <img src="../../assets/img/illustrations/empty-box.png" class="avatar avatar-xl mt-4" alt="">
                    <h6 class="mt-4">No requests found!</h6>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
  $userId = $_SESSION['userid'];

  $sql = "SELECT tblrequests.*, tbluser.Firstname, tbluser.Lastname, tbluser.Email, tbluser.Profile
        FROM tblrequests
        JOIN tbluser ON tblrequests.user_id = tbluser.id
        WHERE tblrequests.user_id = :userId";
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
                            <th>Status</th> <!-- Column for status -->
                            <th>Action</th>
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
                                    </span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($notification['Firstname'] . ' ' . $notification['Lastname']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($notification['Title']); ?></td>
                            <td><?php echo htmlspecialchars($notification['created_at']); ?></td>
                            <td>
                                <?php
                    $status = htmlspecialchars($notification['status']);
                    $statusClass = '';
                    switch ($status) {
                      case 'pending':
                        $statusClass = 'bg-label-primary';
                        break;
                      case 'rejected':
                        $statusClass = 'bg-label-danger';
                        break;
                      case 'approved':
                        $statusClass = 'bg-label-success';
                        break;
                      case 'processing':
                        $statusClass = 'bg-label-info';
                        break;
                      default:
                        $statusClass = 'bg-label-secondary';
                        break;
                    }
                    ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo $status; ?></span>
                            </td>
                            <td>
                                <?php if ($status === 'approved') : ?>
                                <a href="report2.php?id=<?php echo htmlspecialchars($notification['id']); ?>"
                                    class="btn btn-icon"></i> Continue <i class="bx bx-chevron-right"></i></a>
                                <?php elseif ($status == 'pending') : ?>
                                <a href="report-edit.php?id=<?php echo htmlspecialchars($notification['id']); ?>"
                                    class="btn btn-icon"><i class="bx bx-edit-alt"></i></a>
                                <?php elseif ($status == 'approve') : ?>
                                <a href="end-report.php?id=<?php echo htmlspecialchars($notification['id']); ?>"
                                    class="btn btn-icon">Continue <i class="bx bx-chevron-right"></i></a>
                                <?php elseif ($status == 'completed') : ?>
                                <a href="view-report.php?id=<?php echo htmlspecialchars($notification['id']); ?>"
                                    class="btn btn-icon"><i class="bx bx-show"></i></a>

                                <?php elseif ($status == 'inprogress') : ?>
                                <a href="report-edit.php?id=<?php echo htmlspecialchars($notification['id']); ?>"
                                    class="btn btn-icon"><i class="bx bx-edit-alt"></i></a>
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-id="<?php echo htmlspecialchars($notification['id']) ?>"
                                    data-bs-target="#requestfinall<?php echo $index; ?>"><i
                                        class="bx bx-message"></i>Request</button>
                                <?php else : ?>
                                <a href="report-edit.php?id=<?php echo htmlspecialchars($notification['id']); ?>"
                                    class="btn btn-icon"><i class="bx bx-edit-alt"></i></a>
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-id="<?php echo htmlspecialchars($notification['id']) ?>"
                                    data-bs-target="#requestModal<?php echo $index; ?>"><i
                                        class="bx bx-message"></i>Request</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <div class="modal-onboarding modal animate__animated animate__bounceIn"
                            id="requestfinall<?php echo $index; ?>" tabindex="-1" aria-labelledby="requestModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                                <div class="modal-content text-center">
                                    <div class="modal-header border-0">
                                        <a class="text-muted close-label" href="javascript:void(0);"
                                            data-bs-dismiss="modal">Skip
                                            Intro</a>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close">
                                        </button>
                                    </div>
                                    <form id="formAuthentication" class="row g-3 mb-3" method="POST"
                                        enctype="multipart/form-data">
                                        <input type="hidden" name="login_type" value="requestfinall">
                                        <input type="hidden" name="userId" value="<?php echo $_SESSION['userid'] ?>">
                                        <input type="hidden" name="headofunit" value="<?php echo $headOfUnit ?>">
                                        <input type="hidden" name="requestid"
                                            value="<?php echo htmlspecialchars($notification['id']) ?>">
                                        <div class="modal-body onboarding-horizontal p-0">
                                            <div class="onboarding-media">
                                                <img src="../../assets/img/illustrations/boy-verify-email-light.png"
                                                    alt="boy-verify-email-light" width="273" class="img-fluid"
                                                    data-app-dark-img="illustrations/boy-verify-email-dark.png"
                                                    data-app-light-img="illustrations/boy-verify-email-light.png">
                                            </div>
                                            <div class="onboarding-content mb-0">
                                                <h4 class="onboarding-title text-body mef2 fw-bold text-center">
                                                    សំណើសុំបង្កើតរបាយការណ៍សវនកម្ម
                                                </h4>
                                                <div class="onboarding-info alert alert-warning mb-3 text-center">
                                                    ក្នុងការដាក់សំណើបង្កើតសេចក្តីព្រាងបឋមរបាយការណ៍សវនកម្ម
                                                    សវនករត្រូវបំពេញមតិស្នើសុំ
                                                    និងត្រូវបញ្ចូលរបាយការណ៍លទ្ធផលដែលបានរកឃើញដើម្បីដាក់បញ្ជូនទៅកាន់
                                                    <span class="mef2 fw-bloder">ឯកឧត្តមប្រធាន</span> ក្នុងការពិនិត្យ
                                                    និងអនុម័តបន្ត។
                                                </div>
                                                <div class="row text-start">
                                                    <div class="col-sm-12">
                                                        <div class="mb-3">
                                                            <label for="formValidationName" data-i18n="Request Comment"
                                                                class="form-label text-start">Request Comment</label>
                                                            <textarea class="form-control" id="formValidationName"
                                                                rows="4" name="formValidationName" cols="4"
                                                                aria-label="With textarea" placeholder="Comment"
                                                                required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 mb-3">
                                                        <label for="inputGroupFile01" data-i18n="Documents"
                                                            class="form-label text-start">Documents</label>
                                                        <span class="text-danger fw-bloder">*</span>
                                                        <div class="input-group">
                                                            <input type="file" name="document" class="form-control"
                                                                id="inputGroupFile01" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-label-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button class="btn btn-primary" data-i18n="Submit"
                                                type="submit">Submit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Request Modal -->
                        <div class="modal-onboarding modal animate__animated animate__bounceIn"
                            id="requestModal<?php echo $index; ?>" tabindex="-1" aria-labelledby="requestModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                                <div class="modal-content text-center">
                                    <div class="modal-header border-0">
                                        <a class="text-muted close-label" href="javascript:void(0);"
                                            data-bs-dismiss="modal">Skip
                                            Intro</a>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close">
                                        </button>
                                    </div>
                                    <form id="formAuthentication" class="row g-3 mb-3" method="POST"
                                        enctype="multipart/form-data">
                                        <input type="hidden" name="login_type" value="requests2">
                                        <input type="hidden" name="userId" value="<?php echo $_SESSION['userid'] ?>">
                                        <input type="hidden" name="headofunit" value="<?php echo $headOfUnit ?>">
                                        <input type="hidden" name="requestid"
                                            value="<?php echo htmlspecialchars($notification['id']) ?>">
                                        <div class="modal-body onboarding-horizontal p-0">
                                            <div class="onboarding-media">
                                                <img src="../../assets/img/illustrations/boy-verify-email-light.png"
                                                    alt="boy-verify-email-light" width="273" class="img-fluid"
                                                    data-app-dark-img="illustrations/boy-verify-email-dark.png"
                                                    data-app-light-img="illustrations/boy-verify-email-light.png">
                                            </div>
                                            <div class="onboarding-content mb-0">
                                                <h4 class="onboarding-title text-body mef2 fw-bold text-center">
                                                    សំណើសុំបង្កើតសេចក្តីព្រាងបឋមរបាយការណ៍
                                                </h4>
                                                <div class="onboarding-info alert alert-warning mb-3 text-center">
                                                    ក្នុងការដាក់សំណើបង្កើតសេចក្តីព្រាងបឋមរបាយការណ៍សវនកម្ម
                                                    សវនករត្រូវបំពេញមតិស្នើសុំ
                                                    និងត្រូវបញ្ចូលរបាយការណ៍លទ្ធផលដែលបានរកឃើញដើម្បីដាក់បញ្ជូនទៅកាន់
                                                    <span class="mef2 fw-bloder">ឯកឧត្តមប្រធាន</span> ក្នុងការពិនិត្យ
                                                    និងអនុម័តបន្ត។
                                                </div>
                                                <div class="row text-start">
                                                    <div class="col-sm-12">
                                                        <div class="mb-3">
                                                            <label for="formValidationName" data-i18n="Request Comment"
                                                                class="form-label text-start">Request Comment</label>
                                                            <textarea class="form-control" id="formValidationName"
                                                                rows="4" name="formValidationName" cols="4"
                                                                aria-label="With textarea" placeholder="Comment"
                                                                required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 mb-3">
                                                        <label for="inputGroupFile01" data-i18n="Documents"
                                                            class="form-label text-start">Documents</label>
                                                        <span class="text-danger fw-bloder">*</span>
                                                        <div class="input-group">
                                                            <input type="file" name="document" class="form-control"
                                                                id="inputGroupFile01" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-label-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                            <button class="btn btn-primary" data-i18n="Submit"
                                                type="submit">Submit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Request Modal -->
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
                            <td></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal-onboarding modal fade animate__animated" data-bs-backdrop="static"
        id="onboardHorizontalImageModal" tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content text-center">
                <div class="modal-header border-0">
                    <a class="text-muted close-label" href="javascript:void(0);" data-bs-dismiss="modal">Skip
                        Intro</a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <form id="formAuthentication" class="row g-3 mb-3" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="login_type" value="requests">
                    <input type="hidden" name="userId" value="<?php echo $_SESSION['userid'] ?>">
                    <input type="hidden" name="headofunit" value="<?php echo $headOfUnit ?>">
                    <div class="modal-body onboarding-horizontal p-0">
                        <div class="onboarding-media">
                            <img src="../../assets/img/illustrations/boy-verify-email-light.png"
                                alt="boy-verify-email-light" width="273" class="img-fluid"
                                data-app-dark-img="illustrations/boy-verify-email-dark.png"
                                data-app-light-img="illustrations/boy-verify-email-light.png">
                        </div>
                        <div class="onboarding-content mb-0">
                            <h4 class="onboarding-title text-body mef2 fw-bold text-center">
                                សំណើសុំបង្កើតសេចក្តីព្រាងរបាយការណ៍
                            </h4>
                            <div class="onboarding-info alert alert-warning mb-3 text-center">
                                ក្នុងការដាក់សំណើបង្កើតសេចក្តីព្រាងរបាយការណ៍សវនកម្ម
                                សវនករត្រូវបំពេញមតិស្នើសុំ
                                និងត្រូវបញ្ចូលរបាយការណ៍លទ្ធផលដែលបានរកឃើញដើម្បីដាក់បញ្ជូនទៅកាន់
                                <span class="mef2 fw-bloder">ឯកឧត្តមប្រធាន</span> ក្នុងការពិនិត្យ និងអនុម័តបន្ត។
                            </div>
                            <div class="row">

                                <div class="col-sm-12">
                                    <div class="mb-3">
                                        <label for="formValidationName" data-i18n="Request Comment"
                                            class="form-label">Request Comment</label>
                                        <textarea class="form-control" id="formValidationName" rows="4"
                                            name="formValidationName" cols="4" aria-label="With textarea"
                                            placeholder="Comment" required></textarea>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="inputGroupFile01" data-i18n="Documents"
                                        class="form-label">Documents</label>
                                    <span class="text-danger fw-bloder">*</span>
                                    <div class="input-group">
                                        <input type="file" name="document" class="form-control" id="inputGroupFile01"
                                            required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary" data-i18n="Submit" type="submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- end modal -->
</div>
<!-- <?php
      // Fetch data from the form_insert table
      $user_id = $_SESSION['userid'];

      $stmt = $dbh->prepare("SELECT headline, data FROM form_insert WHERE user_id = :user_id ");
      $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->execute();
      $form_data = $stmt->fetch(PDO::FETCH_ASSOC);

      // Split the headline and data strings into arrays
      $headlines = explode(',', $form_data['headline']);
      $dataSections = explode(',', $form_data['data']);
      ?>
    <div class="card">
        <div id="data-content" class="data-content">
            <?php foreach ($headlines as $index => $headline) : ?>
            <h4><?php echo htmlspecialchars($headline); ?></h4>
            <p><?php echo nl2br(htmlspecialchars($dataSections[$index])); ?></p>
            <?php endforeach; ?>
        </div>
    </div>
</div> -->
<?php $content = ob_get_clean(); ?>
<?php include('../../includes/layout.php'); ?>