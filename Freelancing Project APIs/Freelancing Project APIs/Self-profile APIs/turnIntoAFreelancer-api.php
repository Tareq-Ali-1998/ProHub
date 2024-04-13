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

if (!Utilities::checkJsonKeysMatch($requestBody, ["freelancer_id", "freelancer_brief_description",
                                                  "freelancer_description", "freelancer_hourly_rate", 
                                                  "freelancer_tags", "profile_picture_base64encoding_string"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, ".
                                    "or the keys are not in the expected order.", null);
    exit();
}

// Get the freelancer's data from the request body
$freelancerId = $requestBody["freelancer_id"];
$freelancerBriefDescription = $requestBody["freelancer_brief_description"];
$freelancerDescription = $requestBody["freelancer_description"];
$freelancerHourlyRate = $requestBody["freelancer_hourly_rate"];
$freelancerTags = $requestBody["freelancer_tags"];
$freelancerProfilePictureInBase64Encoding = $requestBody["profile_picture_base64encoding_string"];










$helper->connectToMySQLDatabase();
// Check if the provided user ID is registered in our platform
$queryData = mysqli_fetch_assoc($helper->executeQuery("SELECT `db_a993c8_freelan`.`users`.user_id
                                                       FROM `db_a993c8_freelan`.`users`
                                                       WHERE `db_a993c8_freelan`.`users`.user_id = '$freelancerId'"));
if (!isset($queryData["user_id"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "We don't have any user with this id in the platform.", null);
    exit();
}



// Now handling all the tags possible errors
if (count($freelancerTags) > 25) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The maximum number of tags allowed for a freelancer is 25.", null);
    exit();
}

// Check if all the provided tags exist in the tags table
$existingTagsCounter = mysqli_fetch_assoc($helper->executeQuery(
                       "SELECT COUNT(*)
                        FROM `db_a993c8_freelan`.`tags`
                        WHERE `tag_name` IN ('" . implode("', '", $freelancerTags) . "')"))["COUNT(*)"];

if ($existingTagsCounter != count($freelancerTags)) {
    http_response_code(400);
    $helper->sendRequestBody(false, "One or more of the provided tags do not exist in the system.", null);
    exit();
}








// Now handling the profile picture
// Decode the profile picture from base64 encoding
$profilePictureBytesString = null;
try {
    $profilePictureBytesString = base64_decode($freelancerProfilePictureInBase64Encoding);
    
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
$userProfilePicturePath = "C:/Users/Tareq/Desktop/Freelancing Project Assets/Profile Pictures/{$freelancerId}.jpg";
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
     WHERE user_id = {$freelancerId}");















// Insert the new tags for the given freelancer
$queryData = $helper->executeQuery(
    "INSERT INTO `db_a993c8_freelan`.`freelancer_tags` (`freelancer_id`, `tag_id`)
     SELECT $freelancerId, `tag_id`
     FROM `db_a993c8_freelan`.`tags`
     WHERE `tag_name` IN ('" . implode("', '", $freelancerTags) . "')");















// Now after all, inserting the main information about the new freelancer
$queryData = $helper->executeQuery("INSERT INTO `db_a993c8_freelan`.`freelancers` (freelancer_id,
                                    freelancer_brief_description, freelancer_hourly_rate, freelancer_description)
                                    VALUES ({$freelancerId}, '$freelancerBriefDescription', 
                                            {$freelancerHourlyRate}, '$freelancerDescription')");


// Send the response
$helper->sendRequestBody(true, "Ok", null);