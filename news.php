<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <title>Simple News Website</title>
    <link rel="stylesheet" href="news.css">
    <link rel="stylesheet" href="base.css">
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

    <!-- navigation bar -->
    <nav>
        <ul>
            <li><a href="news.php">Home</a></li>
            <?php
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
                echo '<li><a href="story/submit_story.php">Submit Story</a></li>';
                if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
                    echo '<li><a href="users/users.php">Users</a></li>';
                    echo '<li><a href="trash/trash.php">Trash</a></li>'; 
                }
                echo '<li><a href="logout/logout.php">Logout</a></li>';
            } else {
                echo '<li><a href="login/login.php">Login</a></li>';
                echo '<li><a href="register/register.php">Register</a></li>';
            }
            ?>
        </ul>
    </nav>


    <main>
        <?php
        // Database configuration file
        require '/home/ec2-user/config/mod3/config.php';

        // Retrieve stories from the database
        $sql = "SELECT stories.*, users.username 
        FROM stories 
        JOIN users ON stories.user_id = users.id 
        -- This line is a filter condition. It ensures that the query only retrieves stories that haven't been marked as deleted
        WHERE stories.deleted = 0 
        -- This line is a sort condition. It ensures that the query retrieves stories in descending order by their creation date
        ORDER BY stories.created_at DESC";


        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="story">';
                echo '<h2><a class="story-link" href="story/story.php?id=' . $row['id'] . '">' . $row['title'] . '</a></h2>';
                echo '<div class="story-info">';
                echo '<p>Submitted by: ' . $row['username'] . '</p>';
                echo '</div>'; // End .story-info
                echo '</div>'; // End .story
            }
        } else {
            echo '<p>No stories found.</p>';
        }

        // Close connection
        $conn->close();
        ?>
    </main>

    <footer>
        <p>&copy; 2023 Simple News. All rights reserved.</p>
    </footer>
</body>

</html>