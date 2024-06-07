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
// Fetch data from the form_insert table where ID matches $getid
// Retrieve the inserted data from the form_insert table
$stmt = $dbh->prepare("SELECT headline, report_data_step1 FROM tblreports WHERE id = :id");
$stmt->bindParam(':id', $getid, PDO::PARAM_INT);
$stmt->execute();
$insertedData = $stmt->fetch(PDO::FETCH_ASSOC);

// Assuming $insertedData contains the inserted data
?>

<div class="row">
    <div class="col-12 mb-4">
        <form id="wizard-validation-form" method="post">
            <input type="hidden" name="login_type" value="createreport2">
            <input type="hidden" name="reportid" value="<?php echo $getid ?>">
            <div class="content">
                <?php
                // Display each headline followed by its corresponding data paragraph in separate textareas
                $headlines = explode(',', $insertedData['headline']);
                $data = explode(',', $insertedData['report_data_step1']);
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
                        <input type="text" name="headline[]" value="១៥.ការតាមដានការអនុវត្តអនុសាសន៍"
                            class="form-control mb-3">
                        <textarea class="form-control ckeditor" rows="10"
                            name="updatedData[]">ក្នុងគោលបំណងធ្វើឱ្យប្រសើរឡើងនូវប្រព័ន្ធត្រួតពិនិត្យផ្ទៃក្នុងរបស់ ន.គ.ស. សវនករទទួលបន្ទុកបានផ្តល់អនុសាសន៍សវនកម្មមួយចំនួនក្នុងរបាយការណ៍សវនកម្មអនុលោមភាពនេះ។ អង្គភាពសវនកម្មផ្ទៃក្នុងនៃ អ.ស.ហ. នឹងធ្វើការតាមដានលើវឌ្ឍនភាពនៃការអនុវត្តតាមអនុសាសន៍ដែលបានផ្តល់ជូន ន.គ.ស. ខាងលើនៅគ្រាបន្ទាប់។</textarea>
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
