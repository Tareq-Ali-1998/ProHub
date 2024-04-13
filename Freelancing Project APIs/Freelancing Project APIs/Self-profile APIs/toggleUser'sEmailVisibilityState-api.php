


<?php

include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php';










$helper = new APIHandler();

$helper->authorizeRequestUsernameAndPassword();










$requestBody = $helper->getRequestBody();
// Check if the request body exists and contains the required keys
if ($requestBody === null) {
    http_response_code(400);
    $helper->sendRequestBody(false, "This API expects JSON data, but it found nothing.", null);
    exit();
}
if (!Utilities::checkJsonKeysMatch($requestBody, ["user_id"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, ".
                                    "or the keys are not in the expected order.", null);
    exit();
}

$userId = $requestBody['user_id'];










$helper->connectToMySQLDatabase();
// Check if the user ID is registered in our platform
$queryData = mysqli_fetch_assoc($helper->executeQuery("SELECT `db_a993c8_freelan`.`users`.user_id
                                                       FROM `db_a993c8_freelan`.`users`
                                                       WHERE `db_a993c8_freelan`.`users`.user_id = '$userId'"));
if (!isset($queryData["user_id"])) {
    $helper->sendRequestBody(false, "We don't have any user with this user_id in the platform.", null);
    exit();
}

// Flip the state of the user's email visibility column
$queryData = $helper->executeQuery("UPDATE `db_a993c8_freelan`.`users`
                                    SET `db_a993c8_freelan`.`users`.user_phone_number_visibility = 
                                    CASE 
                                        WHEN `db_a993c8_freelan`.`users`.user_email_visibility = 0 
                                        THEN 1 
                                        ELSE 0 
                                    END 
                                    WHERE `db_a993c8_freelan`.`users`.user_id = '$userId'");

// Send the response
$helper->sendRequestBody(true, "Ok", null);