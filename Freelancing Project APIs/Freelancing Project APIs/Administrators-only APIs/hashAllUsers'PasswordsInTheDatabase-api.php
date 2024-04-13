<?php



// This is a GET API

include "C:/Users/Tareq/Desktop/Freelancing Project APIs/Freelancing Project Classes/APIHandler.php";


$helper = new APIHandler();

$helper->connectToMySQLDatabase();

for ($i = 1; $i <= 100; $i++) {

    $currentUserPassword = mysqli_fetch_assoc($helper->executeQuery(
        "SELECT user_password FROM users WHERE user_id = {$i}"
    ))["user_password"];
    
    $currentUserPassword = password_hash($currentUserPassword, PASSWORD_BCRYPT);

    $helper->executeQuery(
        "UPDATE users
         SET user_password = '$currentUserPassword'
         WHERE user_id = {$i}"
    );

}


echo "Successfully Done:\n    All the passwords in the database has been hashed with the BCRYPT hashing algorithm";

?>