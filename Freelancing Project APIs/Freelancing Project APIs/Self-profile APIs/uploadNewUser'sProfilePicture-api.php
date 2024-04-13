
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
if (!Utilities::checkJsonKeysMatch($requestBody, ["user_id", "user_new_profile_picture_base64encoding_string"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, ".
                                    "or the keys are not in the expected order.", null);
    exit();
}

// Get the user ID and the profile picture in base64 encoding from the request body
$userId = $requestBody['user_id'];
$profilePictureInBase64Encoding = $requestBody['user_new_profile_picture_base64encoding_string'];










// Check if the user exists in the platform
$helper->connectToMySQLDatabase();
$userExistsInThePlatform = mysqli_fetch_assoc($helper->executeQuery(
    "SELECT COUNT(*)
     FROM `db_a993c8_freelan`.`users`
     WHERE user_id = {$userId}"))["COUNT(*)"];

if ($userExistsInThePlatform != 1) {
    $helper->sendRequestBody(false, "We don't have any user with the provided user_id in the platform.", null);
    exit();
}










// Decode the profile picture from base64 encoding
$profilePictureBytesString = null;
try {
    $profilePictureBytesString = base64_decode($profilePictureInBase64Encoding);
    
    if (($profilePictureBytesString === false) || ($profilePictureBytesString == null)) {
        http_response_code(400);
        $helper->sendRequestBody(false, "The profile picture data is not in a valid base64 format.", null);
        exit();
    }
}
catch (Exception $exception) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The profile picture data is not in a valid base64 format.", null);
    exit();
}










// Save the profile picture to the file system
$userProfilePicturePath = "C:/Users/Tareq/Desktop/Freelancing Project Assets/Profile Pictures/{$userId}.jpg";
try {
    if (file_put_contents($userProfilePicturePath, $profilePictureBytesString) === false) {
        http_response_code(400);
        $helper->sendRequestBody(false, "An unknown problem occurred on the server-side file system while ".
                                        "writing the profile picture file, please try again.", null);
        exit();
    }
}
catch (Exception $exception) {
    http_response_code(400);
    $helper->sendRequestBody(false, "An unknown problem occurred on the server-side file system while ".
                                    "writing the profile picture file, please try again.", null);
    exit();
}










// Update the user profile picture path in the database
$helper->executeQuery(
    "UPDATE `db_a993c8_freelan`.`users` 
     SET user_profile_picture_path = '{$userProfilePicturePath}'
     WHERE user_id = {$userId}");










// Send the success response
$helper->sendRequestBody(true, "Ok", null);

?>