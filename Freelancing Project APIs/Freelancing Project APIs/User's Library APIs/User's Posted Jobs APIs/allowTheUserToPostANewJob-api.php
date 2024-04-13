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

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_id"])) {
    http_response_code(400);
    $helper->sendRequestBody(true, "The request body is missing one or more required JSON keys, or ".
                                   "the keys are not in the expected order", null);
    exit();
}
$userId = $requestBody["user_id"];

$currentTime = Utilities::getCurrentDateTime();


$helper->connectToMySQLDatabase();
$numberOfPostedJobsInthePrevious24Hours = mysqli_fetch_assoc($helper->executeQuery(
"SELECT COUNT(*)
FROM
jobs
WHERE user_id = '$userId'
AND TIMESTAMPDIFF(MINUTE, job_creation_date, '$currentTime') < 1440"))['COUNT(*)'];

if ($numberOfPostedJobsInthePrevious24Hours > 10) {
    $helper->sendRequestBody(true, "You are not allowed to have more than 10 jobs in the last 24 hours, please ".
                                   "feel free to publish your post on the right time, and read our user manual", null);
    exit();
}

$helper->sendRequestBody(true, "Ok", null);