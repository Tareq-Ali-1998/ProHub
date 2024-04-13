<?php
include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php';

$helper = new APIHandler();

$helper->authorizeRequestUsernameAndPassword();










$requestBody = $helper->getRequestBody();

if ($requestBody === null) {
    http_response_code(400);
    $helper->sendRequestBody(false, "This API expects JSON data, but it found nothing.", null);
    exit();
}

if (!Utilities::checkJsonKeysMatch($requestBody, ["freelancer_id", "pdf_names_in_order"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order.", null);
    exit();
}

$freelancerId = $requestBody["freelancer_id"];
$pdfNamesInOrder = $requestBody["pdf_names_in_order"];










$helper->connectToMySQLDatabase();

// Check that the freelancer ID exists in the database
$queryData = mysqli_fetch_assoc($helper->executeQuery(
    "SELECT `freelancer_id`
     FROM `db_a993c8_freelan`.`freelancers`
     WHERE `freelancer_id` = '$freelancerId'"));

if (!isset($queryData["freelancer_id"])) {
    $helper->sendRequestBody(false, "We don't have any freelancer with this freelancer_id in the platform.", null);
    exit();
}










/*
 * Storing for each PDF name its new PDF order as provided in the given $pdfNamesInOrder array, so
 * that we can know the new pdf order from its name in a good time complexity (The associative array
 * using the appropriate hashing functions to optimize the lookups).
 */
$newPDFNameOrderNumber = array();
for ($i = 0; $i < count($pdfNamesInOrder); $i++) {
    $newPDFNameOrderNumber[$pdfNamesInOrder[$i]] = $i + 1;
}

// Check that all provided PDF names exist in the database for the given freelancer
$queryResult = $helper->executeQuery(
    "SELECT `db_a993c8_freelan`.`freelancer_pdfs`.pdf_path
     FROM `db_a993c8_freelan`.`freelancer_pdfs`
     WHERE `freelancer_id` = '$freelancerId'");
$currentPDFCountInTheDatabase = mysqli_num_rows($queryResult);

if ($currentPDFCountInTheDatabase != count($pdfNamesInOrder)) {
    $helper->sendRequestBody(false, "The number of provided PDF files does not match the number ".
                                    "of PDF files for this freelancer in the platform.", null);
    exit();
}

$allPDFsPaths = array();
while ($queryData = mysqli_fetch_assoc($queryResult)) {
    $pdfPath = $queryData["pdf_path"];
    $allPDFsPaths[] = $pdfPath;
    $currentPDFName = Utilities::getPDFNameFromItsPath($pdfPath);

    if (!isset($newPDFNameOrderNumber[$currentPDFName])) {
        $helper->sendRequestBody(false, "One or more of the provided PDF names are not valid ".
                                        "for this freelancer.", null);
        exit();
    }
}

// Update the order of the PDF files for the given freelancer
foreach ($allPDFsPaths as $pdfPath) {
    $pdfName = Utilities::getPDFNameFromItsPath($pdfPath);
    $newPDFOrderNumber = $newPDFNameOrderNumber[$pdfName];

    $helper->executeQuery(
        "UPDATE `db_a993c8_freelan`.`freelancer_pdfs`
         SET `pdf_order_number` = $newPDFOrderNumber
         WHERE `freelancer_id` = '$freelancerId' AND `pdf_path` = '$pdfPath'");
}

$helper->sendRequestBody(true, "Ok", null);

?>