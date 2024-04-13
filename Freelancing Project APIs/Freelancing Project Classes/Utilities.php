

<?php

require_once "C:/Users/Tareq/Desktop/Freelancing Project APIs/Dependencies/autoload.php";

use Smalot\PdfParser\Parser;

class Utilities {

    public static function generateEmailVerificationCodes() {
        $emailVerificationCodes = array();
        while (count($emailVerificationCodes) < 100) {
            $currentCode = "";
            for ($i = 0; $i < 9; $i++) {
                $currentDigit = mt_rand(1, 9);
                $currentCode .= $currentDigit;
            }
            if (!isset($emailVerificationCodes[$currentCode])) {
                $emailVerificationCodes[$currentCode] = true;
            }
        }
        return array_keys($emailVerificationCodes);
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
     * Generates a new PDF path based on the freelancer ID and the new PDF name.
     *
     * @param int $freelancerId The ID of the freelancer.
     * @param string $newPDFName The name of the new PDF.
     * @return string The final path for the new PDF.
     */
    public static function generateANewPDFPath(int $freelancerId, string $newPDFName): string {
        // Define the default prefix path for the PDFs.
        $defaultPDFPrefixPath = "C:/Users/Tareq/Desktop/Freelancing Project Assets/PDFs/";

        // Construct the special name to add it to the path.
        $constructedSpecialNameToAddItToThePath = $freelancerId . "." . $newPDFName . ".pdf";

        // Combine the default prefix path and the constructed special name to create the final path for the new PDF.
        $finalPathForTheNewPDF = $defaultPDFPrefixPath . $constructedSpecialNameToAddItToThePath;

        // Return the final path for the new PDF.
        return $finalPathForTheNewPDF;
    }

    /**
     * Extracts the PDF name from its path.
     *
     * It is guaranteed that the input string has at least two dots in its characters. If not, a default PDF name is returned
     * to indicate that the PDF should be renamed. However, this scenario should never happen because the freelancer cannot
     * add a PDF without a name, and this name should not be empty and should not contain any dots.
     *
     * @param string $pdfPathString The path of the PDF file.
     * @return string The name of the PDF file.
     */
    public static function getPDFNameFromItsPath(string $pdfPathString): string {
        // Find the last and second last dot index in the string.
        $lastDotIndex = -10;
        $secondLastDotIndex = strlen($pdfPathString) + 10;
        for ($i = strlen($pdfPathString) - 1; $i >= 0; $i--) {
            if ($pdfPathString[$i] === '.') {
                if ($lastDotIndex === -10) {
                    $lastDotIndex = $i;
                } else {
                    $secondLastDotIndex = $i;
                    break;
                }
            }
        }

        // Calculate the length of the PDF name.
        $pdfNameLength = ($lastDotIndex - 1) - ($secondLastDotIndex + 1) + 1;
        if ($pdfNameLength <= 0) {
            // Return a default PDF name if the PDF name length is less than or equal to zero.
            return "Default PDF Name";
        }

        // Extract the PDF name from the string and return it.
        $pdfName = substr($pdfPathString, $secondLastDotIndex + 1, $pdfNameLength);
        return $pdfName;
    }

    /**
     * The following function generates a new PDF path from a given PDF path and a new PDF name.
     * 
     * It's guaranteed that the input string $pdfPathString has at least two dots in its characters. 
     * If not, the function returns null.
     * 
     * @param string $pdfPathString The path of the PDF file.
     * @param string $newPDFName The new name of the PDF file.
     * @return string|null The new path of the PDF file, or null if the input string $pdfPathString doesn't have two dots.
     */
    public static function getNewPDFPath($pdfPathString, $newPDFName) {
        $lastDotIndex = -10;
        $secondLastDotIndex = strlen($pdfPathString) + 10;
        for ($i = strlen($pdfPathString) - 1; $i >= 0; $i--) {
            if ($pdfPathString[$i] === '.') {
                if ($lastDotIndex === -10) {
                    $lastDotIndex = $i;
                }
                else {
                    $secondLastDotIndex = $i;
                    break;
                }
            }
        }

        $currentPDFNameLength = ($lastDotIndex - 1) - ($secondLastDotIndex + 1) + 1;
        if ($currentPDFNameLength <= 0) {
            return null;
        }

        $newPDFPath = substr_replace($pdfPathString, $newPDFName, $secondLastDotIndex + 1, $currentPDFNameLength);
        return $newPDFPath;
    }
    
    /**
     * Checks if the given file contents represent a valid PDF file
     *
     * @param string $fileContentsAsBytesString The binary contents of the file as a string of bytes
     * @return bool Returns true if the file is a valid PDF file, false otherwise
     */
    public static function isValidPDFFile($fileContentsAsBytesString) {
        // Check that the input parameter has a length of at least 5 bytes
        if (strlen($fileContentsAsBytesString) < 5) {
            return false;
        }

        if (strlen($fileContentsAsBytesString) < 5) {
            return false;
        }

        // Check the file magic number
        $magicNumber = unpack('H*', substr($fileContentsAsBytesString, 0, 5))[1];
        if ($magicNumber !== '255044462d') {
            return false;
        }

        // Check the file format and contents using the Smalot\PdfParser library
        $pdfParser = new Parser();
        try {
            // Parse the file contents and validate the file format
            $pdfParser->parseContent($fileContentsAsBytesString);
        } 
        catch (Exception $exception) {
            // If an exception is thrown, the file is not a valid PDF
            return false;
        }

        // If all checks passed, then the file is a valid PDF
        return true;
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