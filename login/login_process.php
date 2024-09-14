<?php
session_start();
// Database configuration
require '/home/ec2-user/config/mod3/config.php';

// Handle user login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs and sanitize username
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate form inputs (add more validation if needed)
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Something went wrong. Please try again later.";
        header("Location: login.php");
        exit;
    }

    // Retrieve the user's hashed password, ID, and admin status from the database
    $sql = "SELECT id, password, is_admin, deleted FROM users WHERE BINARY username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $_SESSION['login_error'] = "Something went wrong. Please try again later.";
        header("Location: login.php");
        exit;
    }  
    if( !preg_match('/^[\w_\.\-]+$/', $username) ){
        $_SESSION['login_error'] = "Invalid Character";
        header("Location: login.php");
        exit;
    }
    
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Retrieve the case-sensitive username from the database
    $sql_case_sensitive = "SELECT BINARY username FROM users WHERE username = ?";
    $stmt_case_sensitive = $conn->prepare($sql_case_sensitive);

    if ($stmt_case_sensitive === false) {
        $_SESSION['login_error'] = "Something went wrong. Please try again later.";
        header("Location: login.php");
        exit;
    }

    $stmt_case_sensitive->bind_param("s", $username);
    $stmt_case_sensitive->execute();
    $result_case_sensitive = $stmt_case_sensitive->get_result();
    
    // Check if the case-sensitive username matches
    if ($result->num_rows === 1 && $result_case_sensitive->num_rows === 1) {
        $row = $result->fetch_assoc();
        $hashedPassword = $row['password'];
        
        // check if the user is soft deleted first
        $deleted = $row['deleted'];
        if(($deleted === 1)){
            $_SESSION['login_error'] = "User marked for deletion";
            header("Location: login.php");
            exit;
        }
        // Verify the password
        if (password_verify($password, $hashedPassword)) {
            // Password is correct, set session variables to indicate user is logged in
            $_SESSION['username'] = $username;
            $_SESSION['loggedin'] = true;
            $_SESSION['is_admin'] = ($row['is_admin'] == 1);
            $_SESSION['id'] = $row['id']; // Add this line to set the user ID in the session

            //CSRF TOKEN STUFF
            $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32)); // generate a 32-byte random string

            // Redirect to the homepage
            header("Location: ../news.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Invalid username or password.2";
            header("Location: login.php");
            exit;
        }
    } else {
        // User not found or case-sensitive username doesn't match
        $_SESSION['login_error'] = "Invalid username or password.3";
        header("Location: login.php");
        exit;
    }
}



// Close the database connection
$conn->close();


?>
