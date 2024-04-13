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

if (!Utilities::checkJsonKeysMatch($requestBody, ["freelancer_id", "jobs_number", "job_index_for_scrolling"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order.", null);
    exit();
}

$freelancerId = $requestBody["freelancer_id"];
$jobsNumber = $requestBody["jobs_number"];
$jobIndex = $requestBody["job_index_for_scrolling"] - 1;



// Select the tags for the freelancer first as IDs and then for each one of them work.

$helper->connectToMySQLDatabase();

$queryResult = $helper->executeQuery(
"SELECT tag_id
FROM freelancer_tags
WHERE freelancer_id = {$freelancerId}");


$jobIdFrequency = array();
while ($row = mysqli_fetch_assoc($queryResult)) {

    $currentTagId = $row['tag_id'];

    $anotherQueryResult = $helper->executeQuery(
        "SELECT job_id
         FROM jobs_tags
         WHERE tag_id = {$currentTagId}");

    while ($row2 = mysqli_fetch_assoc($anotherQueryResult)) {
        $currentJobId = $row2['job_id'];
        if (isset($jobIdFrequency[$currentJobId])) {
            $jobIdFrequency[$currentJobId]++;
            continue;
        }
        $jobIdFrequency[$currentJobId] = 1;
    }
}



// Create an array of pairs (job frequency and job ID)
$sortedJobPairs = array();
foreach ($jobIdFrequency as $jobId => $frequency) {
    $sortedJobPairs[] = array($frequency, $jobId);
}

sort($sortedJobPairs);
$sortedJobPairs = array_reverse($sortedJobPairs);

$finalJobIds = array();

for ($i = 0; $i < count($sortedJobPairs); $i++) {
    $finalJobIds[]= $sortedJobPairs[$i][1];
}


$rows = array();

for ($i = $jobIndex; $i < min(count($finalJobIds), $jobIndex + $jobsNumber); $i++) {

    $currentJobId = $finalJobIds[$i];

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
        table1.is_favorite,
        (	
            SELECT GROUP_CONCAT(DISTINCT `db_a993c8_freelan`.`tags`.tag_name SEPARATOR ', ')
            FROM `db_a993c8_freelan`.`job_tags`
            LEFT JOIN `db_a993c8_freelan`.`tags` ON `db_a993c8_freelan`.`job_tags`.tag_id = `db_a993c8_freelan`.`tags`.tag_id
            WHERE `db_a993c8_freelan`.`job_tags`.job_id = table1.job_id
            GROUP BY `db_a993c8_freelan`.`job_tags`.job_id
        ) AS job_tags
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
            CASE 
            WHEN `db_a993c8_freelan`.`user_favorite_jobs`.user_id IS NULL 
            THEN FALSE
            ELSE TRUE 
            END AS is_favorite
        FROM `db_a993c8_freelan`.`jobs`
        LEFT JOIN `db_a993c8_freelan`.`users` ON `db_a993c8_freelan`.`jobs`.user_id = `db_a993c8_freelan`.`users`.user_id
        LEFT JOIN `db_a993c8_freelan`.`users` AS helper_table_name ON `db_a993c8_freelan`.`jobs`.freelancer_id = `db_a993c8_freelan`.helper_table_name.user_id
        LEFT JOIN `db_a993c8_freelan`.`user_favorite_jobs` ON (`db_a993c8_freelan`.`user_favorite_jobs`.user_id = {$freelancerId} AND
                                                                   `db_a993c8_freelan`.`jobs`.job_id = `db_a993c8_freelan`.`user_favorite_jobs`.job_id)
        WHERE `db_a993c8_freelan`.`jobs`.job_id = {$currentJobId}
    ) AS table1;";


    $row = mysqli_fetch_assoc($helper->executeQuery($databaseQuery));

    

    
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


    // This code handles the tags associated with the current job and converts them
    // from a comma-separated string into an array of strings.
    if ($row["job_tags"] != null) {
        $row["job_tags"] = explode(",", $row["job_tags"]);
    }

    $row["freelancer_id"] = null;

    if ($row["is_favorite"] == "0") {
        $row["is_favorite"] = false;
    }
    else {
        $row["is_favorite"] = true;
    }


    $rows[]= $row;
    

}

$helper->setHeadersForTheResponse();
$helper->sendRequestBody(true, "Ok", $rows);

?>