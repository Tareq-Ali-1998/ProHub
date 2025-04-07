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


if (!Utilities::checkJsonKeysMatch($requestBody, ['freelancer_id', 'freelancer_brief_description'])) {
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
$freelancerId = $requestBody['freelancer_id'];
$freelancerBriefDescription = $requestBody['freelancer_brief_description'];


try {
    $databaseConnection = new DatabaseConnection();
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}




// Now checking if the provided freelancer_id is already registered in our system.
try {
    $query = "SELECT freelancer_id
              FROM freelancers
              WHERE freelancer_id = ?";
    $queryResult = $databaseConnection->executeQuery($query, [$freelancerId], 'i');
}
catch (Exception $exception) {
    HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
    exit();
}
if ($queryResult->num_rows == 0) {
    HTTPHandler::sendResponse(
        200,
        true,
        "We don't have any freelancer with the provided freelancer_id.",
        null
    );
    exit();
}




// Now just update the freelancer's brief description and return the success response.
try {
    $query = "UPDATE freelancers
              SET freelancer_brief_description = ?
              WHERE freelancer_id = ?";
    $queryResult = $databaseConnection->executeQuery(
        $query, [$freelancerBriefDescription, $freelancerId],
        'si'
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