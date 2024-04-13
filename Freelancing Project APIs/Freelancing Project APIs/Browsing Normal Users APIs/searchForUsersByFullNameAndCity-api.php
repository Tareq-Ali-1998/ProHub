<?php

include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php';

$helper = new APIHandler();


$helper->authorizeRequestUsernameAndPassword();
$requestBody = $helper->getRequestBody();
if ($requestBody === null) {
    $helper->sendRequestBody(false, "This API expects JSON data, but it found nothing.", null);
    exit();
}

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_id"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order", null);
    exit();
}

// *********************************************
// TODO: It's a good idea to make a function that validates the variables types, like here it
// should be int as $userID.
// *********************************************
$userId = $requestBody['user_id'];




$helper->connectToMySQLDatabase();

/*
 * 
 * Note that this query retrieves the normal users only.
 * 
 * The query can be optimized using composite indexing on the columns we are filtering according to.
 * 
 * I am satisfied with the current time complexity, so to remind myself in the future, I can run
 * the EXPLAIN ANALYZE FORMAT = TREE query to check that this query is the best query that could
 * be written in terms of performance because it retrieves only the needed data without scanning any
 * other data from any record that is not involved in the final result set ;).
 *  
*/


// The query is not ready yet and it's not valid and I need to use the Fulltext index for sure.
$databaseQuery = 
"SELECT
user_id
user_first_name,
user_last_name,
user_gender,
user_city,
user_profile_picture_path
FROM users
WHERE user_id
IN (SELECT favorite_user_id
    FROM user_favorite_users
    WHERE user_id = '$userId'
    ORDER BY favorite_user_adding_date DESC)";


$queryResult = $helper->executeQuery($databaseQuery);
$rows = array();
$message = "Ok";
while ($row = mysqli_fetch_assoc($queryResult)) {

    // Puplisher Profile Picture handling
    $row['user_profile_picture'] = null;
    if (($row["user_profile_picture_path"] != null) && (file_exists($row['user_profile_picture_path']))) {
        try {
            // Encode the publisher's profile picture as a base64 string and store it in the $row associative array.
            $row['user_profile_picture'] = base64_encode(file_get_contents($row['user_profile_picture_path']));
        }
        catch (Exception $exception) {
            $message = "Ok, but there was a problem loading the publisher profile picture.\n";
            // $message.= $exception->getMessage();
            $row['user_profile_picture'] = null;
        }
    }
    unset($row["user_profile_picture_path"]);

    $rows[] = $row;
}

$helper->setHeadersForTheResponse();
$helper->sendRequestBody(true, "Ok", $rows);

?>