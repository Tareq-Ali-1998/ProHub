<?php

include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php';
include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/ChatGPT.php';


$helper = new APIHandler();

$helper->authorizeRequestUsernameAndPassword();
$requestBody = $helper->getRequestBody();
if ($requestBody === null) {
    $helper->sendRequestBody(false, "This API expects JSON data, but it found nothing.", null);
    exit();
}

if (!Utilities::checkJsonKeysMatch($requestBody, ["freelancer_id", "freelancer_brief_description",
                                                  "freelancer_description"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, 'The request body is missing one or more required JSON keys, or '.
                                  'the keys are not in the expected order', null);
    exit();
}

// *********************************************
// TODO: It's a good idea here to make a function that validates the variables types.
// *********************************************
$freelancerId = $requestBody["freelancer_id"];
$freelancerDescription = $requestBody["freelancer_description"];
$freelancerBriefDescription = $requestBody["freelancer_brief_description"];



$currentTime = Utilities::getCurrentDateTime();


$helper->connectToMySQLDatabase();
$numberOfChatGPTAPICallsForThisUser = mysqli_fetch_assoc($helper->executeQuery(
"SELECT COUNT(*)
FROM
chatgpt_api_messages
WHERE user_id = '$freelancerId'
AND TIMESTAMPDIFF(MINUTE, message_date, '$currentTime') < 1440"))['COUNT(*)'];

if ($numberOfChatGPTAPICallsForThisUser > 10) {
    $helper->sendRequestBody(true, "You are allowed to use this feature only 10 times a day.", null);
    exit();

}


$chatGPT = new ChatGPT('gpt-3.5-turbo');

// Here $tags is an indexed array of string, it may contain zero to 10 tags at most
$tags = $chatGPT->getFreelancerTags("Freelancer Brief Description:\n".$freelancerBriefDescription."\n\nFreelancer Description:\n".$freelancerDescription);

$requestContent = $chatGPT->getLastRequestString();
$responseContent = $chatGPT->getLastResponseString();

/*
$helper->executeQuery(
"INSERT INTO chatgpt_api_messages (user_id, message_date, request_content, response_content, for_job_categorization)
 VALUES ({$freelancerId}, {$currentTime}, {$requestContent}, {$responseContent}, 0);");
*/

if (($tags == null) || ($tags == [])) {
    $helper->sendRequestBody(true, "We are sorry, our AI system couldn't generate any tag from the above ".
                                   "submitted data, please read our user manual and try again", []);
    exit();
}

$helper->sendRequestBody(true, "Ok", $tags);


?>