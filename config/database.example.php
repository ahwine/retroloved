<?php
/**
 * Database Configuration - EXAMPLE FILE
 * Copy this file to database.php and update with your credentials
 */

// Database connection settings
$host = 'localhost';
$username = 'root';          // Your MySQL username
$password = '';              // Your MySQL password
$database = 'retroloved';    // Database name

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");

/**
 * Helper function untuk query
 */
function query($query) {
    global $conn;
    return mysqli_query($conn, $query);
}

/**
 * Helper function untuk escape string
 */
function escape($string) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($string));
}

/**
 * Helper function untuk validasi email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Helper function untuk set message
 */
function set_message($type, $text) {
    $_SESSION['message'] = [
        'type' => $type,
        'text' => $text
    ];
}
?>
