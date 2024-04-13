<?php

include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php';

$helper = new APIHandler();


$helper->authorizeRequestUsernameAndPassword();
$requestBody = $helper->getRequestBody();
if ($requestBody === null) {
    $helper->sendRequestBody(false, "This API expects JSON data, but it found nothing.", null);
    exit();
}

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_id", "requested_jobs_number", "previous_date",
                                                  "price_first", "price_second", "tags_array"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, 'The request body is missing one or more required JSON keys, or '.
                                    'the keys are not in the expected order', null);
    exit();
}

// *********************************************
// TODO: It's a good idea here to make a function that validates the variables types.
// *********************************************
$userId = $requestBody['user_id'];
$requestedJobsNumber = $requestBody['requested_jobs_number'];
// Here $previousDate is the date on which I will retrieve directly the next $requestedJobsNumber jobs with dates
// greater than $previousDate, where the most recent jobs appearing first
$previousDate = $requestBody['previous_date'];

$priceFirst = $requestBody['price_first'];
$priceSecond = $requestBody['price_second'];
$tagsArray = $requestBody['tags_array'];


// *********************************************
// TODO: It's a good idea to make a function that validates the variables (the type of the values in the 
//       JSON response) one by one in order, and then I should do that for every API, equavalent to the following:
//       if ((gettype($rangeLeft) != "integer") || (gettype($rangeRight) != "integer")) {}
// *********************************************



$maximumNumberOfReturnedJobsViaOneAPICall = 20;
if ($requestedJobsNumber > $maximumNumberOfReturnedJobsViaOneAPICall) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The number of jobs you are asking for is greater than ".
                             $maximumNumberOfReturnedJobsViaOneAPICall.".", null);
    exit();
}

$helper->connectToMySQLDatabase();

/*
 * 
 * To remind myself in the future, I can run the EXPLAIN ANALYZE FORMAT = TREE query to check that this query is the
 * best query that could be written in terms of performance because it retrieves only the needed data without
 * scanning any other data from any record that is not involved in the final result set;).
*/

$databaseQuery = 
"SELECT 
    table1.job_id, 
    table1.publisher_id, 
    table1.publisher_first_name, 
    table1.publisher_last_name,
    table1.publisher_profile_picture_path,
    table1.job_title, 
    table1.job_description, 
    table1.job_creation_date,
    table1.job_deadline_date, 
    table1.job_price,
    table1.job_completion_date,
    table1.job_completion_rating,
    table1.job_completion_message,
    table1.is_favorite,
    (	
        SELECT GROUP_CONCAT(DISTINCT `db_a993c8_freelan`.`tags`.tag_name SEPARATOR ', ')
        FROM `db_a993c8_freelan`.`job_tags`
        LEFT JOIN `db_a993c8_freelan`.`tags` ON `db_a993c8_freelan`.`job_tags`.tag_id = `db_a993c8_freelan`.`tags`.tag_id
        WHERE `db_a993c8_freelan`.`job_tags`.job_id = table1.job_id
        GROUP BY `db_a993c8_freelan`.`job_tags`.job_id
    ) AS job_tags,
    table1.freelancer_id,
    table1.freelancer_first_name,
    table1.freelancer_last_name,
    table1.freelancer_rate,
    table1.freelancer_profile_picture_path
