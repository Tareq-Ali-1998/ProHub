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
if (!Utilities::checkJsonKeysMatch($requestBody, ["freelancer_id", "freelancer_description"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or the ".
                                    "keys are not in the expected order.", null);
    exit();
}

$freelancerId = $requestBody['freelancer_id'];
$freelancerDescription = $requestBody['freelancer_description'];










$helper->connectToMySQLDatabase();
$queryData = mysqli_fetch_assoc($helper->executeQuery("SELECT `db_a993c8_freelan`.`freelancers`.`freelancer_id` 
                                                       FROM `freelancers` 
                                                       WHERE `freelancer_id` = '$freelancerId'"));
if (!isset($queryData["freelancer_id"])) {
    $helper->sendRequestBody(false, "We don't have any freelancer with this freelancer_id in the platform.", null);
    exit();
}

$queryData = $helper->executeQuery("UPDATE `db_a993c8_freelan`.`freelancers`
                                    SET `db_a993c8_freelan`.`freelancers`.freelancer_description = '$freelancerDescription'
                                    WHERE `db_a993c8_freelan`.`freelancers`.freelancer_id = '$freelancerId'");

$helper->sendRequestBody(true, "Ok", null);