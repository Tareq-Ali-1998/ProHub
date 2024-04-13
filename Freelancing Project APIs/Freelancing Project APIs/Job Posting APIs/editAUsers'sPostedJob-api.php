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

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_id", "job_id","job_title", "job_description",
                                                  "job_deadline_date", "job_price", "job_tags", "job_creation_date"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order", null);
    exit();
}

// *********************************************
// TODO: It's a good idea here to make a function that validates the variables types.
// *********************************************
$userId = $requestBody["user_id"];
$jobId = $requestBody["job_id"];
$jobTitle = $requestBody["job_title"];
$jobDescription = $requestBody["job_description"];
$jobDeadlineDate = $requestBody["job_deadline_date"];
$jobPrice = $requestBody["job_price"];
$jobTags = $requestBody["job_tags"];
$jobCreationDate = $requestBody["job_creation_date"];



$jobEditionDateHelper = new DateTime(Utilities::getCurrentDateTime());
$jobDeadlineDateHelper = new DateTime($jobDeadlineDate);

$interval = $jobDeadlineDateHelper->diff($jobEditionDateHelper);
$jobTimeIntervalInMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
$jobTimeIntervalInSeconds = $interval->days * 24 * 60 * 60 + $interval->h * 60 * 60 + $interval->i * 60 + $interval->s;


if ($jobTimeIntervalInSeconds < 5) {
    $helper->sendRequestBody(true, "The time interval is not correct because the deadline date ".
                                   "has passed.", null);
    exit();
}
else if ($jobTimeIntervalInMinutes < 5) {
    $helper->sendRequestBody(true, "The time interval is not correct because the job should stay available on the platform ".
                                   "at least for 5 minutes after the job edition, so the freelancer's opportunities to help you ".
                                   "will increase.", null);
    exit();
}





$helper->connectToMySQLDatabase();
/*
 * This condition will be true also if the array is empty, because PHP considers an
 * empty array falsy. When comparing with null using the `==` operator, null is cast
 * to false, so an empty array and null are considered equal.
 * 
 * To check if the $jobTags variable is an empty array, use the `empty()` function instead
 * of comparing with null.
 */
if ($jobTags == null) {
    // Mahmoud here you should tell the user that "there should be at least one tag for each posted job" bro.
    $helper->sendRequestBody(false, "Any job in our platform should have at least one tag, so please try to categorize ".
                                    "your job by adding its tags, and you may read our FAQ to understand more.", null);
    exit();
}



 


$helper->executeQuery("UPDATE `db_a993c8_freelan`.`jobs`
                       SET job_title = '{$jobTitle}',
                           job_description = '{$jobDescription}',
                           job_creation_date = '{$jobCreationDate}',
                           job_deadline_date = '{$jobDeadlineDate}',
                           job_price = {$jobPrice}
                       WHERE job_id = '{$jobId}'");








// Build the values string for the tags insertion query, in order to insert all the tags at once
// to optimize the performance.
$tagsValues = "";
for ($i = 0; $i < count($jobTags); $i++) {
    if ($i == count($jobTags) - 1) {
        $tagsValues .= "('{$jobTags[$i]}');";
        break;
    }
    $tagsValues .= "('{$jobTags[$i]}'),";
}

// Insert the tags into the tags table
$helper->executeQuery("INSERT IGNORE INTO `db_a993c8_freelan`.`tags` (tag_name) VALUES " .$tagsValues);

// Get the IDs of the inserted tags
$tagsIds = [];
$queryResult = $helper->executeQuery("SELECT `db_a993c8_freelan`.`tags`.tag_id
                                      FROM `db_a993c8_freelan`.`tags`
                                      WHERE `db_a993c8_freelan`.`tags`.tag_name
                                      IN ('".implode("', '", $jobTags)."')");
while ($row = mysqli_fetch_assoc($queryResult)) {
    $tagsIds[] = $row["tag_id"];
}

// Build the values string for the job_tags insert query
$jobTagsValues = "";
for ($i = 0; $i < count($tagsIds); $i++) {
    if ($i == count($tagsIds) - 1) {
        $jobTagsValues .= "({$jobId}, {$tagsIds[$i]});";
        break;
    }
    $jobTagsValues .= "({$jobId}, {$tagsIds[$i]}),";
}

// Insert the job and tag IDs into the job_tags table
$helper->executeQuery("INSERT IGNORE INTO `db_a993c8_freelan`.`job_tags` (job_id, tag_id) VALUES " .$jobTagsValues);

$helper->sendRequestBody(true, "Ok", null);

?>