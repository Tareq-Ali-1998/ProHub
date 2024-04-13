<?php

include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php';






$sortedJobPairs = array(
    
    array(5, 10),
    array(3, 30),
    array(8, 20),
    array(12, 40)
    
);


sort($sortedJobPairs);
$sortedJobPairs = array_reverse($sortedJobPairs);


for ($i = 0; $i < 4; $i++) {
    echo $sortedJobPairs[$i][1]."\n";
}


/*
include "C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php";
require_once "C:/Users/Tareq/Desktop/Freelancing Project APIs/Dependencies/autoload.php";
use Smalot\PdfParser\Parser;



echo Utilities::getCurrentDateTime();



$fileContentsAsBytesString = file_get_contents("C:/Users/Tareq/Desktop/te.pdf");


// Check the file format and contents using the Smalot\PdfParser library
$pdfParser = new Parser();
try {
    // Parse the file contents and validate the file format
    $pdf = $pdfParser->parseContent($fileContentsAsBytesString);
} 
catch (Exception $exception) {
    // If an exception is thrown, the file is not a valid PDF
    echo "The file can't be parsed as a PDF, so it's not a valid PDF file";
    exit();
}

// Extract the text content from the PDF and print it as separate strings of words
$text = $pdf->getText();
$words = str_word_count($text, 1);
foreach ($words as $word) {
    echo $word . " ";
}
*/

?>