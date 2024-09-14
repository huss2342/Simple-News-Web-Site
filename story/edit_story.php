<?php
require '/home/ec2-user/config/mod3/config.php';

// Check if the user is logged in
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to the login page if not logged in
    header('Location: ../login/login.php');
    exit;
}


// Check if the story ID is provided in the URL
if (isset($_GET['id'])) {
    $storyId = $_GET['id'];

    // Retrieve the story data from the database
    $selectStoryQuery = "SELECT title, body, link FROM stories WHERE id = ?";
    $stmt = $conn->prepare($selectStoryQuery);
    $stmt->bind_param("i", $storyId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Check if the story exists
    if ($row) {
        $storyTitle = $row['title'];
        $storyContent = $row['body'];
        $storyLink = $row['link'];
    } else {
        // Story not found, redirect to the news page or display an error message
        $conn->close(); // Close the database connection
        header('Location: ../news.php');
        exit;
    }

    // Close the prepared statement and database connection
    $stmt->close();
    $conn->close();
} else {
    // Story ID not provided, redirect to the news page or display an error message
    header('Location: ../news.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <title>Edit Story</title>
    <link rel="stylesheet" href="edit_story.css">
    <link rel="stylesheet" href="../base.css">
</head>

<body>
    <header>
        <h1>Edit Story</h1>
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
        <div class="story-form">
            <h2>Edit Story</h2>
            <form method="POST" action="update_story.php">
                <input type="hidden" name="id" value="<?php echo $storyId; ?>">
                <input type="text" name="title" autocomplete="off" value="<?php echo $storyTitle; ?>" required>
                <textarea name="body" required><?php echo $storyContent; ?></textarea>
                <input type="text" name="link" autocomplete="off" value="<?php echo $storyLink; ?>">
                <input type="submit" value="Update Story">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <p><a href="<?php echo 'story.php?id=' . $storyId; ?>">Cancel</a></p>
            </form>
        </div>
    </main>
</body>

</html>