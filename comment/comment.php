<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to the login page if not logged in
    header('Location: ../login/login.php');
    exit;
}

// Check if the story ID parameter is provided
if (!isset($_POST['id'])) {
    // Redirect to the home page if story ID is not provided
    header('Location: ../news.php');
    exit;
}

//check the token
if(!hash_equals($_SESSION['token'], $_POST['token'])){
    die("Request forgery detected");
}

// Database configuration
require '/home/ec2-user/config/mod3/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the comment submission
    if (isset($_POST['comment'])) {
        $storyId = $_POST['id'];
        $comment = $_POST['comment'];

        // Prepare an insert statement to add the comment to the database
        $insertSql = "INSERT INTO comments (user_id, story_id, comment) VALUES (?, ?, ?)";
        if ($insertStmt = $conn->prepare($insertSql)) {
            // Bind the parameters
            $insertStmt->bind_param("iis", $_SESSION['id'], $storyId, $comment);

            // Execute the insert statement
            $insertStmt->execute();

            // Close the insert statement
            $insertStmt->close();

            // After processing the comment, redirect to the news page with the story ID
            header('Location:../story/story.php?id=' . $storyId);
            exit;
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

// Close the database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Comment</title>
    <link rel="stylesheet" href="edit_comment.css">
    <link rel="stylesheet" href="../base.css">
</head>
<body>
    <header>
        <h1>Add Comment</h1>
        <?php
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            echo '<div class="login-message">Logged in as: ' . $_SESSION['username'] . '</div>';
        }
        ?>
    </header>

    <nav>
        <ul>
            <li><a href="../news.php">Home</a></li>
            <?php
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
                echo '<li><a href="../story/submit_story.php">Submit Story</a></li>';
                if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
                    echo '<li><a href="../users/users.php">Users</a></li>';
                    echo '<li><a href="../trash/trash.php">Trash</a></li>'; // Add the Trash tab for admin
                }
                echo '<li><a href="../logout/logout.php">Logout</a></li>';
            } else {
                echo '<li><a href="../login/login.php">Login</a></li>';
                echo '<li><a href="../register/register.php">Register</a></li>';
            }
            ?>
        </ul>
    </nav>

    <main>
        <div class="edit-comment-form">
            <form method="POST" action="comment.php">
                <input type="hidden" name="id" value="<?php echo $_POST['id']; ?>">
                <textarea name="comment" required></textarea>
                <input type="submit" value="Submit Comment">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <p><a href="../story/story.php?id=<?php echo $_POST['id']; ?>">Cancel</a></p>
            </form>
        </div>
    </main>
</body>
</html>
