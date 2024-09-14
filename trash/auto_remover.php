<?php

// Function to handle removal based on item type
function handleRemoval($itemType, $itemId, $conn) {
    switch ($itemType) {
        case 'user':
            removeUser($itemId, $conn);
            break;
        case 'story':
            removeStoryAndComments($itemId, $conn);
            break;
        case 'comment':
            removeComment($itemId, $conn);
            break;
        default:
            // If an unknown type is provided, redirect back to home page
            header('Location: ../news/news.php');
            exit;
    }
}

// Function to remove a user and their associated data
function removeUser($userId, $conn) {
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
        $conn->rollback();
    }
}


// Function to remove a story and its comments
function removeStoryAndComments($storyId, $conn) {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Remove all comments of the story
        $removeCommentsQuery = "DELETE FROM comments WHERE story_id = ?";
        $removeCommentsStmt = $conn->prepare($removeCommentsQuery);
        $removeCommentsStmt->bind_param("i", $storyId);
        $removeCommentsStmt->execute();
        $removeCommentsStmt->close();

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

// Function to remove a comment
function removeComment($commentId, $conn) {
    $removeCommentQuery = "DELETE FROM comments WHERE id = ?";
    $removeCommentStmt = $conn->prepare($removeCommentQuery);
    $removeCommentStmt->bind_param("i", $commentId);
    $removeCommentStmt->execute();
    $removeCommentStmt->close();
}


?>
