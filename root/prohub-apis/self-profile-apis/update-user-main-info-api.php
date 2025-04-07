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
                                                  'user_profile_picture_in_base64encoding'])) {
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
$userProfilePictureInBase64Encoding = $requestBody['user_profile_picture_in_base64encoding'];


try {
    $databaseConnection = new DatabaseConnection();
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}




// Now checking if the provided user_id is already registered in our system.
try {
    $query = "SELECT user_id
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




// Now checking for the uniqueness of the provided phone number.
try {
    $query = "SELECT user_id
              FROM users
              WHERE (user_phone_number = ? AND user_id != ?)";
    $queryResult = $databaseConnection->executeQuery($query, [$userPhoneNumber, $userId], "ii");
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}
if ($queryResult->num_rows == 0) {
    HTTPHandler::sendResponse(
        200,
        true,
        'The phone number you are trying to use is currently being used by another account.',
        null
    );
    exit();
}




// Finally updating the user's main info and return the success response.
try {
    $query = "UPDATE users
              SET user_first_name = ?,
                  user_last_name = ?,
                  user_specific_address = ?,
                  user_gender = ?,
                  user_city = ?,
                  user_date_of_birth = ?,
                  user_phone_number = ?
              WHERE user_id = ?"; // 8 params

    $databaseConnection->executeQuery(
        $query,
        [
            $userFirstName,
            $userLastName,
            $userSpecificAddress,
            $userGender,
            $userCity,
            $userDateOfBirth,
            $userPhoneNumber,
            $userId
        ],
        'ssssssii'
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