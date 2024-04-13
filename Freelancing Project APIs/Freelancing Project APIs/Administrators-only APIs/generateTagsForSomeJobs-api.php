<?php

include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php';
include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/ChatGPT.php';

$helper = new APIHandler();


$helper->authorizeRequestUsernameAndPassword();
$helper->setHeadersForTheResponse();
$requestBody = $helper->getRequestBody();
if ($requestBody === null) {
    $helper->sendRequestBody(false, "This API expects JSON data, but it found nothing.", null);
    exit();
}

if (!Utilities::checkJsonKeysMatch($requestBody, ["number_of_jobs_to_categorize"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order", null);
    exit();
}

$numberOfJobsToCategorize = $requestBody['number_of_jobs_to_categorize'];

$helper->connectToMySQLDatabase();

// The query to fetch all jobs from the database
// The query to fetch all jobs from the database
$databaseQuery = "SELECT job_id, job_title, job_description FROM `db_a993c8_freelan`.jobs";




// Execute the query and fetch the results
$queryResult = $helper->executeQuery($databaseQuery);





$totalNumberOfTags = 0;
$totalNumberOfNewTags = 0;
$totalNumberOfExistingTags = 0;


// Function to check if a tag exists in the database and return its ID if it does
function getTagId($tagName) {
    global $helper, $totalNumberOfNewTags;
    $tagName = addslashes($tagName);
    $query = "SELECT tag_id FROM `db_a993c8_freelan`.tags WHERE tag_name = '$tagName'";
    $result = $helper->executeQuery($query);
    $row = mysqli_fetch_assoc($result);
    if (isset($row['tag_id'])) {
        return $row['tag_id'];
    }
    else {
        $totalNumberOfNewTags++;
        return false;
    }
}

// Function to insert a new tag into the database and return its ID
function insertTag($tagName) {
    global $helper;
    $tagName = addslashes($tagName);

    $query = "INSERT INTO `db_a993c8_freelan`.tags (tag_name) VALUES ('$tagName')";
    $helper->executeQuery($query);
    
}


// Function to insert the job-tag relationships into the database
function insertJobTags($jobId, $tagIds) {
    global $helper;
    
    foreach ($tagIds as $tagId) {
        $query = "INSERT INTO `db_a993c8_freelan`.job_tags (job_id, tag_id) VALUES ($jobId, $tagId)";
        $helper->executeQuery($query);
    }
}

$numberOfSuccessfullyCategorizedJobs = 0;
$numberOfUnsuccessfullyCategorizedJobs = 0;
$tagsLines = array();
$counter = 0;
// Loop through the rows and process each job
while ($row = mysqli_fetch_assoc($queryResult)) {
    
    if ($counter == $numberOfJobsToCategorize) {
        break;
    }
    
    $jobId = $row['job_id'];

    // If the job has already one or more tags I don't want to generate more tags for it
    $numberOfCurrentTagsOfTheCurrentJob = mysqli_fetch_assoc($helper->executeQuery("SELECT COUNT(*) FROM ".
                                                             "`db_a993c8_freelan`.job_tags WHERE ".
                                                             "`db_a993c8_freelan`.job_tags.job_id = {$jobId}"))['COUNT(*)'];

    if ($numberOfCurrentTagsOfTheCurrentJob >= 1) {
        continue;
    }

    $counter++;

    $jobTitle = $row['job_title'];
    $jobDescription = $row['job_description'];
    
    // Call your magical function to get the tags for the job
    $chatGPT = new ChatGPT('gpt-3.5-turbo');

    // Here $tags is an indexed array of string, it may contain zero to 10 tags at most
    $tags = $chatGPT->getJobTags("\nJob Title:\n".$jobTitle."\n\n"."Job Description:\n".
                                 $jobDescription);

    // print_r($tags);

    


    if ((is_string($tags)) || ($tags == null) || ($tags == [])) {
        $numberOfUnsuccessfullyCategorizedJobs++;
        continue;
    }

    $tagsLines[] = $tags;
    $numberOfSuccessfullyCategorizedJobs++;
    
    // Loop through the tags and insert them into the database if necessary
    $tagIds = [];
    foreach ($tags as $tag) {
        $totalNumberOfTags++;
        $tagId = getTagId($tag);
        if ($tagId == false) {
            insertTag($tag);
        }
        $tagId = getTagId($tag);
        $tagIds[] = $tagId;
    }
    
    // Insert the job-tag relationships into the database
    insertJobTags($jobId, $tagIds);
}

$totalNumberOfExistingTags = $totalNumberOfTags - $totalNumberOfNewTags;
$data = array(
    'number_of_successfully_categorized_jobs' => $numberOfSuccessfullyCategorizedJobs,
    'number_of_unsuccessfully_categorized_jobs' => $numberOfUnsuccessfullyCategorizedJobs,
    'total_number_of_generated_tags' => $totalNumberOfTags,
    'total_number_of_new_tags' => $totalNumberOfNewTags,
    'total_number_of_existing_tags' => $totalNumberOfExistingTags,
    'tags_lines' => $tagsLines
);

$helper->sendRequestBody(true, "Ok", $data);

?>