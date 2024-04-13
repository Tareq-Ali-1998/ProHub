<?php

include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php';
include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/EmailHandler.php';










$helper = new APIHandler();

$helper->authorizeRequestUsernameAndPassword();










$requestBody = $helper->getRequestBody();
if ($requestBody === null) {
    http_response_code(400);
    $helper->sendRequestBody(false, "This API expects JSON data, but it found nothing.", null);
    exit();
}

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_email", "user_first_name", "user_last_name", "user_password"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order.", null);
    exit();
}

// *********************************************
// TODO: It's a good idea here to make a function that validates the variables types.
// *********************************************
$userEmail = $requestBody['user_email'];
$userFirstName = $requestBody["user_first_name"];
$userLastName = $requestBody["user_last_name"];
$userPassword = $requestBody["user_password"];










$helper->connectToMySQLDatabase();

$thisEmailExistsInThePlatform = mysqli_fetch_assoc($helper->executeQuery("SELECT COUNT(*)
                                                                          FROM `db_a993c8_freelan`.`users`
                                                                          WHERE `db_a993c8_freelan`.`users`.user_email = '$userEmail'"))["COUNT(*)"];

if ($thisEmailExistsInThePlatform == 1) {
    $helper->sendRequestBody(false, "The email address you are trying to register with had already been registered on our platform.", null);
    exit();
}










$queryData = mysqli_fetch_assoc($helper->executeQuery("SELECT `db_a993c8_freelan`.`incomplete_registration_users`.user_registration_status
                                                       FROM `db_a993c8_freelan`.`incomplete_registration_users`
                                                       WHERE `db_a993c8_freelan`.`incomplete_registration_users`.user_email = '$userEmail'"));

$userRegistrationStatus = null;
if (isset($queryData["user_registration_status"])) {
    $userRegistrationStatus = $queryData["user_registration_status"];
}

if ($userRegistrationStatus != null) {

    if ($userRegistrationStatus == 1) {
        $helper->sendRequestBody(false, "This email address has been verified successfully, you can continue your registration by clicking ".
                                        "the following button.", null);
        exit();
    }

    $helper->sendRequestBody(false, "This email address has already received the required verification code, so ".
                                    "you may attempt to verify your email clicking the following button.", null);
    exit();
}










// If there are no more available email verification codes to send for the newly registered users, we should
// generate a hundred new distinct email verification codes and insert them in the `email_verification_codes` table.
$thereIsAtLeastOneEmailVerificationCodeAvailable = mysqli_fetch_assoc($helper->executeQuery("SELECT 'COUNT(*)'
                                                                                             FROM `db_a993c8_freelan`.`email_verification_codes`
                                                                                             LIMIT 1"))['COUNT(*)'];
if ($thereIsAtLeastOneEmailVerificationCodeAvailable == 0) {

    $newEmailVerificationCodesInsertionQuery = "INSERT INTO `db_a993c8_freelan`.`email_verification_codes` 
                                                (`db_a993c8_freelan`.`email_verification_codes`.verification_code) VALUES\n";
        
    $emailVerificationCodes = Utilities::generateEmailVerificationCodes();
    $helperString = "";
    $counter = 0;
    foreach ($emailVerificationCodes as $currentCode) {
        $counter++;
        if ($counter == 100) {
            $helperString .= "('".$currentCode."');\n";
            break;
        }
        $helperString .= "('".$currentCode."'),\n";
    }

    $newEmailVerificationCodesInsertionQuery .= $helperString;
    
    $helper->executeQuery($newEmailVerificationCodesInsertionQuery);

}










$emailVerificationCode = mysqli_fetch_assoc($helper->executeQuery("SELECT `db_a993c8_freelan`.`email_verification_codes`.verification_code
                                                                    FROM `db_a993c8_freelan`.`email_verification_codes` LIMIT 1"))['verification_code'];

$emailHandler = new EmailHandler();

if (!$emailHandler->sendEmail($userEmail, $userFirstName, $userLastName, $emailVerificationCode)) {
    $helper->sendRequestBody(false, "There was a problem while sending your email verification code, please try to receive it again using ".
                                    "the following button.", null);
    exit();
}

$helper->executeQuery("DELETE FROM `db_a993c8_freelan`.`email_verification_codes`
                       WHERE `db_a993c8_freelan`.`email_verification_codes`.verification_code = '$emailVerificationCode'");

$userPasswordAfterHashing = password_hash($userPassword, PASSWORD_BCRYPT);
$helper->executeQuery("INSERT INTO `db_a993c8_freelan`.`incomplete_registration_users` (user_email, user_first_name, user_last_name,
                       user_password, user_verification_code) VALUES
                       ('$userEmail', '$userFirstName', '$userLastName', '$userPasswordAfterHashing', '$emailVerificationCode');");

$helper->sendRequestBody(true, "Ok", null);

?>