<?php
// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to the login page if not logged in
    header('Location: ../login/login.php');
    exit;
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Submission Success</title>
     <link rel="stylesheet" href="../base.css">
</head>
<body>
    <header>
        <h1>Submission Success</h1>
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
                echo '<li><a href="../logout/news.php?logout=true">Logout</a></li>';
            } else {
                echo '<li><a href="../login/login.php">Login</a></li>';
                echo '<li><a href="../register/register.php">Register</a></li>';
            }

            ?>
        </ul>
    </nav>

    <main>
        <p>Your story has been submitted successfully!</p>
    </main>

    <footer>
        <p>&copy; 2023 Simple News. All rights reserved.</p>
    </footer>
</body>
</html>
