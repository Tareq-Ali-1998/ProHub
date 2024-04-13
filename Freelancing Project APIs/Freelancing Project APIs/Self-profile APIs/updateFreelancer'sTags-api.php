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

if (!Utilities::checkJsonKeysMatch($requestBody, ["freelancer_id", "freelancer_tags"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order.", null);
    exit();
}

$freelancerId = $requestBody["freelancer_id"];
$newFreelancerTags = $requestBody["freelancer_tags"];










if (count($newFreelancerTags) > 25) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The maximum number of tags allowed for a freelancer is 25.", null);
    exit();
}










$helper->connectToMySQLDatabase();
// Check if all the provided tags exist in the tags table
$existingTagsCounter = mysqli_fetch_assoc($helper->executeQuery(
                       "SELECT COUNT(*)
                        FROM `db_a993c8_freelan`.`tags`
                        WHERE `tag_name` IN ('" . implode("', '", $newFreelancerTags) . "')"))["COUNT(*)"];

if ($existingTagsCounter != count($newFreelancerTags)) {
    http_response_code(400);
    $helper->sendRequestBody(false, "One or more of the provided tags do not exist in the system.", null);
    exit();
}










// Delete all the existing tags for the given freelancer
$queryData = $helper->executeQuery(
    "DELETE FROM `db_a993c8_freelan`.`freelancer_tags`
     WHERE `freelancer_id` = $freelancerId");










// Insert the new tags for the given freelancer
$queryData = $helper->executeQuery(
    "INSERT INTO `db_a993c8_freelan`.`freelancer_tags` (`freelancer_id`, `tag_id`)
     SELECT $freelancerId, `tag_id`
     FROM `db_a993c8_freelan`.`tags`
     WHERE `tag_name` IN ('" . implode("', '", $newFreelancerTags) . "')");

$helper->sendRequestBody(true, "Ok", null);