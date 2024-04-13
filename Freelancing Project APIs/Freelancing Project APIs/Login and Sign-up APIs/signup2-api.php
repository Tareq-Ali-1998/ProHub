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

if (!Utilities::checkJsonKeysMatch($requestBody, ["user_email", "user_date_of_birth", "user_gender",
                                                  "user_city", "user_specific_address", "user_phone_number"])) {
    http_response_code(400);
    $helper->sendRequestBody(false, "The request body is missing one or more required JSON keys, or ".
                                    "the keys are not in the expected order.", null);
    exit();
}

// *********************************************
// TODO: It's a good idea here to make a function that validates the variables types.
// *********************************************
$userEmail = $requestBody["user_email"];
$userDateOfBirth = $requestBody["user_date_of_birth"];
$userGender = $requestBody["user_gender"];
$userCity = $requestBody["user_city"];
$userSpecificAddress = $requestBody["user_specific_address"];
$userPhoneNumber = $requestBody["user_phone_number"];










$helper->connectToMySQLDatabase();

// The following code checks if a user with the same email address already exists in the `users` table.
$weHaveAUserWithThisSameEmailAddress = mysqli_fetch_assoc($helper->executeQuery("SELECT COUNT(*)
 FROM `db_a993c8_freelan`.`users`
 WHERE `db_a993c8_freelan`.`users`.user_email = '".$userEmail."'"))['COUNT(*)'];

if ($weHaveAUserWithThisSameEmailAddress) {
    // If a user with the same email address exists in the table, return the appropriate error message.
    $helper->sendRequestBody(false, "The email address you are trying to register with is for another ".
                                    "user in our platform, and although you had verifyed this email before ".
                                    "successfully, but this could have been happend because it's been more than ".
                                    "half an hour since you did that, and some other user or you had fully registered ".
                                    "with this email successfully.", null);
    exit();
}










$queryResult = $helper->executeQuery("SELECT *
 FROM `db_a993c8_freelan`.`incomplete_registration_users`
 WHERE `db_a993c8_freelan`.`incomplete_registration_users`.user_email = '".$userEmail."'");

// The following case means that the user had verifyed his email address before but
// he should continue the registration process after verifying his email in a specific
// period of time, and we can change that time as we want, so he should verify his email again.
$numberOfRecords = mysqli_num_rows($queryResult);
if ($numberOfRecords == 0) {
    $helper->sendRequestBody(false, "After verifying your email address, you should continue your registration ".
                                    "in at most half an hour, so now you should verify your email again ".
                                    "with a new verification code by clicking the following button to receive ".
                                    "the new code to your email address.", null);
    exit();
}










$queryData = mysqli_fetch_assoc($queryResult);

$userRegistrationStatus = $queryData['user_registration_status'];

// According to the full logic of our system registration process, the following case is impossible to
// happen for sure, but I still want to validate every single case from the backend perespective in order
// to make sure that everything is fully valid, and we need that specially in the testing process.
if ($userRegistrationStatus != 1) {
    $helper->sendRequestBody(false, "This email address has already received the required verification code. In the ".
                                    "event that any unforeseen issues arise, you may attempt to register again with ".
                                    "the same email address in at most half an hour or verify your email address with ".
                                    "your current email verification code by clicking here.", null);
    exit();
}

$userFirstName = $queryData['user_first_name'];
$userLastName = $queryData['user_last_name'];
$userPassword = $queryData['user_password'];
$userRegistrationDate = $queryData['user_registration_date'];










// The following code checks if a user with the same phone number already exists in the `users` table.
$weHaveAUserWithThisSamePhoneNumber = mysqli_fetch_assoc($helper->executeQuery("SELECT COUNT(*)
 FROM `db_a993c8_freelan`.`users`
 WHERE `db_a993c8_freelan`.`users`.user_phone_number = '".$userPhoneNumber."'"))['COUNT(*)'];

if ($weHaveAUserWithThisSamePhoneNumber) {
    // If a user with the same phone number exists in the table, return the appropriate error message.
    $helper->sendRequestBody(false, "The phone number you are trying to register with is for another ".
                                    "user in our platform, and if the phone number you have provided above is yours, ".
                                    "you can try to login with your original account.", null);
    exit();
}









// If everything is valid in this registration process, then I should withdraw the user record from
// the `incomplete_registration_users` table and insert all the combined data to the `users` table which
// means that the user definitely registered successfully in our system and have a valid account since now.
$helper->executeQuery("DELETE FROM `db_a993c8_freelan`.`incomplete_registration_users` 
                       WHERE `db_a993c8_freelan`.`incomplete_registration_users`.user_email =
                      '$userEmail'");

$helper->executeQuery("INSERT INTO `db_a993c8_freelan`.`users` (user_first_name, user_last_name, user_email,
                                          user_email_visibility, user_phone_number, user_phone_number_visibility,
                                          user_password, user_date_of_birth, user_gender, user_account_creation_date,
                                          user_city, user_specific_address)
                       VALUES
                       ('$userFirstName', '$userLastName', '$userEmail', 1, '$userPhoneNumber', 1,
                        '$userPassword', '$userDateOfBirth', '$userGender', '$userRegistrationDate',
                        '$userCity', '$userSpecificAddress');");










// Now select all the information of the user and return them back.
$data = mysqli_fetch_assoc($helper->executeQuery("SELECT * FROM `db_a993c8_freelan`.users
                                                  WHERE `db_a993c8_freelan`.`users`.user_email = '$userEmail'"));

unset($data['user_profile_picture_path']);
unset($data['user_password']);
$data['user_profile_picture'] = null;
$data['is_freelancer'] = false;
$data["number_of_followers"] = 0;

$helper->sendRequestBody(true, "Ok", $data);

?>