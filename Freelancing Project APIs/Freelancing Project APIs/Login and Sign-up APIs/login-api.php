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

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_email", "user_password"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order", null);
    exit();
}

// *********************************************
// TODO: It's a good idea here to make a function that validates the variables types.
// *********************************************
$userEmail = $requestBody["user_email"];
$userPassword = $requestBody["user_password"];










$helper->connectToMySQLDatabase();

// Now checking if the email address is semi registered in our system or not
$queryData = mysqli_fetch_assoc($helper->executeQuery(
                        "SELECT `db_a993c8_freelan`.`incomplete_registration_users`.user_registration_status
                         FROM `db_a993c8_freelan`.`incomplete_registration_users`
                         WHERE `db_a993c8_freelan`.`incomplete_registration_users`.user_email = '$userEmail'"));

$userRegistrationStatus = null;
if (isset($queryData["user_registration_status"])) {
    $userRegistrationStatus = $queryData["user_registration_status"];
}
if ($userRegistrationStatus != null) {

    if ($userRegistrationStatus == 1) {
        $data = array("user_email" => $userEmail);
        $data += mysqli_fetch_assoc($helper->executeQuery(
            "SELECT  `db_a993c8_freelan`.`incomplete_registration_users`.user_first_name,
                     `db_a993c8_freelan`.`incomplete_registration_users`.user_last_name,
                     `db_a993c8_freelan`.`incomplete_registration_users`.user_password
             FROM `db_a993c8_freelan`.`incomplete_registration_users`
             WHERE `db_a993c8_freelan`.`incomplete_registration_users`.user_email = '$userEmail'"));
        $helper->sendRequestBody(false, "Mahmoud here you should send the user directly to the signup2 widget ".
                                        "to continue his rigestration.", $data);
        exit();
    }
    $helper->sendRequestBody(true, "The user of the email address you entered has received an email verification code, ".
                                    "please check your email and verify it by clicking on the following button.", null);
    exit();
}










// Now checking if the email address is fully registered in our system or not

$thisEmailExistsInThePlatform = mysqli_fetch_assoc($helper->executeQuery(
                                        "SELECT COUNT(*)
                                         FROM `db_a993c8_freelan`.users
                                         WHERE `db_a993c8_freelan`.`users`.user_email = '$userEmail'"))["COUNT(*)"];

if ($thisEmailExistsInThePlatform == 0) {
    $helper->sendRequestBody(true, "The email address you entered is not registered in our system. ".
                                    "Please check your email address or create a new account.", null);
    exit();
}










// Now checking if the password is correct or not

$queryResult = $helper->executeQuery("SELECT * FROM `db_a993c8_freelan`.users
                                      WHERE (`db_a993c8_freelan`.users.user_email = '$userEmail')");

$data = mysqli_fetch_assoc($queryResult);
$userCurrentHashedPasswordFromTheDatabase = $data["user_password"];

if (!password_verify($userPassword, $userCurrentHashedPasswordFromTheDatabase)) {
    // Maybe we will consider resetting the user's password by sending a verification code to his
    // email in the future by clicking the button forgot my password.
    $helper->sendRequestBody(true, "Incorrect password, please try again.", null);
    exit();
}










// Now if the code execution will reach this point, then definitely the user is fully registered in our system and
// I should return his data.
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

$helper->sendRequestBody(true, $message, $data);

?>