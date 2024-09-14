<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie


//got it from: https://www.php.net/manual/en/function.session-destroy.php
if (ini_get("session.use_cookies")) {

    // Get the parameters of the session cookie
    $params = session_get_cookie_params();

    // Set the session cookie with empty value and an expiration time in the past
    setcookie(
        session_name(),     // The name of the session cookie
        '',                 // The value of the session cookie (empty in this case)
        time() - 42000,     // Expiration time set to a past time (e.g., 42000 seconds ago)
        $params["path"],    // The path on the server where the cookie is valid
        $params["domain"],  // The domain on which the cookie is valid
        $params["secure"],  // Indicates whether the cookie should only be sent over HTTPS
        $params["httponly"] // Makes the cookie accessible only through the HTTP protocol
    );
}


session_unset();   // Unset all session variables
session_destroy(); // Destroy the session

// Redirect to the home page
header('Location: ../news.php');
exit;
?>
