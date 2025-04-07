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


if (!Utilities::checkJsonKeysMatch($requestBody, ['user_id', 'user_first_name', 'user_last_name',
                                                  'user_phone_number', 'user_gender', 'user_city',
                                                  'user_date_of_birth', 'user_specific_address'])) {
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
$userFirstName = $requestBody['user_first_name'];
$userLastName = $requestBody['user_last_name'];
$userPhoneNumber = $requestBody['user_phone_number'];
$userGender = $requestBody['user_gender'];
$userCity = $requestBody['user_city'];
$userDateOfBirth = $requestBody['user_date_of_birth'];
$userSpecificAddress = $requestBody['user_specific_address'];


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



/////////////////////////////
// Decode the profile picture from base64 encoding to a string of bytes.
$userProfilePictureBytesString = null;
try {
    $userProfilePictureBytesString = base64_decode($profilePictureInBase64Encoding);
    
    if (($userProfilePictureBytesString == null)  ||
        ($userProfilePictureBytesString === false)) {
        HTTPHandler::sendResponse(
            415,
            false,
            'The profile picture data is not in a valid base64 format.',
            null
        );
        exit();
    }
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(
        415,
        false,
        'The profile picture data is not in a valid base64 format.',
        null
    );
    exit();
}




// Save the profile picture to the file system
$userProfilePicturePath = PROFILE_PICTURES_PATH."/$userId.jpg";
try {
    if (file_put_contents($userProfilePicturePath, $profilePictureBytesString) === false) {
        HTTPHandler::sendResponse(
            415,
            false,
            'The profile picture data is not in a valid base64 format.',
            null
        );
        exit();
    }
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(
        415,
        false,
        'The profile picture data is not in a valid base64 format.',
        null
    );
    exit();
}
/////////////////

 

// Finally, just update the database.
try {
    $query = "UPDATE users
              SET user_profile_picture_name = ?
              WHERE user_id = ?";
    $queryResult = $databaseConnection->executeQuery($query, [$userId, $userId], 'ii');
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