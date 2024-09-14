<?php
session_start();
require '/home/ec2-user/config/mod3/config.php';


// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Redirect to the login page or home page if not logged in or not an admin
    header('Location: ../login/login.php');
    exit;
}

// Check if the item type and ID parameters are provided
if (!isset($_POST['item_type']) || !isset($_POST['item_id'])) {
    // Redirect to the trash page if item type or ID is not provided
    header('Location: trash.php');
    exit;
}

//check the token
if(!hash_equals($_SESSION['token'], $_POST['token'])){
    die("Request forgery detected");
}


// Retrieve the item type and ID from the request parameters
$itemType = $_POST['item_type'];
$itemId = $_POST['item_id'];

// Restore the item based on its type
if ($itemType === 'user') {
    restoreUser($itemId, $conn);
} else if ($itemType === 'story') {
    restoreStory($itemId, $conn);
} else if ($itemType === 'comment') {
    restoreComment($itemId, $conn);
}

// After restoring the item, redirect back to the trash page
header('Location: trash.php');
exit;

// Function to restore a user
function restoreUser($userId, $conn)
{
    // Restore the user by updating the 'deleted' column to 0 and 'deleted_at' to NULL
    $restoreUserQuery = "UPDATE users SET deleted = 0, deleted_at = NULL WHERE id = ?";
    $restoreUserStmt = $conn->prepare($restoreUserQuery);
    $restoreUserStmt->bind_param("i", $userId);
    $restoreUserStmt->execute();
    $restoreUserStmt->close();
}

// Function to restore a story
function restoreStory($storyId, $conn)
{
    // Check if the related user is not deleted
    $userCheckQuery = "SELECT users.deleted as user_deleted, users.username FROM stories JOIN users ON stories.user_id = users.id WHERE stories.id = ? AND stories.deleted = 1";
    $userCheckStmt = $conn->prepare($userCheckQuery);
    $userCheckStmt->bind_param("i", $storyId);
    $userCheckStmt->execute();
    $userCheckResult = $userCheckStmt->get_result();

    if ($userCheckResult->num_rows > 0) {
        $userData = $userCheckResult->fetch_assoc();
        if ($userData['user_deleted'] == 1) {
            $_SESSION['error'] = 'Cannot restore comment because the related user is soft deleted: ' . $userData['username'];
            header('Location: trash.php');
            exit;
        }
    }
    $userCheckStmt->close();

    // Restore the story by updating the 'deleted' column to 0 and 'deleted_at' to NULL
    $restoreStoryQuery = "UPDATE stories SET deleted = 0, deleted_at = NULL WHERE id = ?";
    $restoreStoryStmt = $conn->prepare($restoreStoryQuery);
    $restoreStoryStmt->bind_param("i", $storyId);
    $restoreStoryStmt->execute();
    $restoreStoryStmt->close();
}


// Function to restore a comment
function restoreComment($commentId, $conn)
{
    // Check if the related story and user are not deleted
    $checkQuery = "SELECT stories.deleted as story_deleted, stories.title, users.deleted as user_deleted, users.username 
        FROM comments 
        JOIN stories ON comments.story_id = stories.id 
        JOIN users ON comments.user_id = users.id 
        WHERE comments.id = ? AND comments.deleted = 1";
        
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $commentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $data = $checkResult->fetch_assoc();
        
        if ($data['user_deleted'] == 1) {
            $_SESSION['error'] = 'Cannot restore comment because the related user is soft deleted: ' . $data['username'];
            header('Location: trash.php');
            exit;
        }

        if ($data['story_deleted'] == 1) {
            $_SESSION['error'] = 'Cannot restore comment because the related story is soft deleted: ' . $data['title'];
            header('Location: trash.php');
            exit;
        }
    }
    $checkStmt->close();

    // Restore the comment by setting 'deleted' to 0 and 'deleted_at' to NULL
    $restoreCommentQuery = "UPDATE comments SET deleted = 0, deleted_at = NULL WHERE id = ?";
    $restoreCommentStmt = $conn->prepare($restoreCommentQuery);
    $restoreCommentStmt->bind_param("i", $commentId);
    $restoreCommentStmt->execute();
    $restoreCommentStmt->close();
}

