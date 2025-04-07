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


if (!Utilities::checkJsonKeysMatch($requestBody, ['user_id',
                                                  'user_old_password',
                                                  'user_new_password'])) {
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
$userId = $requestBody['user_id'];
$userOldPassword = $requestBody['user_old_password'];
$userNewPassword = $requestBody['user_new_password'];
// I should consider the hashed passwords when deploying the project.
$userNewHashedPasswordToStoreInTheDatabase = password_hash($requestBody['user_new_password'],
                                                           PASSWORD_BCRYPT);


try {
    $databaseConnection = new DatabaseConnection();
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}




// Now checking if the provided user_id is already registered in our system.
try {
    $query = "SELECT user_password
              FROM users
              WHERE user_id = ?";
    $queryResult = $databaseConnection->executeQuery($query, [$userId], 'i');
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}
if ($queryResult->num_rows == 0) {
    HTTPHandler::sendResponse(
        200,
        true,
        "We don't have any user with the provided user_id.",
        null
    );
    exit();
}




/* 
 * Now checking if the old password is correct or not.
 * 
 * Again, I should consider the hashed passwords when deploying the project, so
 * I should use the following function, and it should return true:
 * password_verify($userOldPassword, $userCurrentPasswordFromTheDatabase)
 * 
 */
$userCurrentPasswordFromTheDatabase = $queryResult->fetch_assoc()['user_password'];
if ($userOldPassword != $userCurrentPasswordFromTheDatabase) {
    HTTPHandler::sendResponse(
        200,
        true,
        "Incorrect old password.",
        null
    );
    exit();
}




// Now just update the password.
try {
    $query = "UPDATE users
              SET user_password = ?
              WHERE user_id = ?";
    $queryResult = $databaseConnection->executeQuery($query, [$userNewPassword, $userId], 'si');
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