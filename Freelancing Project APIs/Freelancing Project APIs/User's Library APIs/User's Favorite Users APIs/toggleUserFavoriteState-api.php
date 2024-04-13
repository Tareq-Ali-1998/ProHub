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

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_id", "favorite_user_id"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order.", null);
    exit();
}

$userId = $requestBody["user_id"];
$favoriteUserId = $requestBody["favorite_user_id"];










$helper->connectToMySQLDatabase();

$exists = mysqli_fetch_assoc($helper->executeQuery("SELECT COUNT(*) FROM `db_a993c8_freelan`.`user_favorite_users` 
                                                    WHERE ((user_id = {$userId}) 
                                                           AND 
                                                           (favorite_user_id = {$favoriteUserId}))"))['COUNT(*)'];

if ($exists == 1) {
    $helper->executeQuery("DELETE FROM `db_a993c8_freelan`.`user_favorite_users` 
                           WHERE ((user_id = {$userId})
                                  AND 
                                  (favorite_user_id = {$favoriteUserId}))");

    $helper->sendRequestBody(true, "Ok", null);
    exit();
}


$currentDateTime = Utilities::getCurrentDateTime();
$helper->executeQuery("INSERT INTO `db_a993c8_freelan`.`user_favorite_users` (user_id, favorite_user_id, favorite_user_adding_date)
                       VALUES ({$userId}, {$favoriteUserId}, '$currentDateTime')");


$helper->sendRequestBody(true, "Ok", null);

?>