<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <title>Simple News Website</title>
    <link rel="stylesheet" href="story.css">
    <link rel="stylesheet" href="../base.css">
</head>

<body>
    <header>
        <h1>Welcome to Simple News</h1>
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
                echo '<li><a href="submit_story.php">Submit Story</a></li>';
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
        <?php
        // Database configuration
        require '/home/ec2-user/config/mod3/config.php';

        // Retrieve the story id from the URL
        $story_id = $_GET['id'];

        // Prepare the SQL query using prepared statements
        $sql = "SELECT stories.*, users.username 
        FROM stories 
        JOIN users ON stories.user_id = users.id 
        WHERE stories.id = ? AND stories.deleted = 0";

        if ($stmt = $conn->prepare($sql)) {
            // Bind the story ID parameter
            $stmt->bind_param("i", $story_id);

            // Execute the query
            $stmt->execute();

            // Get the result
            $result = $stmt->get_result();

            // Check if the story exists
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="story">';
                    echo '<h2 class="story-title">' . $row['title'] . '</h2>';
                    if (!empty($row['link'])) {
                        echo '<a href="' . $row['link'] . '" class="link-button">' . $row['link'] . '</a>';
                    }
                    echo '<div class="story-content">';
                    echo '<p>' . $row['body'] . '</p>';
                    echo '</div>'; // End .story-content
                    echo '<div class="story-info">';
                    echo '<p>Submitted by: ' . $row['username'] . '</p>';
                    echo '<p>Created at: ' . $row['created_at'] . '</p>';
                    echo '<p>Updated at: ' . $row['updated_at'] . '</p>';
                    echo '</div>'; // End .story-info

                    // Add edit and delete buttons for the story
                    if (isset($_SESSION['id']) && ($_SESSION['id'] == $row['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true))) {
                        // Add delete button for the story
                        echo '<form action="delete_story.php" method="POST">';
                        echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
                        echo '<button type="submit" class="story-delete-button">Delete Story</button>';
                        echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
                        echo '</form>';
                    }

                    if (isset($_SESSION['id']) && ($_SESSION['id'] == $row['user_id'])) {
                        // Add edit button for the story
                        echo '<form method="GET" action="edit_story.php">';
                        echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
                        echo '<button type="submit" class="story-edit-button">Edit Story</button>';
                        echo '</form>';
                    }

                    // Now retrieve and display the comments for the story
                    $commentsSql = "SELECT comments.*, users.username 
                    FROM comments 
                    JOIN users ON comments.user_id = users.id 
                    WHERE comments.story_id = ? AND comments.deleted = 0"; // Only fetch non-deleted comments

                    if ($commentsStmt = $conn->prepare($commentsSql)) {
                        // Bind the story ID parameter
                        $commentsStmt->bind_param("i", $row['id']);

                        // Execute the query
                        $commentsStmt->execute();

                        // Get the result
                        $commentsResult = $commentsStmt->get_result();

                        // Display comments
                        if ($commentsResult->num_rows > 0) {
                            echo '<p>Comments:</p>';
                            while ($commentRow = $commentsResult->fetch_assoc()) {

                                // Display each comment
                                echo '<div class="comment">';
                                echo '<p><strong>' . $commentRow['username'] . ':</strong> ' . $commentRow['comment'] . '</p>';

                                // Add edit and delete buttons for the comments

                                // Add delete button for the comments
                                if (isset($_SESSION['id']) && ($_SESSION['id'] == $commentRow['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true))) {
                                    echo '<form action="../comment/delete_comment.php" method="POST">';
                                    echo '<input type="hidden" name="id" value="' . $commentRow['id'] . '">';
                                    echo '<input type="hidden" name="story_id" value="' . $row['id'] . '">';
                                    echo '<input type="submit" value="Delete" class="comment-delete-button">';
                                    echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
                                    echo '</form>';
                                }

                                // Add edit button for the comments
                                if (isset($_SESSION['id']) && ($_SESSION['id'] == $commentRow['user_id'])) {
                                    // Edit Button
                                    echo '<form action="../comment/edit_comment.php" method="POST">';
                                    echo '<input type="hidden" name="comment_id" value="' . $commentRow['id'] . '">';
                                    echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
                                    echo '<input type="submit" value="Edit" class="comment-edit-button">';
                                    echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
                                    echo '</form>';
                                }

                                echo '</div>'; // End .comment
                            }
                        } else {
                            echo '<p>No comments yet.</p>';
                        }

                        // Close the prepared statement for comments
                        $commentsStmt->close();
                    } else {
                        echo "Error: " . $conn->error;
                    }

                    // Add comment button for the story
                    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
                        echo '<form method="POST" action="../comment/comment.php">';
                        echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
                        echo '<button type="submit" class="comment-button">Comment</button>';
                        echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
                        echo '</form>';
                    } else {
                        echo '<p class="error-message">Please <a href="../login/login.php">login</a> to comment.</p>';
                    }

                    echo '</div>'; // End .story
                }
            } else {
                echo '<p>Story not found.</p>';
            }

            // Close the prepared statement
            $stmt->close();
        } else {
            echo "Error: " . $conn->error;
        }

        // Close the database connection
        $conn->close();
        ?>
    </main>

    <footer>
        <p>&copy; 2023 Simple News. All rights reserved.</p>
    </footer>
</body>

</html>
