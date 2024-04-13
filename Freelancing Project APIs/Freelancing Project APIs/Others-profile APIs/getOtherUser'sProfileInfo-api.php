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

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_id"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order", null);
    exit();
}



$userId = $requestBody["user_id"];


$helper->connectToMySQLDatabase();

$queryResult = $helper->executeQuery("SELECT * FROM `db_a993c8_freelan`.users
                                      WHERE (`db_a993c8_freelan`.users.user_id = {$userId})");

$data = mysqli_fetch_assoc($queryResult);

$message = "Ok";

// Now loading the profile picture of the user
if (($data["user_profile_picture_path"] != null) && (file_exists($data["user_profile_picture_path"]))) {
    try {
        $userProfilePictureInBase64Encoding = base64_encode(file_get_contents($data["user_profile_picture_path"]));
        if ($userProfilePictureInBase64Encoding == null) {
            $message = "Ok, but there was a problem loading the user's profile picture.";
            $data["user_profile_picture"] = null;
        }
        else {
            $data["user_profile_picture"] = $userProfilePictureInBase64Encoding;
        }
    }
    catch (Exception $exception) {
        $message = "Ok, but there was a problem loading the user's profile picture.";
        $data["user_profile_picture"] = null;
    }
}
else {
    $message = "Ok, but there was a problem loading the user's profile picture.";
    $data["user_profile_picture"] = null;
}
unset($data["user_profile_picture_path"]);










// Now checking if the user is a freelancer and not just a normal user, and if so, then fetch more data about him
$data["is_freelancer"] = false;

$queryResult = $helper->executeQuery("SELECT * FROM `db_a993c8_freelan`.freelancers
                                      WHERE `db_a993c8_freelan`.freelancers.freelancer_id = {$data['user_id']}");
                                      
$numRows = mysqli_num_rows($queryResult);

if ($numRows == 1) {

    $data["is_freelancer"] = true;
    $data += mysqli_fetch_assoc($queryResult);
    // The following unset function is because the user_id is the same as the freelancer_id always so
    // it makes no sense to send it in the response.
    unset($data["freelancer_id"]);

    // Now fetching the freelancer's tags
    $databaseQuery= "SELECT 
                        `db_a993c8_freelan`.`tags`.tag_name
                     FROM
                         `db_a993c8_freelan`.`tags`
                     WHERE
                         `db_a993c8_freelan`.`tags`.tag_id IN (
                             SELECT 
                                 `db_a993c8_freelan`.`freelancer_tags`.tag_id
                             FROM
                                 `db_a993c8_freelan`.`freelancer_tags`
                             WHERE
                                 `db_a993c8_freelan`.`freelancer_tags`.freelancer_id = {$data['user_id']})";
    
    $queryResult = $helper->executeQuery($databaseQuery);
    $freelancerTags = null;
    while ($tag = mysqli_fetch_assoc($queryResult)) {
        $freelancerTags[] = $tag["tag_name"];
    }

    $data["freelancer_tags"] = $freelancerTags;

    // Now loading the PDFs of the freelancer one by one
    $databaseQuery = "SELECT
                        `db_a993c8_freelan`.`freelancer_pdfs`.pdf_path
                      FROM
                        `db_a993c8_freelan`.freelancer_pdfs
                      WHERE
                        `db_a993c8_freelan`.`freelancer_pdfs`.freelancer_id = ".$data['user_id'];

    $queryResult = $helper->executeQuery($databaseQuery);

    $freelancerPDFs = null;
    $freelancerPDFsCounter = 1;
    // If at least one PDF failed to load, then I don't want to send any PDFs, sending all or none.
    while ($freelancerPDF = mysqli_fetch_assoc($queryResult)) {
        if (($freelancerPDF["pdf_path"] != null) && file_exists($freelancerPDF["pdf_path"])) {
            try {
                $currentPDFInBase54Encoding = base64_encode(file_get_contents($freelancerPDF["pdf_path"]));
                if ($currentPDFInBase54Encoding == null) {
                    $message .=  "\nOk, but there was a problem loading the freelancer's PDFs.\n";
                    $freelancerPDFs = null;
                    break;
                }
                $currentPDFName = Utilities::getPDFNameFromItsPath($freelancerPDF["pdf_path"]);
                if ($currentPDFName == "Default PDF Name") {
                    $currentPDFName .= " {$freelancerPDFsCounter}";
                    $freelancerPDFsCounter++;
                }
                $freelancerPDFs[$currentPDFName] = $currentPDFInBase54Encoding;
                
            }
            catch (Exception $exception) {
                $message .= "\nOk, but there was a problem loading the freelancer's PDFs.\n";
                $freelancerPDFs = null;
                break;
            }
        }
        else {
            $message .= "\nOk, but there was a problem loading the freelancer's PDFs.\n";
            $freelancerPDFs = null;
            break;
        }
    }

    $data["freelancer_pdfs"] = $freelancerPDFs;
}

$numberOfFollowers = mysqli_fetch_assoc($helper->executeQuery(
    "SELECT COUNT(*)
     FROM `db_a993c8_freelan`.user_favorite_users 
     WHERE `db_a993c8_freelan`.`user_favorite_users`.favorite_user_id = {$data["user_id"]}"))["COUNT(*)"];

$data["number_of_followers"] = $numberOfFollowers;

$date["user_password"] = "Definitely a false password, but for data consistency between the frontend and the ".
                         "backend sides.";

$helper->sendRequestBody(true, $message, $data);

?>