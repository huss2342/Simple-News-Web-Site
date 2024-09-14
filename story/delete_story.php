<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /login/login.php');
    exit;
}

// Check if the story ID parameter is provided
if (!isset($_POST['id'])) {
    header('Location: /news.php');
    exit;
}

// Database configuration
require '/home/ec2-user/config/mod3/config.php';

// Establish database connection
$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the story ID from the POST data
$storyId = $_POST['id'];

// Retrieve the comments associated with the story
$commentsQuery = "SELECT id FROM comments WHERE story_id = ?";
$commentsStmt = $conn->prepare($commentsQuery);
$commentsStmt->bind_param("i", $storyId);
$commentsStmt->execute();
$commentsResult = $commentsStmt->get_result();
$commentsStmt->close();

// Soft delete the story and associated comments
$deletedAt = date('Y-m-d H:i:s');

// Soft delete the story
$deleteStorySql = "UPDATE stories SET deleted = 1, deleted_at = ? WHERE id = ? AND (user_id = ? OR ? = 1)";
$deleteStoryStmt = $conn->prepare($deleteStorySql);
$deleteStoryStmt->bind_param("siii", $deletedAt, $storyId, $_SESSION['id'], $_SESSION['is_admin']);
$deleteStoryStmt->execute();
$deleteStoryStmt->close();

// Soft delete the associated comments
while ($comment = $commentsResult->fetch_assoc()) {
    $commentId = $comment['id'];

    // Soft delete the comment
    $deleteCommentSql = "UPDATE comments SET deleted = 1, deleted_at = ? WHERE id = ?";
    $deleteCommentStmt = $conn->prepare($deleteCommentSql);
    $deleteCommentStmt->bind_param("si", $deletedAt, $commentId);
    $deleteCommentStmt->execute();
    $deleteCommentStmt->close();
}

// Close connection
$conn->close();

// Redirect to the home page after successful deletion
header('Location: ../news.php');
exit;
?>
