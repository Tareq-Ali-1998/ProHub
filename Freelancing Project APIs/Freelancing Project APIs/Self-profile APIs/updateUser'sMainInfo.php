<?php

include 'C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php';










$helper = new APIHandler();

$helper->authorizeRequestUsernameAndPassword();










$requestBody = $helper->getRequestBody();
// Check if the request body exists and contains the required keys
if ($requestBody === null) {
    http_response_code(400);
    $helper->sendRequestBody(false, "This API expects JSON data, but it found nothing.", null);
    exit();
}

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_id", "user_first_name", "user_last_name",
                                                  "user_phone_number" ,"user_gender", "user_city",
                                                  "user_date_of_birth", "user_specific_address"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, ".
                                    "or the keys are not in the expected order.", null);
    exit();
}

// Get the user's data from the request body
$userId = $requestBody["user_id"];
$userFirstName = $requestBody["user_first_name"];
$userLastName = $requestBody["user_last_name"];
$userSpecificAddress = $requestBody["user_specific_address"];
$userGender = $requestBody["user_gender"];
$userCity = $requestBody["user_city"];
$userDateOfBirth = $requestBody["user_date_of_birth"];
$userPhoneNumber = $requestBody["user_phone_number"];










$helper->connectToMySQLDatabase();
// Check if the user ID is registered in our platform
$queryData = mysqli_fetch_assoc($helper->executeQuery("SELECT `db_a993c8_freelan`.`users`.user_id
                                                       FROM `db_a993c8_freelan`.`users`
                                                       WHERE `db_a993c8_freelan`.`users`.user_id = '$userId'"));
if (!isset($queryData["user_id"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "We don't have any user with this user_id in the platform.", null);
    exit();
}




$phoneNumberFrequency = mysqli_fetch_assoc($helper->executeQuery("SELECT COUNT(*)
                                                       FROM `db_a993c8_freelan`.`users`
                                                       WHERE `db_a993c8_freelan`.`users`.user_phone_number = '$userPhoneNumber'
                                                       AND `db_a993c8_freelan`.`users`.user_id != {$userId}"))['COUNT(*)'];


if ($phoneNumberFrequency >= 1) {
    $helper->sendRequestBody(false, "The phone number you are trying to use is currently used by another account ".
                                    "on the platform, and you can't make two accounts with the same phone number.", null);
    exit();
}






// Update the user data in the database
$queryData = $helper->executeQuery("UPDATE `db_a993c8_freelan`.`users`
                                    SET `db_a993c8_freelan`.`users`.user_first_name = '$userFirstName',
                                        `db_a993c8_freelan`.`users`.user_last_name = '$userLastName',
                                        `db_a993c8_freelan`.`users`.user_specific_address = '$userSpecificAddress',
                                        `db_a993c8_freelan`.`users`.user_gender = '$userGender',
                                        `db_a993c8_freelan`.`users`.user_city = '$userCity',
                                        `db_a993c8_freelan`.`users`.user_date_of_birth = '$userDateOfBirth',
                                        `db_a993c8_freelan`.`users`.user_phone_number = '$userPhoneNumber'
                                    WHERE `db_a993c8_freelan`.`users`.user_id = '$userId'");

// Send the response
$helper->sendRequestBody(true, "Ok", null);


?>