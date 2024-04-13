<?php

include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php';
include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/AprioriAlgorithm.php';
$helper = new APIHandler();


$helper->authorizeRequestUsernameAndPassword();
$helper->setHeadersForTheResponse();
$requestBody = $helper->getRequestBody();
if ($requestBody === null) {
    $helper->sendRequestBody(false, "This API expects JSON data, but it found nothing.", null);
    exit();
}

if (!Utilities::checkJsonKeysMatch($requestBody, ["tags", "level", "minimum_support_count", 
                                   "minimum_support", "minimum_confidence"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order", null);
    exit();
}

// *********************************************
// TODO: It's a good idea here to make a function that validates the variables types.
// *********************************************
$tags = $requestBody['tags'];
$level = $requestBody['level'];
$minimumSupportCount = $requestBody['minimum_support_count'];
$minimumSupport = $requestBody['minimum_support'];
$minimumConfidence = $requestBody['minimum_confidence'];


if (($tags == null) || (empty($tags))) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The tags array you have sent is null or empty", null);
    exit();
}
$helper->connectToMySQLDatabase();

// select tag_id for each tag_name of them from the tags table in the database 
$tagIDs = array();
foreach ($tags as $tag) {
    $databaseQuery = "SELECT tag_id FROM `db_a993c8_freelan`.tags WHERE tag_name = '{$tag}'";
    $queryResult = $helper->executeQuery($databaseQuery);
    $row = mysqli_fetch_assoc($queryResult);
    if ($row !== null) {
        array_push($tagIDs, $row['tag_id']);
    }
}
// convert the array tags to a new array of integers in the end
$givenItemSet = $tagIDs;
sort($givenItemSet);


// Now extracting the needed data set or as we call it the transactions
$databaseQuery = "SELECT job_id, tag_id FROM `db_a993c8_freelan`.job_tags ORDER BY job_id ASC;";

$queryResult = $helper->executeQuery($databaseQuery);

$jobTags = array();

$currentItems = array();

$visitedTagID = array();

// loop over result set
while ($row = mysqli_fetch_assoc($queryResult)) {
    $jobID = intval($row['job_id']);
    $tagID = intval($row['tag_id']);
    
    
    // add tag to job's tag list
    if (!isset($jobTags[$jobID])) {
        $jobTags[$jobID] = array();
    }
    array_push($jobTags[$jobID], $tagID);

    if (!isset($visitedTagID[$tagID])) {
        $currentItems[] = $tagID;
        $visitedTagID[$tagID] = true;
    }
}

// The following for loop converts the $current Items 1D array to a 2D array as the following needed functions except
for ($i = 0; $i < count($currentItems); $i++) {
    $currentItems[$i] = array($currentItems[$i]);
}

$aprioriAlgorithmHandler = new AprioriAlgorithm();
$aprioriAlgorithmHandler->setMinimumSupportCount($minimumSupportCount);
$aprioriAlgorithmHandler->setMinimumSupport($minimumSupport);
$aprioriAlgorithmHandler->setMinimumConfidence($minimumConfidence);
$aprioriAlgorithmHandler->setTransactions($jobTags);

// The following function should return the final association rules in a form of
// an associative array where each key is a serialized string of a sorted array and each value is an array of
// arrays (even if it contains one array and that array contains one integer element) where each inner array is sorted.
$associationRules = $aprioriAlgorithmHandler->apriori($currentItems, 1, $level);

$tagsListsToReturn = array();

if ($associationRules == null) {
    $helper->sendRequestBody(true, "Ok", $tagsListsToReturn);
    exit();
}

foreach ($associationRules as $itemset => $linkedItems) {
    $itemset = unserialize($itemset);
    sort($itemset);
    if ($itemset != $givenItemSet) {
        continue;
    }

    foreach ($linkedItems as $linkedItem) {
        // Iterate over the $linkedItem array and store the tag_name for each tag_id
        $tagNames = array();
        foreach ($linkedItem as $tagID) {
            $databaseQuery = "SELECT tag_name FROM `db_a993c8_freelan`.tags WHERE tag_id = '{$tagID}'";
            $queryResult = $helper->executeQuery($databaseQuery);
            $row = mysqli_fetch_assoc($queryResult);
            if ($row !== null) {
                array_push($tagNames, $row['tag_name']);
            }
        }

        $tagsListsToReturn[] = $tagNames;

    }
}

$helper->sendRequestBody(true, "Ok", $tagsListsToReturn);

?>