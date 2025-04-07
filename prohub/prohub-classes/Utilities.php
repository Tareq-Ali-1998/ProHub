
<?php

require_once dirname(__DIR__) . '/config.php';

class Utilities {

    /**
     * Generates a single 9-digit email verification code using cryptographically 
     * secure random.
     *
     * Each digit is between 1-9. Uses random_int() which is suitable for 
     * security-sensitive contexts.
     * 
     * The probability of collision is extremely low (1 in 387,420,489) even with 
     * simultaneous requests.
     *
     * @return string Returns a 9-digit verification code as a string.
     */
    public static function generateEmailVerificationCode(): string {
        $verificationCodeode = '';
        for ($i = 0; $i < 9; $i++) {
            // Cryptographically secure random digit between 1 and 9.
            $verificationCodeode .= random_int(1, 9);
        }
        return $verificationCodeode;
    }


    public static function getCurrentDateTime() {
        // Set the timezone to GMT+3
        $timezone = new DateTimeZone('GMT+3');
      
        // Create a new DateTime object with the current time and the GMT+3 timezone
        $currentDateTime = new DateTime('now', $timezone);
      
        // Format the DateTime as a string
        $DateTimeString = $currentDateTime->format('Y-m-d H:i:s');
      
        // Return the DateTime string in the format required by MySQL
        return $DateTimeString;
    }


    public static function getCurrentDate() {
        // Set the timezone to GMT+3
        $timezone = new DateTimeZone('GMT+3');
      
        // Create a new DateTime object with the current time and the GMT+3 timezone
        $currentDateTime = new DateTime('now', $timezone);
      
        // Format the date as a string
        $dateString = $currentDateTime->format('Y-m-d');
      
        // Return the date string in the format required by MySQL
        return $dateString;
    }


    /*
     * The isValidJson function takes a string as input and checks if it's a valid JSON or not. 
     * The function first checks if the input string is empty or not, if it's empty, then it's considered a valid JSON.
     * This is because an empty string is a valid JSON string according to the JSON specification.
     * Then it continue the process using the built-in PHP function json_decode to try to decode the input string as a JSON object.
     * If decoding succeeds, it means that the input string was a valid JSON, and the function returns true. 
     * If decoding fails, due to an error in the JSON syntax, the function returns false.
     * @param string $jsonString A string that needs to be checked for JSON validity
     * @return bool Returns true if the input string is a valid JSON, false otherwise.
     */
    public static function isValidJson($jsonString) {
        // Check if the input string is empty, then it's considered a valid JSON.
        if (empty($jsonString)) {
            return true;
        }

        // Try to decode the input string as JSON using the json_decode function.
        $decodedJson = json_decode($jsonString);

        // Check if any error occurred during the decoding process, and return the result.
        return (json_last_error() === JSON_ERROR_NONE);
    }


    /**
     * The following function checks if the keys in a decoded JSON object (in other words an associative array) match
     * a list of required keys in the same order.
     * If any of the two passed arrays is null, the function will return false.
     */
    public static function checkJsonKeysMatch($decodedJSON, $requiredKeysList) {
        $identical = true;
        
        // If an array is null, then it will no longer be considered an array, and be careful because here
        // null means that I am passing the values like this "Utilities::checkJsonKeysMatch(null, null)", which
        // means that I am passing null values and not an array, but if I pass them like
        // this "Utilities::checkJsonKeysMatch(null, null)", then they are valid empty arrays and their value is
        // null but they are not null.
        if (!is_array($decodedJSON) || !is_array($requiredKeysList)) {
            return false;
        }
        
        // If the decoded JSON object is empty, the required keys list should also be empty
        if (empty($decodedJSON) && !empty($requiredKeysList)) {
            return false;
        }

        // This may happen (I mean the following if statement could be true), and this is equivalent
        // to if ($empty($decodedJSON)), and it's important because the following function array_keys($decodedJSON) should
        // accept arrays of one or more values because otherwise, it will throw an error.
        if ($decodedJSON == null) {
            return false;
        }
        
        // Get the keys of the JSON object in the same order as they appear in the object
        $jsonKeys = array_keys($decodedJSON);
        
        // Check if the length of the keys list matches the length of the string list
        if (count($jsonKeys) !== count($requiredKeysList)) {
            return false;
        }
        
        // Check if the keys match the strings in the same order
        for ($i = 0; $i < count($jsonKeys); $i++) {
            if ($jsonKeys[$i] !== $requiredKeysList[$i]) {
                return false;
            }
        }

        return true;

    }

    public static function isIndexedArrayOfStrings($arr) {
        if (!is_array($arr)) {
            return false; // Not an array
        }
        if (empty($arr)) {
            return false; // Empty array
        }
        foreach ($arr as $element) {
            if (!is_string($element)) {
                return false; // One or more elements are not strings
            }
        }
        return true; // Indexed array of strings
    }







    /**
     * Generates the valid file path for a user's profile picture based on the profile picture name.
     *
     * @param string $profilePictureName The name of the user's profile picture file.
     * @return string The full path to the profile picture in the format: `PROFILE_PICTURES_PATH/profilePictureName`.
     */
    public static function getUserProfilePicturePath(string $profilePictureName) {
        return PROFILE_PICTURES_PATH . "/$profilePictureName";
    }

    /**
     * Generates the valid file path for a PDF based on the user ID and PDF name.
     *
     * @param string $userId The unique identifier of the PDF owner.
     * @param string $pdfName The name of the PDF file.
     * @return string The full path to the PDF file in the format: `PDFS_PATH/userId/pdfName`.
     */
    public static function getPDFPathFromItsUserIdAndName(string $userId, string $pdfName) {
        return PDFS_PATH . "/$userId.$pdfName";
    }
    

    /**
     * Compress a JPG image file and return the compressed image data.
     *
     * @param string $imagePath The path to the JPEG image file.
     * @param float $compressionRatio The compression ratio, between 0 (lowest quality) and 1 (highest quality).
     * @return string The compressed image data as a string.
     */
    public static function compressJPGImage($imagePath, $compressionRatio) {
        // Load the JPG image using the GD library
        $image = imagecreatefromjpeg($imagePath);

        // Output the compressed image as a JPG to a buffer
        ob_start();
        imagejpeg($image, null, 100 * $compressionRatio);
        $compressedImageData = ob_get_clean();

        // Free up memory by destroying the GD image resource
        imagedestroy($image);

        // Return the compressed image data
        return $compressedImageData;
    }

}

?>