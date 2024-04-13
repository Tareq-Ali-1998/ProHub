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

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_email", "user_verification_code"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order", null);
    exit();
}

// *********************************************
// TODO: It's a good idea here to make a function that validates the variables types.
// *********************************************
$userEmail = $requestBody['user_email'];
$userVerificationCode = $requestBody['user_verification_code'];










$helper->connectToMySQLDatabase();

$thisEmailAddressOwnedByAUserInThePlatform = mysqli_fetch_assoc($helper->executeQuery(
                    "SELECT COUNT(*) 
                     FROM `db_a993c8_freelan`.`users`
                     WHERE `db_a993c8_freelan`.`users`.user_email = '$userEmail'"))['COUNT(*)'];

if ($thisEmailAddressOwnedByAUserInThePlatform == 1) {
    $helper->sendRequestBody(false, "Definitely, you have been stuck on this screen for a long while, when an account with the ".
                                    "same email address you provided before now exists in our system. If it's you, please try to log in ".
                                    "or use a different email address to create your account.", null);
    exit();
}










$thisEmailExistsInTheIncompleteRegistrationTables = mysqli_fetch_assoc($helper->executeQuery(
                    "SELECT COUNT(*)
                     FROM `db_a993c8_freelan`.`incomplete_registration_users`
                     WHERE `db_a993c8_freelan`.`incomplete_registration_users`.user_email = '$userEmail'"))['COUNT(*)'];

if ($thisEmailExistsInTheIncompleteRegistrationTables == 0) {
    $helper->sendRequestBody(false, "This email doesn't have any valid verification code right now, try to ".
                                    "rigester again by clicking the following button.", null);

    exit();
}










$numRows = mysqli_fetch_assoc($helper->executeQuery(
                    "SELECT COUNT(*)
                     FROM `db_a993c8_freelan`.`incomplete_registration_users`
                     WHERE `db_a993c8_freelan`.`incomplete_registration_users`.user_email = '$userEmail'
                     AND `db_a993c8_freelan`.`incomplete_registration_users`.user_verification_code = '$userVerificationCode'"))['COUNT(*)'];

if ($numRows == 0) {
    $helper->sendRequestBody(false, "This verification code is wrong, just write the one ".
                                    "that you have received in your email.", null);
    exit();

}










$helper->executeQuery("UPDATE `db_a993c8_freelan`.`incomplete_registration_users`
                       SET `db_a993c8_freelan`.`incomplete_registration_users`.user_registration_status = 1
                       WHERE `db_a993c8_freelan`.`incomplete_registration_users`.user_email = '$userEmail'");


$helper->sendRequestBody(true, "Ok", null);

?>