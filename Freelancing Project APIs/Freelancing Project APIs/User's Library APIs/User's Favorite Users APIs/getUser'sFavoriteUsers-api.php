<?php

include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php';

$helper = new APIHandler();


$helper->authorizeRequestUsernameAndPassword();
$requestBody = $helper->getRequestBody();
if ($requestBody === null) {
    $helper->sendRequestBody(false, "This API expects JSON data, but it found nothing.", null);
    exit();
}

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_id", "requested_users_number", "previous_date"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order", null);
    exit();
}

// *********************************************
// TODO: It's a good idea to make a function that validates the variables types, like here it
// should be int as $userId.
// *********************************************
$userId = $requestBody['user_id'];
$requestedJobsNumber = $requestBody['requested_users_number'];
/* Here $previousDate is the date on which I will retrieve directly the next $requested_users_number jobs with dates
 * greater than $previousDate (And definitely the jobs are marked as favorite by the use with the provided
 * user_id), where the jobs are sorted according to the date in which the user marked the job as favorite in
 * such a way that the recently added jobs to his favorite list appearing first.
 */
$previousDate = $requestBody['previous_date'];




$helper->connectToMySQLDatabase();

/*
 *
 * This query retrieves information about the user's favorite users, returning various details related
 * to each one of them.
 * 
 * To retrieve the required data, only the user's Id is needed.
 * 
 * Note that this query retrieves the normal users and the freelancers too.
 * 
 * The results of the query are ordered based on the date of the favorite relationships with the newest date
 * of a relationship appears first.
 * 
 * I am satisfied with the current time complexity, so to remind myself in the future, I can run
 * the EXPLAIN ANALYZE FORMAT = TREE query to check that this query is the best query that could
 * be written in terms of performance because it retrieves only the needed data without scanning any
 * other data from any record that is not involved in the final result set ;).
 *  
*/

$databaseQuery = 
"SELECT
user_id,
user_first_name,
user_last_name,
user_gender,
user_city,
user_profile_picture_path,
freelancer_id,
freelancer_rate,
freelancer_hourly_rate,
freelancer_brief_description
FROM users
LEFT JOIN freelancers ON user_id = freelancer_id
WHERE user_id
IN (SELECT favorite_user_id
    FROM user_favorite_users
    WHERE user_id = '$userId'
    AND favorite_user_adding_date > '$previousDate'
    ORDER BY favorite_user_adding_date DESC
    )
LIMIT {$requestedJobsNumber}
";


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

    $row['is_freelancer'] = true;
    if ($row["freelancer_id"] == null) {
        $row['is_freelancer'] = false;
        unset($row['freelancer_rate']);
        unset($row['freelancer_hourly_rate']);
        unset($row['freelancer_brief_description']);
    }

    $row['is_favorite_user'] = true;

    $numberOfFollowers = mysqli_fetch_assoc($helper->executeQuery(
        "SELECT COUNT(*)
         FROM `db_a993c8_freelan`.user_favorite_users 
         WHERE `db_a993c8_freelan`.`user_favorite_users`.favorite_user_id = {$userId}"))["COUNT(*)"];
    
    $row["number_of_followers"] = $numberOfFollowers;

    $rows[] = $row;
}

$helper->setHeadersForTheResponse();
$helper->sendRequestBody(true, "Ok", $rows);

?>