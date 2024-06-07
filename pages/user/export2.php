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
$stmt = $dbh->prepare("SELECT headline, data FROM tblreport_step2 WHERE id = :id");
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

// Define styles for headlines and data
$headlineFontStyle = array('name' => 'Khmer MEF2', 'size' => 12, 'color' => '000000', 'bold' => true);
$dataFontStyle = array('name' => 'Khmer MEF1', 'size' => 8, 'color' => '000000');
$paragraphStyle = array('alignment' => Jc::BOTH);

// Add a section to the document for the TOC
$tocSection = $phpWord->addSection($sectionStyle);

// Add a footer to the TOC section with page numbers
$tocFooter = $tocSection->addFooter();
$tocFooter->addPreserveText('Page {PAGE} of {NUMPAGES}', null, array('alignment' => Jc::CENTER));

// Add a Table of Contents (TOC)
$tocSection->addText('មាតិកា', array('name' => 'Khmer MEF2', 'size' => 16, 'bold' => true), array('alignment' => Jc::CENTER));
$tocSection->addTOC(array('name' => 'Khmer MEF2', 'size' => 10));

// Add a section to the document for the content
$contentSection = $phpWord->addSection($sectionStyle);

// Add a footer to the content section with page numbers
$contentFooter = $contentSection->addFooter();
$contentFooter->addPreserveText('{PAGE}', null, array('alignment' => Jc::CENTER));

// Add the form data to the document
if ($insertedData) {
    $headlines = explode("\n", $insertedData['headline']);
    $data = explode("\n", $insertedData['data']);

    // Iterate through each headline and add it to the document
    foreach ($headlines as $index => $headline) {
        // Clean and decode the headline and data
        $cleanHeadline = html_entity_decode(strip_tags(trim($headline)));
        $cleanData = isset($data[$index]) ? html_entity_decode(strip_tags(trim($data[$index]))) : '';

        // Add the headline as a heading (level 1)
        $contentSection->addTitle($cleanHeadline, 1);

        // Add the corresponding data, if it exists
        if ($cleanData) {
            $contentSection->addText($cleanData, $dataFontStyle, $paragraphStyle);
        }

        // Add a line break after each set of headline and data
        $contentSection->addTextBreak();
    }
}

// Set up headers for file download
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment;filename="export.docx"');
// Save the document to output
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('php://output');
?>
