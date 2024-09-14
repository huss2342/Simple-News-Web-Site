<?php
session_start();
require '/home/ec2-user/config/mod3/config.php';
include 'remove_functions.php';

// If an error message exists in the session variables, display it
if (isset($_SESSION['error'])) {
    echo "<div class='error-message'>" . $_SESSION['error'] . "</div>";
    // Remove the error message from the session variables so it doesn't persist across pages
    unset($_SESSION['error']);
}

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Redirect to the login page or home page if not logged in or not an admin
    header('Location: ../login/login.php');
    exit;
}

// Check if the item type and ID parameters are provided
if (!isset($_POST['item_type']) || !isset($_POST['item_id'])) {
    // Redirect to the home page if item type or ID is not provided
    header('Location: ../news.php');
    exit;
}

//check the token
if(!hash_equals($_SESSION['token'], $_POST['token'])){
    die("Request forgery detected");
}


// Function to manually remove an item based on item type
function handleManualRemoval($itemType, $itemId, $conn) {
    switch ($itemType) {
        case 'user':
            manuallyRemoveUser($itemId, $conn);
            break;
        case 'story':
            manuallyRemoveCommentsThenStory($itemId, $conn);
            break;
        case 'comment':
            manuallyRemoveComment($itemId, $conn);
            break;
        default:
            // If an unknown type is provided, redirect back to trash page
            header('Location: trash.php');
            exit;
    }
}

// Retrieve the item type and ID from the request parameters
$itemType = $_POST['item_type'];
$itemId = $_POST['item_id'];

// Handle manual removal
handleManualRemoval($itemType, $itemId, $conn);

function manuallyRemoveUser($userId, $conn) {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Remove all comments of the user
        $removeUserCommentsQuery = "DELETE FROM comments WHERE user_id = ?";
        $removeUserCommentsStmt = $conn->prepare($removeUserCommentsQuery);
        $removeUserCommentsStmt->bind_param("i", $userId);
        $removeUserCommentsStmt->execute();
        $removeUserCommentsStmt->close();

        // Remove all stories of the user
        $removeUserStoriesQuery = "DELETE FROM stories WHERE user_id = ?";
        $removeUserStoriesStmt = $conn->prepare($removeUserStoriesQuery);
        $removeUserStoriesStmt->bind_param("i", $userId);
        $removeUserStoriesStmt->execute();
        $removeUserStoriesStmt->close();

        // Remove the user
        $removeUserQuery = "DELETE FROM users WHERE id = ?";
        $removeUserStmt = $conn->prepare($removeUserQuery);
        $removeUserStmt->bind_param("i", $userId);
        $removeUserStmt->execute();
        $removeUserStmt->close();

        // If no exception has been thrown, commit the transaction
        $conn->commit();
    } catch (Exception $e) {
        // An exception has been thrown, rollback the transaction
        //https://www.w3schools.com/php/func_mysqli_rollback.asp
        $conn->rollback();
    }
}

function manuallyRemoveComment($commentId, $conn) {
    $removeCommentQuery = "DELETE FROM comments WHERE id = ?";
    $removeCommentStmt = $conn->prepare($removeCommentQuery);
    $removeCommentStmt->bind_param("i", $commentId);
    $removeCommentStmt->execute();
    $removeCommentStmt->close();
}

function manuallyRemoveCommentsThenStory($storyId, $conn) {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Remove all comments of the story
        $removeStoryCommentsQuery = "DELETE FROM comments WHERE story_id = ?";
        $removeStoryCommentsStmt = $conn->prepare($removeStoryCommentsQuery);
        $removeStoryCommentsStmt->bind_param("i", $storyId);
        $removeStoryCommentsStmt->execute();
        $removeStoryCommentsStmt->close();

        // Remove the story
        $removeStoryQuery = "DELETE FROM stories WHERE id = ?";
        $removeStoryStmt = $conn->prepare($removeStoryQuery);
        $removeStoryStmt->bind_param("i", $storyId);
        $removeStoryStmt->execute();
        $removeStoryStmt->close();

        // If no exception has been thrown, commit the transaction
        $conn->commit();
    } catch (Exception $e) {
        // An exception has been thrown, rollback the transaction
        $conn->rollback();
    }
}

// After removing the item, redirect back to the trash page
header('Location: trash.php');
exit;
?>
