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


if (!Utilities::checkJsonKeysMatch($requestBody, ['freelancer_id', 'freelancer_tags'])) {
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
$newFreelancerTags = $requestBody['freelancer_tags'];


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




// Now checking if the number of the provided tags is acceptable.
if (count($newFreelancerTags) > 30) {
    HTTPHandler::sendResponse(
        200,
        true,
        'The maximum number of tags for a freelancer is 30.',
        null
    );
    exit();
}




// Here checking for the tags uniqueness.
sort($newFreelancerTags);
for ($i = 1; $i < count($newFreelancerTags); $i++) {
    if ($newFreelancerTags[$i] == $newFreelancerTags[$i - 1]) {
        HTTPHandler::sendResponse(
            200,
            true,
            'The provided tags should be unique.',
            null
        );
        exit();
    }
}




// Check if all the provided tags exist in the system.
if (count($newFreelancerTags) > 0) {
    $placeholders = implode(',', array_fill(0, count($newFreelancerTags), '?'));
    $query = "SELECT tag_id FROM tags WHERE tag_name IN ($placeholders)";
    $paramTypes = str_repeat('s', count($newFreelancerTags));
    
    try {
        $queryResult = $databaseConnection->executeQuery($query, $newFreelancerTags, $paramTypes);
        $existingTagIds = $queryResult->fetch_all(MYSQLI_ASSOC);
    }
    catch (Exception $exception) {
        HTTPHandler::sendResponse(500, false, $exception->getMessage(), null);
        exit();
    }
    
    // Verify all tags exist.
    if (count($existingTagIds) !== count($newFreelancerTags)) {
        HTTPHandler::sendResponse(
            200,
            true,
            'One or more tags do not exist in the system.',
            null
        );
        exit();
    }
    
    // Extract tag IDs.
    $tagIds = array_column($existingTagIds, 'tag_id');
}
else {
    $tagIds = [];
}

// Delete the old tags and insert the new ones within a transaction.
try {
    $databaseConnection->beginTransaction();
    
    // Delete existing tags for the freelancer.
    $deleteQuery = "DELETE FROM freelancer_tags WHERE freelancer_id = ?";
    $databaseConnection->executeQuery($deleteQuery, [$freelancerId], 'i');
    
    // Insert new tags if any.
    if (!empty($tagIds)) {
        $valuesPlaceholder = implode(',', array_fill(0, count($tagIds), '(?, ?)'));
        $insertQuery = "INSERT INTO freelancer_tags (freelancer_id, tag_id)
                        VALUES $valuesPlaceholder";
        
        // Prepare parameters and types.
        $params = [];
        $paramTypes = '';
        foreach ($tagIds as $tagId) {
            $params[] = $freelancerId;
            $params[] = $tagId;
            $paramTypes .= 'ii';
        }
        
        $databaseConnection->executeQuery($insertQuery, $params, $paramTypes);
    }
    
    $databaseConnection->commitTransaction();
}
catch (Exception $exception) {
    $databaseConnection->rollbackTransaction();
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