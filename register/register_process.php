<?php
session_start();

// Database configuration
require '/home/ec2-user/config/mod3/config.php';

// Handle user registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate form inputs (add more validation if needed)
    if (empty($username) || empty($password)) {
        $_SESSION['registration_error'] = "Please enter both username and password.";
        header("Location: register.php");
        exit;
    }

    // Check if the username already exists
    $checkUsernameQuery = "SELECT id FROM users WHERE username = ?";
    $checkUsernameStmt = $conn->prepare($checkUsernameQuery);

    if ($checkUsernameStmt === false) {
        $_SESSION['registration_error'] = "Something went wrong. Please try again later.";
        header("Location: register.php");
        exit;
    }

    if( !preg_match('/^[\w_\.\-]+$/', $username) ){
        $_SESSION['registration_error'] = "Invalid Character";
        header("Location: register.php");
        exit;
    }


    $checkUsernameStmt->bind_param("s", $username);
    $checkUsernameStmt->execute();
    $checkUsernameResult = $checkUsernameStmt->get_result();

    if ($checkUsernameResult->num_rows > 0) {
        // Username already exists
        $_SESSION['registration_error'] = "Username already taken. Please choose a different username.";
        header("Location: register.php");
        exit;
    }

    // Hash and salt the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user data into the database
    $insertUserQuery = "INSERT INTO users (username, password) VALUES (?, ?)";
    $insertUserStmt = $conn->prepare($insertUserQuery);
    $insertUserStmt->bind_param("ss", $username, $hashedPassword);

    if ($insertUserStmt->execute()) {
        // Registration successful
        $_SESSION['username'] = $username; // Create session for the user
        $_SESSION['loggedin'] = true; // Set the user's login status
        $_SESSION['id'] = $conn->insert_id; // Store the ID of the last inserted record

        //CSRF TOKEN STUFF
        $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32)); // generate a 32-byte random string

        // Redirect to the home page
        header("Location: ../news.php");
        exit();
    } else {
        echo "Error: " . $insertUserStmt->error;
    }

    $insertUserStmt->close();
    $checkUsernameStmt->close();
}

// Close the database connection
$conn->close();
?>
