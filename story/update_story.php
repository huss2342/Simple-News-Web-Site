<?php
require '/home/ec2-user/config/mod3/config.php';

// Check if the user is logged in
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to the login page if not logged in
    header('Location: /login/login.php');
    exit;
}

//check the token
if(!hash_equals($_SESSION['token'], $_POST['token'])){
    die("Request forgery detected");
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the story ID and form data
    $storyId = $_POST['id'];
    $title = $_POST['title'];
    $body = $_POST['body'];
    $link = $_POST['link']; 


    // Prepare the update statement
    $updateStoryQuery = "UPDATE stories SET title = ?, body = ?, link = ? WHERE id = ?";
    $stmt = $conn->prepare($updateStoryQuery);
    $stmt->bind_param("sssi", $title, $body, $link, $storyId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Story updated successfully, redirect to the story page
        $stmt->close(); // Close the prepared statement
        $conn->close(); // Close the database connection
        header('Location:story.php?id=' . $storyId);
        exit;
    } else {
        // Error updating the story, redirect to the news page or display an error message
        $stmt->close(); // Close the prepared statement
        $conn->close(); // Close the database connection
        header('Location: ../news.php');
        exit;
    }
} else {
    // Form not submitted, redirect to the news page or display an error message
    header('Location:.. /news.php');
    exit;
}
?>
