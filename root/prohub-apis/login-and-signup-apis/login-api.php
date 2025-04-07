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


if (!Utilities::checkJsonKeysMatch($requestBody, ['user_email', 'user_password'])) {
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
$userEmail = $requestBody["user_email"];
$userPassword = $requestBody["user_password"];


try {
    $databaseConnection = new DatabaseConnection();
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}




// Now checking if the email address is semi registered in our system or not.
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
if ($queryResult->num_rows != 0) {
    $userRegistrationStatus = $queryResult->fetch_assoc()['user_email_verification_status'];
    if ($userRegistrationStatus == 1) {
        $data = array("user_email" => $userEmail);
        try {
            $query = "SELECT user_first_name, user_last_name, user_password
                      FROM incomplete_registration_users
                      WHERE user_email = ?";
            $queryResult = $databaseConnection->executeQuery($query, [$userEmail], 's');
        }
        catch (Exception $exception) {
            HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
            exit();
        }
        $data += $queryResult->fetch_assoc();
        HTTPHandler::sendResponse(
            200,
            true,
            'Here we should send the user directly to the signup2 phase '.
            'to continue his rigestration.',
            $data
        );
        exit();
    }
    else {
        HTTPHandler::sendResponse(
            200,
            true,
            'The user of the email address you entered has received an email '.
            "verification code but hasn't used it to verify his email yet.", 
            null
        );
        exit();
    }
}




// Now checking if the email address is fully registered in our system or not.
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
$thisEmailIsFullyRegistered = $queryResult->fetch_assoc()['COUNT(*)'];
if (!$thisEmailIsFullyRegistered) {
    HTTPHandler::sendResponse(
        200,
        true,
        'The email you entered is not registered in our system, please '.
        'check your email address or create a new account.',
        null 
    );
    exit();
}




// Now if the code execution will reach this point, then definitely the
// user is fully registered in our system and I should return his data.
$message = "OK.";
// Now checking if the password is correct or not.
// Note that currently I am not using the following commented part
// related to the hashed password.
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
$userHashedPasswordFromTheDatabase = $data['user_password'];
if ($userHashedPasswordFromTheDatabase != $userPassword) {
    // Maybe we will consider resetting the user's password by sending a verification
    // code to his email in the future by clicking the button forgot my password.
    HTTPHandler::sendResponse(
        200,
        true,
        'Incorrect password, please try again.',
        null
    );
    exit();
}
/*
if (password_verify($userPassword, $userHashedPasswordFromTheDatabase)) {
    // Maybe we will consider resetting the user's password by sending a verification
    // code to his email in the future by clicking the button forgot my password.
    HTTPHandler::sendResponse(
        200,
        true,
        "Incorrect password, please try again.",
        null
    );
    exit();
}
*/




// Now getting the number of followers for this user.
try {
    $query = "SELECT COUNT(*)
              FROM user_favorite_users
              WHERE favorite_user_id = ?";
    $queryResult = $databaseConnection->executeQuery($query, [$data['user_id']], 'i');
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}
$data['number_of_followers'] = $queryResult->fetch_assoc()['COUNT(*)'];




// Now loading the user's profile picture of the user.
$userProfilePictureName = $data['user_profile_picture_name'];
if ($userProfilePictureName == null) {
    $userProfilePictureName = "nothing";
}
$userProfilePicturePath = Utilities::getUserProfilePicturePath($userProfilePictureName);
unset($data['user_profile_picture_name']);
if (file_exists($userProfilePicturePath)) {
    try {
        $userProfilePictureInBase64Encoding = base64_encode(file_get_contents($userProfilePicturePath));
        if ($userProfilePictureInBase64Encoding == null) {
            $message = "\nOK, but there was a problem loading the user's profile picture.";
            $data['user_profile_picture'] = null;
        }
        $data['user_profile_picture'] = $userProfilePictureInBase64Encoding;
    }
    catch (Exception $exception) {
        $message = "\nOK, but there was a problem loading the user's profile picture.";
        $data['user_profile_picture'] = null;
    }
}
else {
    $message = "\nOK, but there was a problem loading the user's profile picture.";
    $data['user_profile_picture'] = null;
}




// Now checking if the user is a freelancer and not just a normal
// user, and if so, then fetch more data about him.
$data['is_freelancer'] = false;
try {
    $query = "SELECT *
              FROM freelancers
              WHERE freelancers.freelancer_id = ?";
    $queryResult = $databaseConnection->executeQuery($query, [$data['user_id']], 'i');
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}
if ($queryResult->num_rows == 1){
    $data['is_freelancer'] = true;
    $data += $queryResult->fetch_assoc();
    // The following unset function is that because the user_id is the same as
    // the freelancer_id, so it makes no sense to send it in the response.
    unset($data['freelancer_id']);
    
    // Now fetching the freelancer's tags.
    try {
        $query = "SELECT tag_name
                  FROM tags
                  WHERE tags.tag_id IN (
                      SELECT tag_id
                      FROM freelancer_tags
                      WHERE freelancer_id = ?)";
        $queryResult = $databaseConnection->executeQuery($query, [$data['user_id']], 'i');
    }
    catch (Exception $exception) {
        HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
        exit();
    }
    $freelancerTags = null;
    while ($row = $queryResult->fetch_assoc()) {
        $freelancerTags[] = $row['tag_name'];
    }
    $data['freelancer_tags'] = $freelancerTags;

    // Now loading the freelancer's PDFs one by one ordered by their pdf_order_number.
    // Note that I am either keep all the freelancer's PDFs or none of them.
    try {
        $query = "SELECT pdf_name
                  FROM freelancer_pdfs
                  WHERE freelancer_id = ?
                  ORDER BY pdf_order_number ASC";
        $queryResult = $databaseConnection->executeQuery($query, [$data['user_id']], 'i');
    }
    catch (Exception $exception) {
        HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
        exit();
    }
    $freelancerPDFs = array();
    while ($row = $queryResult->fetch_assoc()) {
        $pdfName = $row['pdf_name'];
        $pdfPath = Utilities::getPDFPathFromItsUserIdAndName($data['user_id'], $pdfName);
        if (file_exists($pdfPath)) {
            try {
                $pdfFileInBase64Encoding = base64_encode(file_get_contents($pdfPath));
                if ($pdfFileInBase64Encoding == null) {
                    $message .= "\nOk, but there was a problem loading the freelancer's PDFs.";
                    $freelancerPDFs = null;
                    break;
                }
                $freelancerPDFs[$pdfName] = $pdfFileInBase64Encoding;
            }
            catch (Exception $exception) {
                $message .= "\nOk, but there was a problem loading the freelancer's PDFs.";
                $freelancerPDFs = null;
                break;
            }
        }
        else {
            $message .= "\nOk, but there was a problem loading the freelancer's PDFs.";
            $freelancerPDFs = null;
            break;
        }
    }
    $data['freelancer_pdfs'] = $freelancerPDFs;
}




HTTPHandler::sendResponse(
    200,
    true,
    $message,
    $data
);

?>