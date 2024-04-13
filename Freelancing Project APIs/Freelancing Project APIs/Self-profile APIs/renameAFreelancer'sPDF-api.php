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

if (!Utilities::checkJsonKeysMatch($requestBody, ["freelancer_id", "old_pdf_name", "new_pdf_name"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order.", null);
    exit();
}



$freelancerId = $requestBody['freelancer_id'];
$oldPDFName = $requestBody['old_pdf_name'];
$newPDFName = $requestBody['new_pdf_name'];










if ($oldPDFName == $newPDFName) {
    $helper->sendRequestBody(true, "Ok, but it's the same PDF's name!!, Therefore, no changes are made.", null);
}










$helper->connectToMySQLDatabase();

$queryData = mysqli_fetch_assoc($helper->executeQuery(
    "SELECT `db_a993c8_freelan`.`freelancers`.freelancer_id
     FROM `db_a993c8_freelan`.`freelancers`
     WHERE `db_a993c8_freelan`.`freelancers`.freelancer_id = '$freelancerId'"));

if (!isset($queryData["freelancer_id"])) {
    $helper->sendRequestBody(false, "We don't have any freelancer with this freelancer_id in the platform.", null);
    exit();
}










$queryData = $helper->executeQuery(
    "SELECT `db_a993c8_freelan`.`freelancer_pdfs`.pdf_path
     FROM `db_a993c8_freelan`.`freelancer_pdfs`
     WHERE `freelancer_id` = '$freelancerId'");

$correspondingPDFFound = false;

while ($row = mysqli_fetch_assoc($queryData)) {
    $currentPDFPath = $row["pdf_path"];

    $currentPDFName = Utilities::getPDFNameFromItsPath($currentPDFPath);

    if ($currentPDFName == $newPDFName) {
        // Mahmoud you should also handle this case from the Flutter side in order not to process a request without a meaning
        $helper->sendRequestBody(false, "This freelancer already has a PDF with the same name as the new name ".
                                        "provided in the request, and the PDFs' names should be unique for each ".
                                        "freelancer independently.", null);
        exit();
    }

    if ($oldPDFName == $currentPDFName) {
        $correspondingPDFFound = true;

        $newPDFPath = Utilities::getNewPDFPath($currentPDFPath, $newPDFName);
        if ($newPDFPath == null) {
            $helper->sendRequestBody(false, "A problem occurred on the server-side file system while renaming ".
                                            "the corresponding PDF because there was already a problem with the current ".
                                            "PDF name, which is that it hasn't two dots, please try again.", null);
            exit();
        }

        try {
            if (rename($currentPDFPath, $newPDFPath)) {
                $helper->executeQuery(
                    "UPDATE `db_a993c8_freelan`.`freelancer_pdfs`
                     SET `pdf_path` = '$newPDFPath'
                     WHERE `freelancer_id` = '$freelancerId' AND `pdf_path` = '$currentPDFPath'");
            }
            else {
                $helper->sendRequestBody(false, "A problem occurred on the server-side file system while renaming ".
                                                "the pdf, please try again.", null);
                exit();
            }
        }
        catch (Exception $exception) {
            $helper->sendRequestBody(false, "A problem occurred on the server-side file system while renaming ".
                                            "the pdf, please try again.", null);
            exit();
        }
        break;
    }
}

if (!$correspondingPDFFound) {
    $helper->sendRequestBody(false, "We don't have any PDF for this freelancer with the provided PDF's old name.", null);
    exit();
}










$helper->sendRequestBody(true, "Ok", null);

?>