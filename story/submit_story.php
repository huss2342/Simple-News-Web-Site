<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to the login page if not logged in
    header('Location: ../login/login.php');
    exit;
}


//check the token AND a submission request is sent
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(!hash_equals($_SESSION['token'], $_POST['token'])){
        die("Request forgery detected");
    }
}

// Database configuration
require '/home/ec2-user/config/mod3/config.php';


// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form inputs
    $title = $_POST['title'];
    $body = $_POST['body'];
    $link = $_POST['link'];

    // Check if the user ID is set in the session
    if (isset($_SESSION['id'])) {
        $user_id = $_SESSION['id'];

        // Debugging statement
        echo "User ID: " . $user_id . "<br>";

        // Prepare an insert statement
        $sql = "INSERT INTO stories (user_id, title, body, link) VALUES (?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("isss", $user_id, $title, $body, $link);

            // Debugging statement
            echo "Bound Values: " . $user_id . " " . $title . " " . $body . " " . $link . "<br>";

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to the submission success page
                header('Location: submission_success.php');
                exit;
            } else {
                echo "Error: " . $stmt->error;
            }

            // Close statement
            $stmt->close();
        }
    } else {
        echo "User ID is not set in the session.";
    }
}

// Close connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <title>Submit Story</title>
    <link rel="stylesheet" href="../base.css">
    <link rel="stylesheet" href="edit_story.css">
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
                    echo '<li><a href="../trash/trash.php">Trash</a></li>';
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
            <h2>Submit Story</h2>
            <form action="submit_story.php" method="POST">
                <label for="title">Title:</label><br>
                <input type="text" id="title" name="title" autocomplete="off" required><br>
                <label for="body">Body:</label><br>
                <textarea id="body" name="body" rows="4" required></textarea><br>
                <label for="link">Link:</label><br>
                <input type="text" id="link" name="link" autocomplete="off"><br>
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <input type="submit" value="Submit" class="submit-button">
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 Simple News. All rights reserved.</p>
    </footer>
</body>

</html>