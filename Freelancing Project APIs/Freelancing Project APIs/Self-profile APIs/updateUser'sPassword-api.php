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

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_id", "user_old_password", "user_new_password"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order.", null);
    exit();
}

$userId = $requestBody["user_id"];
$userOldPassword = $requestBody["user_old_password"];
$userNewHashedPasswordToStorInTheDatabase = password_hash($requestBody["user_new_password"], PASSWORD_BCRYPT);










$helper->connectToMySQLDatabase();

$queryData = mysqli_fetch_assoc($helper->executeQuery("SELECT `db_a993c8_freelan`.`users`.user_password
                                                       FROM `db_a993c8_freelan`.`users`
                                                       WHERE `db_a993c8_freelan`.`users`.user_id = '$userId'"));

if (!isset($queryData["user_password"])) {
    $helper->sendRequestBody(false, "We don't have any user with this user_id in the platform.", null);
    exit();
}

$userCurrentHashedPasswordFromTheDatabase = $queryData["user_password"];

if (!password_verify($userOldPassword, $userCurrentHashedPasswordFromTheDatabase)) {
    // Maybe in the future, we will consider involving a process that handle
    // the case when a user forgot his password.
    $helper->sendRequestBody(false, "Incorrect old password.", null);
    exit();
}










$queryData = $helper->executeQuery("UPDATE `db_a993c8_freelan`.`users`
                                    SET `db_a993c8_freelan`.`users`.user_password = '$userNewHashedPasswordToStorInTheDatabase'
                                    WHERE `db_a993c8_freelan`.`users`.user_id = '$userId'");
                                    
$helper->sendRequestBody(true, "Ok", null);