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


if (!Utilities::checkJsonKeysMatch($requestBody, ['user_email', 'user_date_of_birth',
                                                  'user_gender', 'user_city',
                                                  'user_specific_address',
                                                  'user_phone_number'])) {
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
$userDateOfBirth = $requestBody['user_date_of_birth'];
$userGender = $requestBody['user_gender'];
$userCity = $requestBody['user_city'];
$userSpecificAddress = $requestBody['user_specific_address'];
$userPhoneNumber = $requestBody['user_phone_number'];


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




// Now checking if the user passed the signup1 phase, and if yes, I have
// to check if he had successfully verified his email or not.
try {
    $query = "SELECT *
              FROM incomplete_registration_users
              WHERE user_email = ?";
    $queryResult = $databaseConnection->executeQuery($query, [$userEmail], 's');
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}
if ($queryResult->num_rows == 0) {
    HTTPHandler::sendResponse(
        200,
        true,
        "The email address you are trying to register with should pass " .
        'the signup1 phase first.',
        null
    );
    exit();
}
$queryData = $queryResult->fetch_assoc();
$userEmailVerificationStatus = $queryData['user_email_verification_status'];
if (!$userEmailVerificationStatus) {
    HTTPHandler::sendResponse(
        200,
        true,
        'This email address has already received the required verification ' .
        "code but hasn't verified it yet.",
        null
    );
    exit();
}




// Checking if the user's phone number isn't already in use by another
// account in the system.
try {
    $query = "SELECT COUNT(*)
              FROM users
              WHERE user_phone_number = ?";
    $queryResult = $databaseConnection->executeQuery($query, [$userPhoneNumber], 's');
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}
$weHaveAUserWithThisSamePhoneNumber = $queryResult->fetch_assoc()['COUNT(*)'];
if ($weHaveAUserWithThisSamePhoneNumber) {
    HTTPHandler::sendResponse(
        200,
        true,
        'The provided phone number already exists in our system ',
        null
    );
    exit();
}



// Now if the code execution reaches this point, then I should withdraw the user
// record from the `incomplete_registration_users` table and insert all the combined
// data to the `users` table, which means that the user has been registered successfully
// on our system.
$userFirstName = $queryData['user_first_name'];
$userLastName = $queryData['user_last_name'];
$userPassword = $queryData['user_password'];
$userRegistrationDate = $queryData['user_registration_date'];
try {
    $databaseConnection->beginTransaction();

    $query = "DELETE FROM incomplete_registration_users 
              WHERE user_email = ?";
    $databaseConnection->executeQuery($query, [$userEmail], 's');

    $query = "INSERT INTO users (user_first_name,
                                 user_last_name,
                                 user_email,
                                 user_email_visibility,
                                 user_phone_number,
                                 user_phone_number_visibility,
                                 user_password,
                                 user_date_of_birth,
                                 user_gender,
                                 user_account_creation_date,
                                 user_city,
                                 user_specific_address)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; // 12 params
    $databaseConnection->executeQuery(
        $query,
        [
            $userFirstName,
            $userLastName,
            $userEmail,
            1,  // ← INT
            $userPhoneNumber,
            1,  // ← INT
            $userPassword,
            $userDateOfBirth,
            $userGender,
            $userRegistrationDate,
            $userCity,
            $userSpecificAddress
        ],
        'sssisissssss'
    );
    
    $databaseConnection->commitTransaction();
}
catch (Exception $exception) {
    $databaseConnection->rollbackTransaction();
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}




// The final step is to return all the following user's data.
try {
    $query = "SELECT *
              FROM users
              WHERE user_email = ?";
    $queryResult = $databaseConnection->executeQuery($query, [$userEmail], 's');
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}
$data = $queryResult->fetch_assoc();
unset($data['user_profile_picture_name']);
unset($data['user_password']);
$data['user_profile_picture'] = null;
$data['is_freelancer'] = false;
$data["number_of_followers"] = 0;




HTTPHandler::sendResponse(
    200,
    true,
    'OK',
    $data
);

?>