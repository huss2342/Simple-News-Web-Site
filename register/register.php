<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="../base.css">
</head>

<body>
    <header>
        <h1>Register</h1>
        <?php
        // if the user is logged in, send them back to the home page
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            echo '<div class="login-message">Logged in as: ' . $_SESSION['username'] . '</div>';
            // Redirect to the homepage
            header("Location: ../news.php");
        }
        ?>
    </header>

    <nav>
        <ul>
            <li><a href="../news.php">Home</a></li>
            <?php
            echo '<li><a href="../login/login.php">Login</a></li>';
            echo '<li><a href="register.php">Register</a></li>';
            ?>
        </ul>
    </nav>

    <main class="main-form">
        <div class="registration-form">

            <form method="POST" action="register_process.php">
                <label for="username"></label>
                <input type="text" name="username" placeholder="username" id="username" autocomplete="off" required><br><br>
                <label for="password"></label>
                <input type="password" name="password" placeholder="password" id="password" autocomplete="off" required><br><br>
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <input type="submit" value="Register">
                <?php
                // Display the registration error message if it exists
                if (isset($_SESSION['registration_error'])) {
                    echo '<div class="error-message">' . $_SESSION['registration_error'] . '</div>';
                    unset($_SESSION['registration_error']); // Clear the error message
                }
                ?>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; 2023 Simple News. All rights reserved.</p>
    </footer>
</body>

</html>