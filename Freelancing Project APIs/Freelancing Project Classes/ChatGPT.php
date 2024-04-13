<?php

// gpt-3.5-turbo
// I should bring the GPT-4 model name and put it here in this comment like the above model 
class ChatGPT {
    private $apiEndpoint;
    private $apiKey;
    private $headers;
    private $body;
    private $modelName;
    private $lastResponseString;
    private $lastRequestString;
    
    
    // This is the constructor where in my use case, it's better to pass only the 
    public function __construct($modelName) {
        $this->apiEndpoint = 'https://api.openai.com/v1/chat/completions';
        $this->apiKey = 'sk-xBocXO0PwIBBmTC8lPSKT3Blbkthathasbeenchanged';
        $this->headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];
        
        $this->modelName = $modelName;
        $this->body = [
            'model' => $this->modelName,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello, are you ready to assist me today with your unbelievable abilities?']
            ],
        ];
        $this->lastRequestString = "";
        $this->lastResponseString = "";
    }

    // This function sets the model name, where $modelName should be a string
    public function setModelName($modelName) {
        $this->modelName = $modelName;
    }
    
    // This function sets the model name as a string
    public function getModelName() {
        return $this->modelName;
    }
    
    // This function sets the request headers, and here the parameter is an indexed array of strings
    public function setHeaders($headers) {
        $this->headers = $headers;
    }

    // This function returns the headers as an indexed array of strings
    public function getHeaders() {
        return $this->headers;
    }

    // This function sets the request body
    private function setBody($message) {
        $this->body = [
            'model' => $this->modelName,
            'messages' => [
                ['role' => 'user', 'content' => $message]
            ],
        ];
    }

    // This function returns the body as an associative array
    public function getBody() {
        return $this->body;
    }

    

    public function getLastResponseString() {
        return $this->lastResponseString;
    }

    public function getLastRequestString() {
        return $this->lastRequestString;
    }

    // This function returns a string that represents the full response of the ChatGPT API call
    private function getFullResponse($message) {

        $this->setBody($message);

        // Initialize cURL session
        $ch = curl_init($this->apiEndpoint);
        
        // I should delete this statement when I upload the file to the SmarterASP.NET server
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // Return response as string
        curl_setopt($ch, CURLOPT_POST, true);  // Use POST method
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->body));  // Set request body
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);  // Set request headers

        // Execute the API call, and return a string
        $responseString = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            return 'cURL error: ' . curl_error($ch);
        }

        // Close cURL session
        curl_close($ch);


        $currentRequestAssociativeArray = array("request headers" => $this->headers,
                                                "request body" => $this->body);
        $this->lastRequestString = print_r($currentRequestAssociativeArray, true);
        $this->lastResponseString = $responseString;
        return $responseString;
        
    }

    // This function returns only the message content that the ChatGPT will return as a response
    // Also it's important to know that the return type here is a string
    public function getResponseContenet($message) {

            $responseString = $this->getFullResponse($message);
        
            if ((strlen($responseString) >= 10) && substr($responseString, 0, 10) == 'cURL error') {
                return $responseString;
            }

            if ((!Utilities::isValidJson($responseString)) || (strlen($responseString) == 0)) {
                return "An error occurred, because the ChatGPT full response wasn't in a valid JSON format.";
            }

            // Decode the JSON string response body into an associative array
            $responseArray = json_decode($responseString, true);
            
            if (isset($responseArray['choices']) &&
                isset($responseArray['choices'][0]) &&
                isset($responseArray['choices'][0]['message']) &&
                isset($responseArray['choices'][0]['message']['content'])) {
                
                return (string)($responseArray['choices'][0]['message']['content']);

            }
            else {
                return "An error occurred, because the ChatGPT full response wasn't in the excpected format, it's a ".
                       "valid JSON format, but it doesn't contains all the required data";
            }

    }


    // This function returns an indexed arrays of tags that are related to the passed $job, where $job should be a string
    public function getJobTags($job) {

        $gettingJobTagsMessage = 
            "Please categorize the following job post and return the result in ".
            "a JSON object with the following format: {\"tags\": [\"tag1\", \"tag2\", ...]}, ".
            "where each tag is a string with the first letter capitalized. \nIf the job ".
            "information is not clear enough to categorize or if you are unable to find any ".
            "applicable tags, please return {\"tags\": null}, also you shouldn't return more ".
            "than 10 tags, I prefer 6 to 8 tags in general but as you find to be approprite I will be satisfied. ".
            "So you must take care of the job post giving it the full due categories it deserves.\n".
            "I am expecting you to follow the categorization of the most common ".
            "freelancing platforms like the UpWork for example. \nAlso, make sure to return the ".
            "tags as null in case the job post is bad or seems un appropriate in terms of ".
            "ethics and also in terms of coherence in all of the ideas of the following job ".
            "post at the same time.\nAnd the last thing is that if you can make the tag just one word, like instead ".
            "of \"Python Development\" just say \"Python\", but not in every tag for sure as you prefer, and don't ever include single or double quotes ".
            "or anything that is related to special characters that can make issues with the MySQL database, so don't ever include quotes, ".
            "and here is the job description:\n\n";
            
        // Get the response as a string that is actually represents a JSON format 
        $responseString = $this->getResponseContenet($gettingJobTagsMessage.$job);

        // If it's not in a JSON format that means that there is an error and I should return it
        if (!Utilities::isValidJson($responseString)) {
            return $responseString;
        }
        
        // Decode JSON-encoded response body into an associative array
        $responseToJSON = json_decode($responseString, true);

        $tags = [];

        if (isset($responseToJSON['tags'])) {

            // I should also check the following condition to fully validate the $tags array
            // Although the first condition is included in the second one, I still would like to check it too ;).
            if (($responseToJSON['tags'] == null) || ($responseToJSON['tags'] == []) || Utilities::isIndexedArrayOfStrings($responseToJSON, ["tags"])) {
                return [];
                /*
                return "An error occurred, because the ChatGPT full response wasn't in the excpected format, it's a ".
                       "valid JSON format, but the response content array \$tags is not an indexed array of strings";
                */
            }

            // Capture the tags in an indexed array and return it back
            $tags = $responseToJSON['tags'];
            
        }

        return $tags;
    }

    // This function returns an indexed arrays of tags that represents the string passed parameter $freelancerDescription
    public function getFreelancerTags($freelancerDescription) {
        
        $gettingFreelancerTagsMessage = 
            "Please categorize the following freelancer based on their self-description and ".
            "return the result in a JSON object with the following format: {\"tags\": [\"tag1\", \"tag2\", ...]}, ".
            "where each tag is a string with the first letter capitalized. \nIf the freelancer's ".
            "self-description is not clear enough to categorize or if you are unable to find any ".
            "applicable tags, please return {\"tags\": null}, also you shouldn't return more ".
            "than 10 tags and I prefer 6 to 8 tags in general but as you find to be approprite I will be happy. ".
            "So you must take care of him giving him the full due categories he deserves.\n.".
            "I am expecting you to follow the categorization of the most common ".
            "freelancing platforms like the UpWork for example. \nAlso, make sure to return the ".
            "tags as null in case the self-description is bad or seems un appropriate in terms of ".
            "ethics and also in terms of coherence in all of the ideas of the freelancer's self-description at the same time.\n".
            "And the last thing is that if you can make the tag just one word, like instead ".
            "of \"Python Development\" just say \"Python\", but not in every tag for sure as you prefer, and don't ever include single or double quotes ".
            "or anything that is related to special characters that can make issues with the MySQL database, so don't ever include quotes, and ".
            "here is the freelancer description:\n\n";

        // Get the response as a string that is actually represents a JSON format
        $responseString = $this->getResponseContenet($gettingFreelancerTagsMessage.$freelancerDescription);
        
        // If it's not in a JSON format that means that there is an error and I should return it
        if (!Utilities::isValidJson($responseString)) {
            return $responseString;
        }
        
        // Decode JSON-encoded response body into an associative array
        $responseToJSON = json_decode($responseString, true);

        $tags = [];

        if (isset($responseToJSON['tags'])) {

            // I should also check the following condition to fully validate the $tags array
            // Although the first condition is included in the second one, I still would like to check it too ;).
            if (($responseToJSON['tags'] == null) || ($responseToJSON['tags'] == []) || Utilities::isIndexedArrayOfStrings($responseToJSON, ["tags"])) {
                return [];
                /*
                return "An error occurred, because the ChatGPT full response wasn't in the excpected format, ok it's a ".
                       "valid JSON format, but the response content array \$tags is not an indexed array of strings";
                */
            }

            // Capture the tags in an indexed array and return it back
            $tags = $responseToJSON['tags'];
            
        }

        return $tags;
    }

}

?>