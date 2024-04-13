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

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_id", "job_id"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order.", null);
    exit();
}

// *********************************************
// TODO: It's a good idea here to make a function that validates the variables types.
// *********************************************
$userId = $requestBody["user_id"];
$jobId = $requestBody["job_id"];










$helper->connectToMySQLDatabase();

$exists = mysqli_fetch_assoc($helper->executeQuery("SELECT COUNT(*) FROM `db_a993c8_freelan`.`user_favorite_jobs` 
                                                    WHERE ((user_id = {$userId}) 
                                                           AND 
                                                           (job_id = {$jobId}))"))['COUNT(*)'];

if ($exists == 1) {
    $helper->executeQuery("DELETE FROM `db_a993c8_freelan`.`user_favorite_jobs` 
                           WHERE ((user_id = {$userId})
                                  AND 
                                  (job_id = {$jobId}))");

    $helper->sendRequestBody(true, "Ok", null);
    exit();
}

$helper->executeQuery("INSERT INTO `db_a993c8_freelan`.`user_favorite_jobs` (user_id, job_id)
                       VALUES ({$userId}, {$jobId})");


$helper->sendRequestBody(true, "Ok", null);

?>