<?php
session_start();
include('../../config/dbconn.php');

// Redirect to index page if the user is not authenticated
if (!isset($_SESSION['userid'])) {
  header('Location: ../../index.php');
  exit();
}

$pageTitle = "ទំព័រដើម";
$sidebar = "home";
ob_start(); // Start output buffering
include('../../controllers/form_process.php');
$getid = $_GET['id'];
// Fetch data from the tblreports table where ID matches $getid
$stmt = $dbh->prepare("SELECT headline, report_data_step2 FROM tblreports WHERE id = :id");
$stmt->bindParam(':id', $getid, PDO::PARAM_INT);
$stmt->execute();
$insertedData = $stmt->fetch(PDO::FETCH_ASSOC);

// Assuming $insertedData contains the inserted data
?>

<div class="row">
    <div class="col-12 mb-4">
        <form id="wizard-validation-form" method="post">
            <input type="hidden" name="login_type" value="createreport3">
            <input type="hidden" name="reportid" value="<?php echo $getid ?>">
            <div class="content">
                <?php
                // Display each headline followed by its corresponding data paragraph in separate textareas
                $headlines = explode(',', $insertedData['headline']);
                $data = explode(',', $insertedData['report_data_step2']);
                foreach ($headlines as $index => $headline) {
                ?>
                <div class="row g-3 mb-4">
                    <div class="col-sm-12">
                        <input type="text" name="headline[]" value="<?php echo htmlspecialchars($headline); ?>"
                            class="form-control">
                        <textarea class="form-control ckeditor" rows="10"
                            name="updatedData[]"><?php echo htmlspecialchars($data[$index]); ?></textarea>
                    </div>
                </div>
                <?php } ?>
                <div class="row g-3 mb-4">
                    <div class="col-sm-12">
                        <input type="text" name="headline[]" value="១៦.បញ្ហាប្រឈម និងសំណូមពររបស់សវនករទទួលបន្ទុក"
                            class="form-control mb-3">
                        <textarea class="form-control ckeditor" rows="10" name="updatedData[]">
                          ១៦.១.បញ្ហាប្រឈម
ដោយអនុលោមទៅតាមផែនការសវនកម្មប្រចាំឆ្នាំ ប្រតិភូសវនកម្ម និងសវនករទទួលបន្ទុកបានខិតខំប្រឹងប្រែងអនុវត្តការងារសវនកម្ម រហូតទទួលបានជោគជ័យលើការរៀបចំរបាយការណ៍សវនកម្មប្រចាំឆ្នាំ២០២៣ របស់ ន.គ.ស.។ ស្របជាមួយលទ្ធផលគួរជាទីមោទនៈនេះ ប្រតិភូសវនកម្ម និងសវនករទទួលបន្ទុក ពុំមានជួបប្រទះនូវបញ្ហាប្រឈមក្នុងការអនុវត្តការងារសវនកម្មឆ្នាំ២០២៣ នេះទេ។
១៦.២.សំណូមពរ
ប្រតិភូសវនកម្ម និងសវនករទទួលបន្ទុកពុំមានសំណូមពរបន្ថែមនោះទេ។
</textarea>
                    </div>
                </div>
            </div>
            <!-- Removed the div here and placed it after the loop -->
            <div class="col-12 d-flex justify-content-between">
                <button type="submit" class="btn btn-primary btn-submit">Submit</button>
            </div>
        </form>
    </div>
</div>

<?php $content = ob_get_clean(); ?>

<?php include('../../includes/layout.php'); ?>
