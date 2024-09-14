<?php
session_start();
require '/home/ec2-user/config/mod3/config.php';


// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: /login/login.php');
    exit;
}

//check the token
if(!hash_equals($_SESSION['token'], $_POST['token'])){
    die("Request forgery detected");
}


// Check if the user ID parameter is provided
if (!isset($_POST['user_id'])) {
    header('Location: users.php');
    exit;
}


// Establish database connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the user ID from the POST data
$userId = $_POST['user_id'];

// Get the current timestamp
$deletedAt = date('Y-m-d H:i:s');

// Soft delete the user
$softDeleteUserSql = "UPDATE users SET deleted = 1, deleted_at = ? WHERE id = ?";
$softDeleteUserStmt = $conn->prepare($softDeleteUserSql);
$softDeleteUserStmt->bind_param("si", $deletedAt, $userId);

if ($softDeleteUserStmt->execute()) {
    // User soft deletion successful
    $softDeleteUserStmt->close();
    
    // Soft delete the user's stories
    $softDeleteStoriesSql = "UPDATE stories SET deleted = 1, deleted_at = ? WHERE user_id = ?";
    $softDeleteStoriesStmt = $conn->prepare($softDeleteStoriesSql);
    $softDeleteStoriesStmt->bind_param("si", $deletedAt, $userId);
    $softDeleteStoriesStmt->execute();
    $softDeleteStoriesStmt->close();
    
    // Soft delete the user's comments
    $softDeleteCommentsSql = "UPDATE comments SET deleted = 1, deleted_at = ? WHERE user_id = ?";
    $softDeleteCommentsStmt = $conn->prepare($softDeleteCommentsSql);
    $softDeleteCommentsStmt->bind_param("si", $deletedAt, $userId);
    $softDeleteCommentsStmt->execute();
    $softDeleteCommentsStmt->close();

    $conn->close();
    header('Location: users.php');
    exit;
} else {
    // Failed to soft delete the user
    $softDeleteUserStmt->close();
    $conn->close();
    die("Failed to soft delete the user. Error: " . $conn->error);
}
?>
