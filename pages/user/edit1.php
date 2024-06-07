<?php
session_start();
// Include database connection
include('../../config/dbconn.php');

// Redirect to the index page if the user is not authenticated
if (!isset($_SESSION['userid'])) {
  header('Location: ../../index.php');
  exit();
}

$pageTitle = "ទំព័រដើម";
$sidebar = "home";
ob_start(); // Start output buffering
// include('../../controllers/form_process.php');
$getid = $_GET['id'];

// Fetch data from the tblreport_step1 table where ID matches $getid
$stmt = $dbh->prepare("SELECT headline, data FROM tblreport_step1 WHERE request_id = :id");
$stmt->bindParam(':id', $getid, PDO::PARAM_INT);
$stmt->execute();
$insertedData = $stmt->fetch(PDO::FETCH_ASSOC);

// Assuming $insertedData contains the inserted data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Check if the form submission is for editing a report
  if ($_POST['login_type'] === 'edit_report2') {
      // Retrieve form data
      $reportId = $_POST['reportid'];
      $updatedHeadlines = $_POST['updatedHeadlines'];
      $updatedData = $_POST['updatedData'];

      // Check if the number of headlines matches the number of data elements
      if (count($updatedHeadlines) !== count($updatedData)) {
          // Redirect back to the edit page with an error message
          header('Location: edit1.php?id=' . $reportId . '&error=1');
          exit();
      }

      try {
          // Update the records in the database
          $stmt = $dbh->prepare("UPDATE tblreport_step1 SET headline = :headline, data = :data WHERE request_id = :id");

          // Loop through each headline and its corresponding data
          foreach ($updatedHeadlines as $index => $headline) {
              // Bind parameters and execute the query for each record
              $stmt->bindParam(':headline', $headline);
              $stmt->bindParam(':data', $updatedData[$index]);
              $stmt->bindParam(':id', $reportId);
              $stmt->execute();
          }

          // Redirect back to the page with a success message
          header('Location: edit1.php?id=' . $reportId . '&success=1');
          exit();
      } catch (PDOException $e) {
          // Handle any database errors
          echo "Error: " . $e->getMessage();
      }
  } else {
      // If the login type is not recognized, redirect to index page
      header('Location: ../../index.php');
      exit();
  }
}

?>
<!-- <style>
  @font-face {
    font-family: 'Khmer MEF2';
    src: url('../../fonts/KhmerMEF2.woff2') format('woff2'),
      url('../../fonts/KhmerMEF2.woff') format('woff');
    font-weight: normal;
    font-style: normal;
  }

  .khmer-font {
    font-family: 'Khmer MEF2', sans-serif;
  }

  p {
    font-size: 16px;
    text-align: justify;
    line-height: 2.0;
    /* Adjust line height for better readability */
  }
</style> -->
<div class="row mb-3 align-items-center">
  <div class="col-12 col-lg-6 mb-3 mb-lg-0">
    <h3 class="khmer-font">Report Details</h3>
  </div>
</div>

<form id="wizard-validation-form" method="post">
  <input type="hidden" name="login_type" value="edit_report2">
  <input type="hidden" name="reportid" value="<?php echo $getid ?>">
  <div class="accordion mt-3 accordion-header-primary" id="reportAccordion">
    <?php
    // Use newline as the delimiter to split the headlines and data
    $headlines = explode("\n", trim($insertedData['headline']));
    $data = explode("\n", trim($insertedData['data']));

    foreach ($headlines as $index => $headline) {
      $headline = htmlspecialchars_decode($headline);
      $dataLine = htmlspecialchars_decode($data[$index] ?? '');
    ?>
      <div class="accordion-item card mt-1">
        <h2 class="accordion-header" id="heading<?php echo $index; ?>">
          <button class="accordion-button collapsed mef2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false" aria-controls="collapse<?php echo $index; ?>">
            <?php echo $headline; ?>
          </button>
        </h2>
        <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#reportAccordion">
          <div class="accordion-body">
            <label class="form-label khmer-font">Headline <?php echo $index + 1; ?></label>
            <input type="text" class="form-control mb-3" name="updatedHeadlines[]" value="<?php echo trim(htmlspecialchars($headline)); ?>">
            <label class="form-label khmer-font">Content <?php echo $index + 1; ?></label>
            <div id="editor-container-<?php echo $index; ?>"></div>
            <textarea class="d-none" name="updatedData[]" id="hiddenEditorContent-<?php echo $index; ?>"></textarea>
            <div id="selectedTextInfo-<?php echo $index; ?>"></div> <!-- Display selected text info here -->
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
  <div class="col-12 d-flex justify-content-between mt-3">
    <button type="submit" class="btn btn-primary btn-submit">Submit</button>
  </div>
</form>

<?php $content = ob_get_clean(); ?>
<?php include('../../layouts/layout_edit_report1.php'); ?>
