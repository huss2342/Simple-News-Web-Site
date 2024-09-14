<?php
session_start();
require '/home/ec2-user/config/mod3/config.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Redirect to the login page or home page if not logged in or not an admin
    header('Location: ../login/login.php');
    exit;
}

// Check if the user ID parameter is provided
if (!isset($_POST['user_id'])) {
    // Redirect to the users page if user ID is not provided
    header('Location: users.php');
    exit;
}


// Retrieve the user's information from the database
$getUserSql = "SELECT * FROM users WHERE id = ?";
$getUserStmt = $conn->prepare($getUserSql);
$getUserStmt->bind_param("i", $_POST['user_id']);
$getUserStmt->execute();
$userResult = $getUserStmt->get_result();
$userData = $userResult->fetch_assoc();
$getUserStmt->close();

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <title>Delete User Confirmation</title>
    <link rel="stylesheet" href="../base.css">
    <link rel="stylesheet" href="users.css">
</head>

<body>
    <header>
        <h1>Delete User Confirmation</h1>
        <?php
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            echo '<div class="login-message">Logged in as: ' . $_SESSION['username'] . '</div>';
        }
        ?>
    </header>

    <nav>
        <ul>
            <li><a href="news.php">Home</a></li>
            <?php
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
                echo '<li><a href="../story/submit_story.php">Submit Story</a></li>';
                if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
                    echo '<li><a href="users.php">Users</a></li>';
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
        <h2>Confirm User Deletion</h2>
        <p>Are you sure you want to delete the following user?</p>
        <p><strong>Username:</strong>
            <?php echo $userData['username']; ?>
        </p>
        <form method="POST" action="delete_user.php">
            <input type="hidden" name="user_id" value="<?php echo $userData['id']; ?>">
            <button type="submit" class="user-delete">Delete User</button>
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <a href="users.php" class="cancel-button">Cancel</a>
        </form>
    </main>

    <footer>
        <p>&copy; 2023 Simple News. All rights reserved.</p>
    </footer>
</body>

</html>