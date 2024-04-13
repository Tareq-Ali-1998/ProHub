




<?php

include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php';










$helper = new APIHandler();

$helper->authorizeRequestUsernameAndPassword();










$requestBody = $helper->getRequestBody();
// Check if the request body exists and contains the required keys
if ($requestBody === null) {
    http_response_code(400);
    $helper->sendRequestBody(false, "This API expects JSON data, but it found nothing.", null);
    exit();
}
if (!Utilities::checkJsonKeysMatch($requestBody, ["user_id"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, ".
                                    "or the keys are not in the expected order.", null);
    exit();
}

$userId = $requestBody['user_id'];










$helper->connectToMySQLDatabase();

$userExistsInThePlatform = mysqli_fetch_assoc($helper->executeQuery(
                                "SELECT COUNT(*)
                                 FROM `db_a993c8_freelan`.`users`
                                 WHERE user_id = {$userId}"))["COUNT(*)"];

if ($userExistsInThePlatform != 1) {
    $helper->sendRequestBody(false, "We don't have any user with this user_id in the platform.", null);
    exit();
}










$userProfilePicturePath = mysqli_fetch_assoc($helper->executeQuery(
    "SELECT `db_a993c8_freelan`.`users`.user_profile_picture_path
     FROM `users`
     WHERE user_id = {$userId}"))["user_profile_picture_path"];


if ($userProfilePicturePath == null) {
    // Mahmoud, here you should assign to the user the default profile picture according to his gender.
    $helper->sendRequestBody(true, "This user doesn't have a profile picture yet.", null);
    exit();
}










if (file_exists($userProfilePicturePath)) {
    try {
        $userProfilePictureInBase64Encoding = base64_encode(file_get_contents($userProfilePicturePath));
        if ($userProfilePictureInBase64Encoding == null) {
            // Mahmoud, here you should assign to the user the default profile picture according to his gender.
            $helper->sendRequestBody(true, "An unknown problem occurred in the server-side file system while ".
                                            "reading the profile picture file, please try again.", null);
            exit();
        }
        else {
            $helper->sendRequestBody(true, "Ok", array("user_profile_picture_base64encoding_string" => 
                                                                        $userProfilePictureInBase64Encoding));
            exit();
        }
    }
    catch (Exception $exception) {
        // Mahmoud, here you should assign to the user the default profile picture according to his gender.
        $helper->sendRequestBody(true, "An unknown problem occurred in the server-side file system while ".
                                        "reading the profile picture file, please try again.", null);
        exit();
    }
}
else {
    // Mahmoud, here you should assign to the user the default profile picture according to his gender.
    $helper->sendRequestBody(false, "A problem occurred on the server-side which is that the picture file ".
                                    "of the corresponding user profile picture path doesn't exist anymore ".
                                    "on the server's file system.", null);
    exit();
}

?>
