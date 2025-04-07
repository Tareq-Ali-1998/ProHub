<?php

require_once dirname(dirname(dirname(__DIR__))) . '/prohub/config.php';
require_once CLASSES_PATH . '/Utilities.php';
require_once CLASSES_PATH . '/HTTPHandler.php';
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


if (!Utilities::checkJsonKeysMatch($requestBody, ['user_email', 'user_verification_code'])) {
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
$userVerificationCode = $requestBody['user_verification_code'];


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
if ($thisEmailIsAlreadyRegistered == 1) {
    HTTPHandler::sendResponse(
        200,
        true,
        'The email address you are trying to register with had already been ' .
        'registered on our platform.',
        null
    );
    exit();
}




// Now checking if the user received an email verification code or not.
try {
    $query = "SELECT COUNT(*) 
              FROM incomplete_registration_users
              WHERE user_email = ?";
    $queryResult = $databaseConnection->executeQuery($query, [$userEmail], 's');
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}
$thisEmailReceivedVerificationCode = $queryResult->fetch_assoc()['COUNT(*)'];
if (!$thisEmailReceivedVerificationCode) {
    HTTPHandler::sendResponse(
        200,
        true,
        "This email doesn't have any valid verification code right now, we " . 
        'should send the user directly to the signup1 phase.',
        null
    );
    exit();
}




// Now checking if the provided email verification code is the valid one.
try {
    $query = "SELECT COUNT(*)
              FROM incomplete_registration_users
              WHERE (
                  user_email = ? 
                  AND
                  user_verification_code = ?)";
    $queryResult = $databaseConnection->executeQuery(
        $query,
        [$userEmail, $userVerificationCode],
        'ss'
    );
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}
$validEmailVerificationCode = $queryResult->fetch_assoc()['COUNT(*)'];
if (!$validEmailVerificationCode) {
    HTTPHandler::sendResponse(
        200,
        true,
        'This verification code is wrong, just write the one ' . 
        'that you have received via your email.',
        null
    );
    exit();
}




// Now if the code execution reaches this point, I should verify the user's email.
try {
    $query = "UPDATE incomplete_registration_users
              SET user_email_verification_status = 1
              WHERE user_email = ?";
    $databaseConnection->executeQuery($query, [$userEmail], 's');
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