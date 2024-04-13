<?php

include 'h:/root/home/tareqmahmood-001/www/prohub/Freelancing Project Classes/APIHandler.php';










$helper = new APIHandler();

$helper->authorizeRequestUsernameAndPassword();










$requestBody = $helper->getRequestBody();
if ($requestBody === null) {
    http_response_code(400);
    $helper->sendRequestBody(false, "This API expects JSON data, but it found nothing.", null);
    exit();
}

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_email"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order.", null);
    exit();
}

$userEmail = $requestBody['user_email'];







$helper->connectToMySQLDatabase();

$thisEmailExistsInThePlatform = mysqli_fetch_assoc($helper->executeQuery("SELECT COUNT(*)
                                                                          FROM `db_a993c8_freelan`.`users`
                                                                          WHERE `db_a993c8_freelan`.`users`.user_email = '$userEmail'"))["COUNT(*)"];

if ($thisEmailExistsInThePlatform == 1) {
    $helper->sendRequestBody(false, "The email address you are trying to register with had already been registered on our platform.", null);
    exit();
}






$queryData = mysqli_fetch_assoc($helper->executeQuery("SELECT *
                                                       FROM `db_a993c8_freelan`.`incomplete_registration_users`
                                                       WHERE `db_a993c8_freelan`.`incomplete_registration_users`.user_email = '$userEmail'"));

$userRegistrationStatus = null;
if (isset($queryData['user_registration_status'])) {
    $userRegistrationStatus = $queryData['user_registration_status'];
}

if ($userRegistrationStatus != null) {

    if ($userRegistrationStatus == 1) {
        $helper->sendRequestBody(false, "This email address has been verified successfully, you can continue your registration by clicking ".
                                        "the following button.", null);
        exit();
    }

    $arr = array("user_verification_code" => $queryData['user_verification_code']);
    $helper->sendRequestBody(true, "This email address has already received the required verification code, so ".
                                    "you may attempt to verify your email clicking the following button.", $arr);
    exit();
}
else {
    $helper->sendRequestBody(false, "This email address has no verification code.", null);
    exit();
}