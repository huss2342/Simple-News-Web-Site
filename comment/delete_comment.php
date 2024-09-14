<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to the login page if not logged in
    header('Location: ../login/login.php');
    exit;
}

// Check if the comment ID parameter is provided
if (!isset($_POST['id'])) {
    // Redirect to the home page if comment ID is not provided
    header('Location: ../news.php');
    exit;
}

//check the token
if(!hash_equals($_SESSION['token'], $_POST['token'])){
    die("Request forgery detected");
}

// Database configuration
require '/home/ec2-user/config/mod3/config.php';

// Establish database connection
$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the comment ID and story ID from the POST data
$commentId = $_POST['id'];
$storyId = $_POST['story_id'];

// Soft delete the comment
$deletedAt = date('Y-m-d H:i:s');
$deleteCommentSql = "UPDATE comments SET deleted = 1, deleted_at = ? WHERE id = ?";
$deleteCommentStmt = $conn->prepare($deleteCommentSql);
$deleteCommentStmt->bind_param("si", $deletedAt, $commentId);
$deleteCommentStmt->execute();
$deleteCommentStmt->close();

// Close connection
$conn->close();

// Redirect to the story page after successful deletion
header('Location:../story/story.php?id=' . $storyId);
exit;
?>