FROM (
    SELECT
        `db_a993c8_freelan`.`jobs`.job_id, 
        `db_a993c8_freelan`.`users`.user_id AS publisher_id, 
        `db_a993c8_freelan`.`users`.user_first_name AS publisher_first_name, 
        `db_a993c8_freelan`.`users`.user_last_name AS publisher_last_name,
        `db_a993c8_freelan`.`users`.user_profile_picture_path AS publisher_profile_picture_path, 
        `db_a993c8_freelan`.`jobs`.job_title, 
        `db_a993c8_freelan`.`jobs`.job_description, 
        `db_a993c8_freelan`.`jobs`.job_creation_date,
        `db_a993c8_freelan`.`jobs`.job_deadline_date, 
        `db_a993c8_freelan`.`jobs`.job_price,
        `db_a993c8_freelan`.`jobs`.freelancer_id,
        `db_a993c8_freelan`.`jobs`.job_completion_date,
        `db_a993c8_freelan`.`jobs`.job_completion_rating,
        `db_a993c8_freelan`.`jobs`.job_completion_message,
        `freelancers`.freelancer_rate,
        helper_table_name.user_first_name AS freelancer_first_name,
        helper_table_name.user_last_name AS freelancer_last_name,
        helper_table_name.user_profile_picture_path AS freelancer_profile_picture_path,
        CASE 
        WHEN `db_a993c8_freelan`.`user_favorite_jobs`.user_id IS NULL 
        THEN FALSE
        ELSE TRUE 
		END AS is_favorite
    FROM `db_a993c8_freelan`.`jobs`
    LEFT JOIN `db_a993c8_freelan`.`users` ON `db_a993c8_freelan`.`jobs`.user_id = `db_a993c8_freelan`.`users`.user_id
    LEFT JOIN `db_a993c8_freelan`.`users` AS helper_table_name ON `db_a993c8_freelan`.`jobs`.freelancer_id = `db_a993c8_freelan`.helper_table_name.user_id
    LEFT JOIN `db_a993c8_freelan`.`freelancers` ON `db_a993c8_freelan`.`jobs`.freelancer_id = `db_a993c8_freelan`.`freelancers`.freelancer_id
    LEFT JOIN `db_a993c8_freelan`.`user_favorite_jobs` ON (`db_a993c8_freelan`.`user_favorite_jobs`.user_id = {$userId} AND
                                                               `db_a993c8_freelan`.`jobs`.job_id = `db_a993c8_freelan`.`user_favorite_jobs`.job_id)
    WHERE `db_a993c8_freelan`.`jobs`.job_creation_date > '$previousDate'
    AND `db_a993c8_freelan`.`jobs`.job_price >= {$priceFirst}
    AND `db_a993c8_freelan`.`jobs`.job_price <= {$priceSecond}
    ORDER BY `db_a993c8_freelan`.`jobs`.job_creation_date DESC
    LIMIT {$requestedJobsNumber}
) AS table1;";


$queryResult = $helper->executeQuery($databaseQuery);
$rows = array();
while ($row = mysqli_fetch_assoc($queryResult)) {

    // Puplisher Profile Picture handling
    $row['publisher_profile_picture'] = null;
    if (($row["publisher_profile_picture_path"] != null) && (file_exists($row['publisher_profile_picture_path']))) {
        try {
            // Encode the publisher's profile picture as a base64 string and store it in the $row associative array.
            $row['publisher_profile_picture'] = base64_encode(file_get_contents($row['publisher_profile_picture_path']));
        }
        catch (Exception $exception) {
            $message = "Ok, but there was a problem loading the publisher profile picture.\n";
            // $message.= $exception->getMessage();
            $row['publisher_profile_picture'] = null;
        }
    }
    unset($row["publisher_profile_picture_path"]);


    // Freelancer Profile Picture handling
    $row['freelancer_profile_picture'] = null;
    if (($row["freelancer_profile_picture_path"] != null) && (file_exists($row['freelancer_profile_picture_path']))) {
        try {
            // Encode the freelancer's profile picture as a base64 string and store it in the $row associative array.
            $row['freelancer_profile_picture'] = base64_encode(file_get_contents($row['freelancer_profile_picture_path']));
        }
        catch (Exception $exception) {
            $message = "Ok, but there was a problem loading the freelancer profile picture.\n";
            // $message.= $exception->getMessage();
            $row['freelancer_profile_picture'] = null;
        }
    }
    unset($row["freelancer_profile_picture_path"]);


    if ($row["freelancer_id"] == null) {
        unset($row['freelancer_first_name']);
        unset($row['freelancer_last_name']);
        unset($row['freelancer_rate']);
        unset($row['freelancer_profile_picture']);
        unset($row['job_completion_date']);
        unset($row['job_completion_rating']);
        unset($row['job_completion_message']);
    }


    // This code handles the tags associated with the current job and converts them
    // from a comma-separated string into an array of strings.
    if ($row["job_tags"] != null) {
        $row["job_tags"] = explode(",", $row["job_tags"]);
    }

    if ($row["is_favorite"] == "0") {
        $row["is_favorite"] = false;
    }
    else {
        $row["is_favorite"] = true;
    }

    $factor = 0;
    foreach ($tagsArray as $providedTag) {
        foreach ($row["job_tags"] as $currentTag) {
            if ($providedTag == $currentTag) {
                $factor++;
                break;
            }
        }
    }

    if ($factor == count($tagsArray)) {
        $rows[] = $row;
    }
}

$helper->setHeadersForTheResponse();
$helper->sendRequestBody(true, "Ok", $rows);

?>