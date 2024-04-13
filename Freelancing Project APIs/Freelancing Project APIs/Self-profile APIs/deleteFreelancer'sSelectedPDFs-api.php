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

if (!Utilities::checkJsonKeysMatch($requestBody, ["freelancer_id", "pdfs_names"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or " .
                                    "the keys are not in the expected order.", null);
    exit();
}

$freelancerId = $requestBody['freelancer_id'];
$pdfsToDelete = $requestBody['pdfs_names'];










$helper->connectToMySQLDatabase();

$queryData = mysqli_fetch_assoc($helper->executeQuery(
    "SELECT `freelancer_id`
     FROM `freelancers`
     WHERE `freelancer_id` = '$freelancerId'"));

if (!isset($queryData["freelancer_id"])) {
    $helper->sendRequestBody(false, "We don't have any freelancer with this freelancer_id in the platform.", null);
    exit();
}










// Get all the PDFs' paths for the freelancer
$queryData = $helper->executeQuery(
    "SELECT `db_a993c8_freelan`.`freelancer_pdfs`.pdf_path
     FROM `db_a993c8_freelan`.`freelancer_pdfs`
     WHERE `db_a993c8_freelan`.`freelancer_pdfs`.freelancer_id = '$freelancerId'");

$deletedPDFsCounter = 0;

// Marking the PDFs names that we need to delete in order to maintain a better time complexity
$toDelete = array();
foreach ($pdfsToDelete as $pdfName) {
    $toDelete[$pdfName] = true;
}

while ($row = mysqli_fetch_assoc($queryData)) {
    $currentPDFPath = $row["pdf_path"];

    // Get the current PDF name from its path
    $currentPDFName = Utilities::getPDFNameFromItsPath($currentPDFPath);

    // Check if the current PDF should be deleted
    if (isset($toDelete[$currentPDFName])) {
        // Try to delete the PDF file
        try {
            if (unlink($currentPDFPath)) {
                // If the unlink operation succeeds, also delete the PDF record from the database.
                $helper->executeQuery(
                    "DELETE FROM `db_a993c8_freelan`.`freelancer_pdfs`
                     WHERE `db_a993c8_freelan`.`freelancer_pdfs`.freelancer_id = '$freelancerId'
                     AND `pdf_path` = '$currentPDFPath'");

                $deletedPDFsCounter++;
            }
        }
        catch (Exception $exception) {
            // I don't need to do anything here for sure
        }
    }
}

if ($deletedPDFsCounter == 0) {
    $helper->sendRequestBody(false, "We don't have any PDFs for this freelancer with the provided names.", null);
    exit();
}

if ($deletedPDFsCounter != count($pdfsToDelete)) {
    if ($deletedPDFsCounter == 1) {
        $helper->sendRequestBody(true, "We have successfully deleted one PDF for this freelancer ".
                                       "but not all the given ones, because the given names either don't exist ".
                                       "or there is an unknown server error.", null);
        exit();
    }
    $helper->sendRequestBody(true, "We have successfully deleted {$deletedPDFsCounter} PDFs for this freelancer ".
                                   "but not all the given ones, because the given names either don't exist ".
                                   "or there is an unknown server error.", null);
    exit();
}






// Now after all I need to renew the remaining PDFs order numbers as the following:
$queryResult = $helper->executeQuery(
    "SELECT `db_a993c8_freelan`.`freelancer_pdfs`.pdf_path
     FROM `db_a993c8_freelan`.`freelancer_pdfs`
     WHERE `db_a993c8_freelan`.`freelancer_pdfs`.freelancer_id = {$freelancerId}
     ORDER BY pdf_order_number ASC");

$currentPDFOrderNumber = 1;
while ($queryData = mysqli_fetch_assoc($queryResult)) {
    $pdfPath = $queryData["pdf_path"];
    $helper->executeQuery(
        "UPDATE `db_a993c8_freelan`.`freelancer_pdfs`
         SET `db_a993c8_freelan`.`freelancer_pdfs`.pdf_order_number = {$currentPDFOrderNumber}
         WHERE `db_a993c8_freelan`.`freelancer_pdfs`.freelancer_id = {$freelancerId}
         AND `db_a993c8_freelan`.`freelancer_pdfs`.pdf_path = '$pdfPath'");

    $currentPDFOrderNumber++;

}


$helper->sendRequestBody(true, "Ok", null);

?>