
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

if (!Utilities::checkJsonKeysMatch($requestBody, ["freelancer_description"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order", null);
    exit();
}

// *********************************************
// TODO: It's a good idea here to make a function that validates the variables types.
// *********************************************
$freelancerDescription = $requestBody["freelancer_description"];



$chatGPT = new ChatGPT('gpt-3.5-turbo');

// Here $tags is an indexed array of string, it may contain zero to 10 tags at most
$tags = $chatGPT->getFreelancerTags("\nFreelancer Description:\n".$freelancerDescription);

if ($tags == []) {
    $helper->sendRequestBody(true, "We are sorry, our AI system couldn't generate any tag from the above ".
                                   "submitted job describtion data, please read our user manual and try again", []);
    exit();
}

$helper->sendRequestBody(true, "Ok", $tags);



?>