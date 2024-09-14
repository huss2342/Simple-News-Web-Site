<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="../base.css">
</head>

<body>
    <header>
        <h1>Login</h1>
        <?php
        // Check if the user is already logged in
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            echo '<div class="login-message">Logged in as: ' . $_SESSION['username'] . '</div>';
            // Redirect to the homepage
            header("Location: ../news.php");
            exit; 
        }
        ?>
    </header>

    <nav>
        <ul>
            <li><a href="../news.php">Home</a></li>
            <?php
            echo '<li><a href="login.php">Login</a></li>';
            echo '<li><a href="../register/register.php">Register</a></li>';
            ?>
        </ul>
    </nav>

    <main>
        <div class="login-form">

            <form method="POST" action="login_process.php">
                <label for="username"></label>
                <input type="text" name="username" placeholder="username" id="username" autocomplete="off" required><br><br>
                <label for="password"></label>
                <input type="password" name="password" id="password" placeholder="password" autocomplete="off" required><br><br>
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <input type="submit" value="Login">
            </form>

            <?php
            // Check for error message in the URL and display it
            if (isset($_SESSION['login_error'])) {
                echo '<div class="error-message">' . $_SESSION['login_error'] . '</div>';
                unset($_SESSION['login_error']); // Clear the error message
            }
            ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 Simple News. All rights reserved.</p>
    </footer>
</body>

</html>