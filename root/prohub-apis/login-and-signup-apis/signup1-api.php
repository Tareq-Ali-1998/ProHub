<?php

require_once dirname(dirname(dirname(__DIR__))) . '/prohub/config.php';
require_once CLASSES_PATH . '/Utilities.php';
require_once CLASSES_PATH . '/HTTPHandler.php';
require_once CLASSES_PATH . '/EmailHandler.php';
require_once CLASSES_PATH . '/Authenticator.php';
require_once CLASSES_PATH . '/DatabaseConnection.php';


if (!Authenticator::authenticate()) {
    HTTPHandler::sendResponse(
        401,
        false,
        'Unauthorized: Invalid or missing credentials.',
        null
    );
    exit();
}


$requestBody = HTTPHandler::getJsonRequestBody();
if ($requestBody == null) {
    HTTPHandler::sendResponse(
        400, 
        false,
        'This API expects JSON data, but it found nothing.',
        null
    );
    exit();
}


if (!Utilities::checkJsonKeysMatch($requestBody, ['user_email', 'user_first_name',
                                                  'user_last_name', 'user_password'])) {
    HTTPHandler::sendResponse(
        400,
        false,
        'The request body is missing one or more required JSON keys, or '.
        'the keys are not in the expected order.',
        null
    );
    exit();
}

// *********************************************
// TODO: It's a good idea here to make a function that validates the variables types.
// *********************************************
$userEmail = $requestBody['user_email'];
$userFirstName = $requestBody["user_first_name"];
$userLastName = $requestBody["user_last_name"];
$userPassword = $requestBody["user_password"];


try {
    $databaseConnection = new DatabaseConnection();
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}




// Now checking if the user is already registered in our system.
try {
    $query = "SELECT COUNT(*)
              FROM users
              WHERE user_email = ?";
    $queryResult = $databaseConnection->executeQuery($query, [$userEmail], 's');
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}
$thisEmailIsAlreadyRegistered = $queryResult->fetch_assoc()['COUNT(*)'];
if ($thisEmailIsAlreadyRegistered) {
    HTTPHandler::sendResponse(
        200,
        true,
        'The email address you are trying to register with had already been ' .
        'registered on our platform.',
        null
    );
    exit();
}




// Now checking if the user is semi-registered in our system, or if
// he has got an email verification code via his email but didn't use 
// that code to verify his email yet.
try {
    $query = "SELECT user_email_verification_status
              FROM incomplete_registration_users
              WHERE user_email = ?";
    $queryResult = $databaseConnection->executeQuery($query, [$userEmail], 's');
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}
if ($queryResult->num_rows == 1) {
    $userRegistrationStatus = $queryResult->fetch_assoc()['user_email_verification_status'];
    if ($userRegistrationStatus == 1) {
        HTTPHandler::sendResponse(
            200,
            true,
            'This email address has been verified successfully, and here we should send ' .
            'the user directly to the signup2 phase to continue his rigestration.',
            null
        );
        exit();
    }
    else {
        HTTPHandler::sendResponse(
            200,
            true,
            'This email address has already received the required verification code, but ' .
            "hasn't verified his email yet, so we should send him to verify his email.",
            null
        );
        exit();
    }
}




// Now insert the user's info into the incomplete_registration_users table
// because the next phase for him is to verify his email.
// Note that I am not sending the hashed password for now.
// $userPasswordAfterHashing = password_hash($userPassword, PASSWORD_BCRYPT);
$emailVerificationCode = Utilities::generateEmailVerificationCode();
$emailHandler = new EmailHandler();
try {
    $emailHandler->sendEmail($userEmail, $userFirstName, $userLastName, $emailVerificationCode);
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}
try {
    $query = "INSERT INTO incomplete_registration_users
              (user_email, user_first_name, user_last_name,
              user_password, user_verification_code)
              VALUES (?, ?, ?, ?, ?);";
    $databaseConnection->executeQuery(
        $query,
        [$userEmail, $userFirstName, $userLastName, $userPassword, $emailVerificationCode],
        'sssss'
    );
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}




HTTPHandler::sendResponse(
    200,
    true,
    'OK',
    null
);

?>