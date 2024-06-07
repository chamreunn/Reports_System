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
include('../../config/dbconn.php');
include('../../includes/login_check.php');
include('../../controllers/form_process.php');
$getrequestid = $_GET['id'];
$stmt = $dbh->prepare("SELECT headline, data FROM form_data LIMIT 14"); // Limit to 14 records
$stmt->execute();
$form_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="row">
    <div class="col-12 mb-4">
        <small class="text-light fw-medium">Validation</small>
        <div id="wizard-validation" class="bs-stepper mt-2 linear">
            <div class="bs-stepper-header p-1">
                <?php foreach ($form_data as $index => $step) : ?>
                <?php
                        $active_class = ($index == 0) ? 'active' : '';
                        $completed_class = ($index > 0) ? 'completed' : '';
                        ?>
                <div class="step <?= $active_class ?> <?= $completed_class ?>" data-target="#stepContent<?= $index ?>">
                    <button type="button" class="step-trigger" aria-selected="<?= ($index == 0 ? 'true' : 'false') ?>">
                        <span class="bs-stepper-circle"><?= ($index + 1) ?></span>
                        <div class="d-block d-md-none"><?= htmlspecialchars($step['headline']) ?></div>
                    </button>
                </div>
                <?php if ($index < count($form_data) - 1) : ?>
                <div class="line">
                    <i class="bx bx-chevron-right d-none"></i>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="bs-stepper-content">
                <form id="wizard-validation-form" method="post">
                    <input type="hidden" name="login_type" value="insert_report">
                    <input type="hidden" name="userid" value="<?php echo $_SESSION['userid'] ?>">
                    <input type="hidden" name="requestid" value="<?php echo $getrequestid ?>">
                    <?php foreach ($form_data as $index => $step) : ?>
                    <?php
                            $active_class = ($index == 0) ? 'active' : '';
                            ?>
                    <div id="stepContent<?= $index ?>" class="content <?= $active_class ?>">
                        <div class="content-header mb-3">
                            <input type="text" name="headline<?= $index ?>" class="form-control mef2 mb-0"
                                value="<?= htmlspecialchars($step['headline']) ?>" />
                            <small>Enter Your <?= htmlspecialchars($step['headline']) ?>.</small>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-12">
                                <label class="form-label" for="formValidationTextarea<?= $index ?>">Textarea</label>
                                <textarea class="form-control ckeditor" id="formValidationTextarea<?= $index ?>"
                                    name="formValidationTextarea<?= $index ?>"><?= htmlspecialchars($step['data']) ?></textarea>
                            </div>
                        </div>
                        <?php if ($index == count($form_data) - 1) : ?>
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-label-primary btn-prev" onclick="prevStep(<?= $index ?>)">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                <span class="align-middle d-sm-inline-block d-none">Previous</span>
                            </button>
                            <button class="btn btn-primary btn-submit">Submit</button>
                        </div>
                        <?php else : ?>
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-label-primary btn-prev" onclick="prevStep(<?= $index ?>)">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                <span class="align-middle d-sm-inline-block d-none">Previous</span>
                            </button>
                            <button type="button" class="btn btn-primary btn-next" onclick="nextStep(<?= $index ?>)">
                                <span class="align-middle d-sm-inline-block d-none me-sm-1">Next</span>
                                <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
function nextStep(currentIndex) {
    var currentStep = document.getElementById('stepContent' + currentIndex);
    var nextStep = document.getElementById('stepContent' + (currentIndex + 1));
    var currentHead = document.querySelector('.step.active');
    var nextHead = document.querySelector('.step[data-target="#stepContent' + (currentIndex + 1) + '"]');
    var prevHead = document.querySelector('.step[data-target="#stepContent' + currentIndex + '"]');

    if (nextStep && nextHead) {
        currentStep.classList.remove('active');
        nextStep.classList.add('active');
        currentHead.classList.remove('active');
        currentHead.classList.add('completed');
        nextHead.classList.add('active');
        prevHead.classList.add('crossed');
        enableDisableButtons(currentIndex + 1);

        // If next step is the summary step, display the summary
        if (currentIndex + 1 === <?= count($form_data) - 1; ?>) {
            displaySummary();
            // Change the button text and onclick event for the last step
            document.querySelector('.btn-next').innerText = 'Submit';
            document.querySelector('.btn-next').setAttribute('onclick', 'submitForm()');
        }
    }
}

function prevStep(currentIndex) {
    var currentStep = document.getElementById('stepContent' + currentIndex);
    var prevStep = document.getElementById('stepContent' + (currentIndex - 1));
    var currentHead = document.querySelector('.step.active');
    var prevHead = document.querySelector('.step[data-target="#stepContent' + (currentIndex - 1) + '"]');
    var nextHead = document.querySelector('.step[data-target="#stepContent' + currentIndex + '"]');

    if (prevStep && prevHead) {
        currentStep.classList.remove('active');
        prevStep.classList.add('active');
        currentHead.classList.remove('active');
        currentHead.classList.remove('completed');
        prevHead.classList.add('active');
        nextHead.classList.remove('crossed');
        enableDisableButtons(currentIndex - 1);
    }
}

function enableDisableButtons(currentIndex) {
    var prevButton = document.querySelector('.btn-prev');
    var nextButton = document.querySelector('.btn-next');
    if (currentIndex === 0) {
        prevButton.setAttribute('disabled', 'disabled');
    } else {
        prevButton.removeAttribute('disabled');
    }
    if (currentIndex === <?= count($form_data) - 1; ?>) {
        nextButton.setAttribute('disabled', 'disabled');
    } else {
        nextButton.removeAttribute('disabled');
    }
}
</script>


<?php $content = ob_get_clean(); ?>

<?php include('../../includes/layout.php'); ?>