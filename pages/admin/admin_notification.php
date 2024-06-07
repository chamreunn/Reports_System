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
include('../../controllers/form_process.php');

function getTimeDifference($created_at) {
  $now = new DateTime();
  $created = new DateTime($created_at);
  $diff = $now->diff($created);

  if ($diff->y > 0 || $diff->m > 0 || $diff->d > 0) {
      return $diff->d . ' ថ្ងៃមុន';
  } elseif ($diff->h > 0) {
      return $diff->h . ' ម៉ោងមុន';
  } elseif ($diff->i > 0) {
      return $diff->i . ' នាទីមុន';
  } else {
      return 'ថ្មីៗនេះ';
  }
}

// Fetch all requests
$sql = "SELECT tblrequests.*, tbluser.Firstname, tbluser.Lastname, tbluser.Profile
      FROM tblrequests
      JOIN tbluser ON tblrequests.user_id = tbluser.id
      ORDER BY tblrequests.created_at DESC";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group requests
$groupedRequests = [
  'ថ្មីៗនេះ' => [],
  'នាទីមុន' => [],
  'ម៉ោងមុន' => [],
  'ថ្ងៃមុន' => [],
];

foreach ($requests as $request) {
  $timeDiff = getTimeDifference($request['created_at']);

  if (strpos($timeDiff, 'ថ្មីៗនេះ') !== false) {
      $groupedRequests['ថ្មីៗនេះ'][] = $request;
  } elseif (strpos($timeDiff, 'នាទីមុន') !== false) {
      $groupedRequests['នាទីមុន'][] = $request;
  } elseif (strpos($timeDiff, 'ម៉ោងមុន') !== false) {
      $groupedRequests['ម៉ោងមុន'][] = $request;
  } else {
      $groupedRequests['ថ្ងៃមុន'][] = $request;
  }
}
?>

<div class="row">
    <!-- Single card -->
    <div class="col-12 col-sm-12">
        <div class="card mb-4">
            <div class="card-header border-bottom mb-0">
                <div class="row">
                    <div class="col-xl-6 col-sm-12">
                        <h5 class="mb-0 mef2">
                            <i class="bx bx-bell me-2 p-2 bg-label-primary rounded-circle mb-0"></i>សារជូនដំណឹង
                        </h5>
                    </div>
                    <div class="col-xl-6 col-sm-12 d-flex justify-content-end">
                        <select id="filterSelect" data-style="btn-default"
                            class="form-select selectpicker mx-auto form-select-sm">
                            <option value="all">ទាំងអស់</option>
                            <option value="ថ្មីៗនេះ">ថ្មីៗនេះ</option>
                            <option value="នាទីមុន">នាទីមុន</option>
                            <option value="ម៉ោងមុន">ម៉ោងមុន</option>
                            <option value="ថ្ងៃមុន">ថ្ងៃមុន</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php
                $hasRequests = false;
                foreach ($groupedRequests as $group =>$requests):
                  if (count($requests) > 0):
                      $hasRequests = true;
              ?>
                <h6 class="mb-3 mt-3"><?php echo htmlspecialchars($group); ?></h6>
                <ul class="list-group <?php echo htmlspecialchars($group); ?>-list">
                    <?php foreach ($requests as $request): ?>
                    <li
                        class="list-group-item list-group-item-action d-flex align-items-center border-bottom cursor-pointer">
                        <?php if (!empty($request['Profile'])): ?>
                        <img src="<?php echo htmlspecialchars($request['Profile']); ?>" alt="Profile"
                            class="rounded-circle me-3" width="50" height="50" style="object-fit: cover;">
                        <?php else: ?>
                        <span
                            class="avatar-initial rounded-circle bg-label-success"><?php echo htmlspecialchars($request['Firstname'][0] . $request['Lastname'][0]); ?></span>
                        <?php endif; ?>
                        <div class="w-100">
                            <div class="d-flex justify-content-between">
                                <div class="user-info">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($request['Title']); ?></h6>
                                    <p class="mb-0"><?php echo htmlspecialchars($request['message']); ?></p>
                                    <small
                                        class="text-muted"><?php echo htmlspecialchars($request['Firstname'] . ' ' . $request['Lastname']); ?></small>
                                </div>
                                <div class="add-btn">
                                    <span
                                        class="badge bg-info"><?php echo htmlspecialchars($request['status']); ?></span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">Created At:
                                    <?php echo htmlspecialchars($request['created_at']); ?></small><br>
                                <?php if (!empty($request['Document'])): ?>
                                <small class="text-muted">Document: <a
                                        href="<?php echo htmlspecialchars($request['Document']); ?>"
                                        target="_blank">View Document</a></small>
                                <?php endif; ?>
                            </div>
                            <div class="mt-3">
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-success me-2">យល់ព្រម</button>
                                    <button type="button" class="btn btn-danger">បដិសេធ</button>
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php
                  endif;
              endforeach;
              if (!$hasRequests): ?>
                <div class="text-center no-requests">
                    <img src="../../assets/img/illustrations/empty-box.png" class="avatar avatar-xl mt-4" alt="">
                    <h6 class="mt-4">No requests found!</h6>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('filterSelect').addEventListener('change', function() {
    var selectedValue = this.value;
    var allLists = document.querySelectorAll('.list-group');
    var hasVisibleRequests = false;

    allLists.forEach(function(list) {
        if (selectedValue === 'all') {
            list.style.display = 'block';
            hasVisibleRequests = true;
        } else {
            if (list.classList.contains(selectedValue + '-list')) {
                list.style.display = 'block';
                hasVisibleRequests = true;
            } else {
                list.style.display = 'none';
            }
        }
    });

    var noRequestsElement = document.querySelector('.no-requests');
    if (hasVisibleRequests) {
        noRequestsElement.style.display = 'none';
    } else {
        noRequestsElement.style.display = 'block';
    }
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include('../../includes/layout.php'); ?>