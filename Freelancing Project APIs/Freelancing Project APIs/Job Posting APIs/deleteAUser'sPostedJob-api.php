<?php

include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php';










$helper = new APIHandler();

$helper->authorizeRequestUsernameAndPassword();










$requestBody = $helper->getRequestBody();
if ($requestBody === null) {
    $helper->sendRequestBody(false, "This API expects JSON data, but it found nothing.", null);
    exit();
}

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_id", "job_id"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order", null);
    exit();
}

// Get the user_id and job_id from the request body
$userID = $requestBody['user_id'];
$jobID = $requestBody['job_id'];










// Connect to the MySQL database
$helper->connectToMySQLDatabase();

// Check if the user exists in the platform
$userExists = mysqli_fetch_assoc($helper->executeQuery(
    "SELECT COUNT(*) FROM `db_a993c8_freelan`.`users` WHERE user_id = {$userID}"))["COUNT(*)"];

if ($userExists != 1) {
    $helper->sendRequestBody(false, "The user with the provided user_id does not exist in the platform.", null);
    exit();
}










// Check if the job exists in the platform
$jobExists = mysqli_fetch_assoc($helper->executeQuery(
    "SELECT COUNT(*) FROM `db_a993c8_freelan`.`jobs` WHERE job_id = {$jobID}"))["COUNT(*)"];

if ($jobExists != 1) {
    $helper->sendRequestBody(false, "The job with the provided job_id does not exist in the platform.", null);
    exit();
}










// Check if the job has been done by a freelancer
$freelancerID = mysqli_fetch_assoc($helper->executeQuery(
    "SELECT `db_a993c8_freelan`.`jobs`.freelancer_id FROM `db_a993c8_freelan`.`jobs` WHERE job_id = {$jobID}"))["freelancer_id"];

if ($freelancerID !== null) {
    // Mahmoud here you should alerte the user about this message exactly the same as it is.
    $helper->sendRequestBody(true, "The job you are trying to deleted has been completed by a freelancer and can't be deleted.", null);
    exit();
}

// Delete the job from the database
$helper->executeQuery("DELETE FROM `db_a993c8_freelan`.`jobs` WHERE job_id = {$jobID}");

// Send the response to the client
$helper->sendRequestBody(true, "Ok", null);