<?php
/**
 * config-example.php
 *
 * This is an example configuration file for the ProHub project.
 * 
 * Instructions:
 * 1. Copy this file and rename it to "config.php".
 * 2. Replace the placeholder values with your actual configuration details.
 * 3. Do not commit your "config.php" with real credentials to your repository.
 *    Ensure that "config.php" is added to your .gitignore file.
 */

// ----------------------
// Database configuration
// ----------------------
// Replace 'your-database-host', 'your-database-username', etc. with your actual database info.
define('DB_HOST', 'your-database-host');               // e.g., 'localhost'
define('DB_USER_NAME', 'your-database-username');        // e.g., 'root'
define('DB_USER_PASSWORD', 'your-database-password');    // e.g., 'your_password'
define('DB_NAME', 'your-database-name');                 // e.g., 'db_prohub'

// ----------------------------
// Basic Authentication config
// ----------------------------
// These credentials are used for Basic Authentication.
define('API_USERNAME', 'your-api-username');
define('API_PASSWORD', 'your-api-password');

// ----------------------------
// Paths configuration
// ----------------------------
// Update the following paths to point to the correct directories on your system.
// It is recommended to use absolute paths.
define('CLASSES_PATH', 'path/to/prohub-classes');
define('DEPENDENCIES_PATH', 'path/to/prohub-dependencies');
define('PROFILE_PICTURES_PATH', 'path/to/prohub-assets/profile-pictures');
define('PDFS_PATH', 'path/to/prohub-assets/pdfs');
define('PROHUB_VERIFICATION_EMAIL_PATH', 'path/to/prohub-assets/prohub-verification-email.html');

// ----------------------------
// Include required class files
// ----------------------------
// Make sure the paths defined above are correct so that these files are included properly.
require_once CLASSES_PATH . '/Utilities.php';
require_once CLASSES_PATH . '/HTTPHandler.php';
require_once CLASSES_PATH . '/Authenticator.php';
require_once CLASSES_PATH . '/DatabaseConnection.php';

?>
