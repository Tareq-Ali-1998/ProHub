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

if (!Utilities::checkJsonKeysMatch($requestBody, ["freelancer_id", "new_pdf_name", "user_new_pdf_base64encoding_string"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order.", null);
    exit();
}

$freelancerId = $requestBody["freelancer_id"];
$newPDFName = $requestBody["new_pdf_name"];
$pdfBase64EncodingString = $requestBody["user_new_pdf_base64encoding_string"];










$helper->connectToMySQLDatabase();

$queryData = mysqli_fetch_assoc($helper->executeQuery(
    "SELECT `freelancer_id`
     FROM `freelancers`
     WHERE `freelancer_id` = '$freelancerId'"));

if (!isset($queryData["freelancer_id"])) {
    $helper->sendRequestBody(false, "We don't have any freelancer with this freelancer_id in the platform.", null);
    exit();
}












$theNumberOfCurrentFreelancerPDFs = mysqli_fetch_assoc($helper->executeQuery(
    "SELECT COUNT(*)
     FROM `freelancer_pdfs`
     WHERE `freelancer_id` = '$freelancerId'"))["COUNT(*)"];

if ($theNumberOfCurrentFreelancerPDFs == 50) {
    // Mahmoud, here you have to prevent the freelancer to add a new PDF if the current number of his PDFs is 50.
    $helper->sendRequestBody(false, "The current freelancer is not allowed to add a new PDF because".
                                    "he already has 50 PDFs, and this is the maximum allowed number on the platform.", null);
    exit();
}










$pdfContentsAsBytesString = base64_decode($pdfBase64EncodingString);
if (!Utilities::isValidPDFFile($pdfContentsAsBytesString)) {
    // Mahmoud, here you have to send the request only if it's a valid PDF, and you can check that on the
    // Flutter side using the class that I will provide you with, it's a Dart class that I had implemented before.
    $helper->sendRequestBody(false, "The provided file isn't a valid PDF file.", null);
    exit();
}










$newPdfPath = Utilities::generateANewPDFPath($freelancerId, $newPDFName);

try {
    $result = file_put_contents($newPdfPath, $pdfContentsAsBytesString);
    if (!$result) {
        $helper->sendRequestBody(false, "A problem occurred on the server-side file system while adding ".
                                        "the new PDF, please try again.", null);
        exit();
    }
}
catch (Exception $exception) {
    $helper->sendRequestBody(false, "A problem occurred on the server-side file system while adding ".
                                    "the new PDF, please try again.", null);
    exit();
}


$helper->executeQuery(
    "UPDATE `db_a993c8_freelan`.`freelancer_pdfs`
     SET `db_a993c8_freelan`.`freelancer_pdfs`.pdf_order_number = `pdf_order_number` + 1
     WHERE `freelancer_id` = '$freelancerId'");

$newPdfOrderNumber = 1;

$helper->executeQuery(
    "INSERT INTO `db_a993c8_freelan`.`freelancer_pdfs` (`freelancer_id`, `pdf_path`, `pdf_order_number`)
     VALUES ('$freelancerId', '$newPdfPath', '$newPdfOrderNumber')");

$helper->sendRequestBody(true, "Ok", null);
?>