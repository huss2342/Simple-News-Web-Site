<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to the login page if not logged in
    header('Location: /login/login.php');
    exit;
}

// Check if the comment ID and comment text are provided
if (!isset($_POST['comment_id']) || !isset($_POST['comment']) || empty($_POST['comment'])) {
    // Redirect back to the news page if comment ID or comment text is not provided
    header('Location: /news.php');
    exit;
}

//check the token
if(!hash_equals($_SESSION['token'], $_POST['token'])){
    die("Request forgery detected");
}

// Database configuration
require '/home/ec2-user/config/mod3/config.php';


// Prepare an update statement for the comment
$updateSql = "UPDATE comments SET comment = ? WHERE id = ?";
if ($updateStmt = $conn->prepare($updateSql)) {
    // Bind the parameters
    $updateStmt->bind_param("si", $_POST['comment'], $_POST['comment_id']);

    // Execute the update statement
    if ($updateStmt->execute()) {
        // Close the update statement
        $updateStmt->close();

        // Close the database connection
        $conn->close();

        // Redirect back to the story page
        $storyId = isset($_POST['story_id']) ? $_POST['story_id'] : '';
        header('Location:../story/story.php?id=' . $storyId);
        exit;
    } else {
        echo "Error: " . $updateStmt->error;
    }


    // Close the update statement
    $updateStmt->close();
} else {
    echo "Error: " . $conn->error;
}

// Close the database connection
$conn->close();
?>