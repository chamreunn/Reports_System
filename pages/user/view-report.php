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

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

$getid = $_GET['id'];

// Fetch data from the tblrequests table where ID matches $getid
$stmt = $dbh->prepare("SELECT headline, data FROM tblrequests WHERE id = :id");
$stmt->bindParam(':id', $getid, PDO::PARAM_INT);
$stmt->execute();
$insertedData = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12 mb-4">
        <form id="wizard-validation-form" method="post">
            <input type="hidden" name="login_type" value="end-report">
            <input type="hidden" name="reportid" value="<?php echo $getid ?>">
            <div class="content">
                <?php
                // Display each headline followed by its corresponding data paragraph in separate textareas
                if ($insertedData) {
                    $headlines = explode(',', $insertedData['headline']);
                    $data = explode(',', $insertedData['data']);
                    foreach ($headlines as $index => $headline) {
                ?>
                <div class="row g-3 mb-4">
                    <div class="col-sm-12">
                        <label class="form-label"><?php echo htmlspecialchars($headline); ?></label>
                        <textarea style="font-family: khmer mef1;" class="form-control ckeditor" rows="10" cols="50"
                            name="updatedData[]"><?php echo htmlspecialchars($data[$index]); ?></textarea>
                    </div>
                </div>
                <?php
                    }
                } else {
                    echo "<p>No data found for the given ID.</p>";
                }
                ?>
            </div>
            <div class="col-12 d-flex justify-content-between">
                <button type="submit" class="btn btn-primary btn-submit">Submit</button>
                <?php if ($insertedData) : ?>
                <a href="export.php?id=<?php echo $getid; ?>" class="btn btn-success">Export to Word</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include('../../includes/layout.php'); ?>