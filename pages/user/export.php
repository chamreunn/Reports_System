<?php
session_start();
// Include Composer's autoloader
require_once '../../vendor/autoload.php';

// Import the PhpWord namespace
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

// Get the ID parameter from the URL
$getid = $_GET['id'];

// Include necessary files and establish database connection
include('../../config/dbconn.php');
include('../../includes/login_check.php');
include('../../controllers/form_process.php');

// Fetch data from the tblreports table where ID matches $getid
$stmt = $dbh->prepare("SELECT headline, report_data_step3 FROM tblreports WHERE id = :id");
$stmt->bindParam(':id', $getid, PDO::PARAM_INT);
$stmt->execute();
$insertedData = $stmt->fetch(PDO::FETCH_ASSOC);

// Create a new PhpWord instance
$phpWord = new PhpWord();
$sectionStyle = array(
    'marginTop' => 1400,
    'marginBottom' => 1400,
    'marginLeft' => 1400,
    'marginRight' => 1400,
);
$phpWord->setDefaultParagraphStyle(
    array(
        'alignment' => Jc::BOTH,
        'spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(12),
        'spacing' => 120,
    )
);

// Add a section to the document
$section = $phpWord->addSection($sectionStyle);

// Define styles for headlines and data
$headlineFontStyle = array('name' => 'khmer mef2', 'size' => 12, 'color' => '000000');
$dataFontStyle = array('name' => 'khmer mef1', 'size' => 8, 'color' => '000000');
$paragraphStyle = array('alignment' => Jc::BOTH);

// Array to store bookmarks for each headline
$bookmarks = array();

// Add the form data to the document
if ($insertedData) {
    $headlines = explode(',', $insertedData['headline']);
    $data = explode(',', $insertedData['report_data_step3']);

    // Iterate through each headline and add it to the document
    foreach ($headlines as $index => $headline) {
        // Add bookmark
        $bookmarkName = 'bookmark_' . $index;
        $section->addBookmark($bookmarkName, $section);
        $bookmarks[$index] = $bookmarkName;

        // Add the headline
        $section->addText(html_entity_decode(strip_tags($headline)), $headlineFontStyle);

        // Add the corresponding data, if it exists
        if (isset($data[$index])) {
            $section->addText(html_entity_decode(strip_tags($data[$index])), $dataFontStyle, $paragraphStyle);
        }

        // Add a line break after each set of headline and data
        $section->addTextBreak();
    }
}

// Add table of contents
$sectionTOC = $phpWord->addSection();
$sectionTOC->addText('មាតិកា', array('name' => 'khmer mef2', 'size' => 16, 'bold' => true, 'alignment' => Jc::CENTER));
foreach ($headlines as $index => $headline) {
    $sectionTOC->addLink(html_entity_decode(strip_tags($headline)), "#" . $bookmarks[$index], array('name' => 'khmer mef1', 'size' => 12));
}

// Set up headers for file download
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment;filename="export.docx"');
// Save the document to output
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('php://output');
?>
