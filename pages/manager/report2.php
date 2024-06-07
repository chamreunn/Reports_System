<?php
// session_start(); // Start the session at the beginning
include('../../config/dbconn.php');
// Redirect to index page if the user is not authenticated
if (!isset($_SESSION['userid'])) {
    header('Location: ../../index.php');
    exit();
}

$pageTitle = "ទំព័រដើម";
$sidebar = "home";
ob_start(); // Start output buffering
include('../../config/dbconn.php');
include('../../includes/login_check.php');
include('../../controllers/form_process.php');
$getid = $_GET['id'];

// Fetch data from the tblrequests table where ID matches $getid
$stmt = $dbh->prepare("SELECT headline, data FROM tblrequests WHERE id = :id");
$stmt->bindParam(':id', $getid, PDO::PARAM_INT);
$stmt->execute();
$insertedData = $stmt->fetch(PDO::FETCH_ASSOC);

// Ensure $insertedData is not empty
if ($insertedData) {
    // Separate headlines and data
    $headlines = array_map('trim', explode(',', $insertedData['headline']));
    $data = array_map('trim', explode(',', $insertedData['data']));

    // Ensure both arrays have the same length
    $maxLength = max(count($headlines), count($data));
    $headlines = array_pad($headlines, $maxLength, '');
    $data = array_pad($data, $maxLength, '');
}
?>

<div class="row">
    <div class="col-12 mb-4">
        <form id="wizard-validation-form" method="post">
            <input type="hidden" name="login_type" value="report2">
            <input type="hidden" name="reportid" value="<?php echo htmlspecialchars($getid); ?>">
            <div class="content">
                <?php if ($insertedData): ?>
                <?php foreach ($headlines as $index => $headline): ?>
                <div class="row g-3 mb-4">
                    <div class="col-sm-12">
                        <label class="form-label"><?php echo htmlspecialchars($headline); ?></label>
                        <textarea class="form-control ckeditor" rows="10"
                            name="updatedData[]"><?php echo htmlspecialchars($data[$index]); ?></textarea>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p>No data found for the given ID.</p>
                <?php endif; ?>
                <!-- Add more fields as needed -->
            </div>
            <div class="col-12 d-flex justify-content-between">
                <button type="submit" class="btn btn-primary btn-submit">Submit</button>
            </div>
        </form>
    </div>
</div>


<?php $content = ob_get_clean(); ?>

<?php include('../../includes/layout.php'); ?>