<?php
// Set the folder path here, so I should put the path of the folder that contains
// all the PHP files, in other words, I have to take a copy the folder "Freelancing Project APIs" that is
// located on the desktop in my case, and name that copy for example "testing" as the following.
$folder_path = "C:/Users/Tareq/Desktop/testing";

// Set up an array of search and replace strings
$search = array(
    "C:/Users/Tareq/Desktop/Freelancing Project Assets/Profile Pictures/",
    "C:/Users/Tareq/Desktop/Freelancing Project APIs/",
);

$replace = array(
    "h:/root/home/tareqmahmood-001/www/prohub/Freelancing Project Assets/Profile Pictures/",
    "h:/root/home/tareqmahmood-001/www/prohub/",
);

// Loop through all the PHP files in the folder and its subfolders
$directory_iterator = new RecursiveDirectoryIterator($folder_path);
$iterator = new RecursiveIteratorIterator($directory_iterator);

foreach ($iterator as $filename) {
    if (pathinfo($filename, PATHINFO_EXTENSION) === 'php') { // Only modify PHP files
        // Read the contents of the file
		if (pathinfo($filename, PATHINFO_FILENAME) === 'prepareTheProjectForTheServer') {
            continue;
        }
		
        $contents = file_get_contents($filename);

        // Replace all occurrences of the search strings with the replacement strings
        $new_contents = str_replace($search, $replace, $contents);

        // Write the new contents back to the file
        file_put_contents($filename, $new_contents);
    }
}


?>