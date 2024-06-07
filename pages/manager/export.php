<?php
session_start();
// Include Composer's autoloader
require_once '../../vendor/autoload.php';

// Import the PhpWord namespace
use PhpOffice\PhpWord\PhpWord;

// Get the ID parameter from the URL
$getid = $_GET['id'];

// Include necessary files and establish database connection
include('../../config/dbconn.php');
include('../../includes/login_check.php');
include('../../controllers/form_process.php');

// Fetch data from the tblrequests table where ID matches $getid
$stmt = $dbh->prepare("SELECT headline, report_data_step3 FROM tblreports WHERE id = :id");
$stmt->bindParam(':id', $getid, PDO::PARAM_INT);
$stmt->execute();
$insertedData = $stmt->fetch(PDO::FETCH_ASSOC);

// Create a new PhpWord instance
$phpWord = new PhpWord();

// Add a section to the document
$section = $phpWord->addSection();

// Add the form data to the document
if ($insertedData) {
  $headlines = explode(',', $insertedData['headline']);
  $data = explode(',', $insertedData['report_data_step3']);
  foreach ($headlines as $index => $headline) {
    $section->addText(html_entity_decode(strip_tags($headline)), array('name' => 'khmer mef2', 'size' => 12, 'color' => '000000'));
    $section->addText(html_entity_decode(strip_tags($data[$index])), array('name' => 'khmer mef1', 'size' => 8, 'color' => '000000', 'align' => 'both'));
    $section->addTextBreak(); // Add a line break after each headline
  }
}

// Set up headers for file download
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment;filename="export.docx"');

// Save the document to output
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('php://output');
