<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to the login page if not logged in
    header('Location: ../login/login.php');
    exit;
}

// Check if the comment ID parameter is provided
if (!isset($_POST['comment_id'])) {
    // Redirect to the home page if comment ID is not provided
    header('Location: /news.php');
    exit;
}

//check the token
if(!hash_equals($_SESSION['token'], $_POST['token'])){
    die("Request forgery detected");
}

// Database configuration
require '/home/ec2-user/config/mod3/config.php';

// Prepare a select statement to fetch the comment details
$selectSql = "SELECT * FROM comments WHERE id = ?";
if ($selectStmt = $conn->prepare($selectSql)) {
    // Bind the comment ID parameter
    $selectStmt->bind_param("i", $_POST['comment_id']);

    // Execute the select statement
    $selectStmt->execute();

    // Store the result
    $selectResult = $selectStmt->get_result();

    // Check if the comment exists
    if ($selectResult->num_rows === 1) {
        // Fetch the comment data
        $commentData = $selectResult->fetch_assoc();

        // Check if the comment belongs to the logged-in user or if the user is an admin
        if ($commentData['user_id'] === $_SESSION['id'] || $_SESSION['is_admin'] === 1) {
            // Store the comment text
            $comment = $commentData['comment'];
        } else {
            echo "You are not authorized to edit this comment.";
            exit;
        }
    } else {
        echo "Comment not found.";
        exit;
    }

    // Close the select statement
    $selectStmt->close();
} else {
    echo "Error: " . $conn->error;
    exit;
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <title>Edit Comment</title>
    <link rel="stylesheet" href="edit_comment.css">
    <link rel="stylesheet" href="../base.css">
</head>

<body>
    <header>
        <h1>Edit Comment</h1>
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
            <form action="update_comment.php" method="POST">
                <input type="hidden" name="comment_id" value="<?php echo $_POST['comment_id']; ?>">
                <input type="hidden" name="story_id" value="<?php echo $commentData['story_id']; ?>">
                <textarea name="comment"><?php echo $comment; ?></textarea>
                <input type="submit" value="Update">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <p><a href="../story/story.php?id=<?php echo $commentData['story_id']; ?>">Cancel</a></p>
            </form>
        </div>
    </main>
</body>

</html>