<?php
session_start();
require '/home/ec2-user/config/mod3/config.php';




// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Redirect to the login page or home page if not logged in or not an admin
    header('Location: ../login/login.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <title>Users</title>
    <link rel="stylesheet" href="users.css">
    <link rel="stylesheet" href="../base.css">
</head>

<body>
    <header>
        <h1>Users</h1>
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
                    echo '<li><a href="users.php">Users</a></li>';
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
        <?php

        // Retrieve the users from the database
        $usersQuery = "SELECT * FROM users WHERE deleted = 0";

        $usersResult = $conn->query($usersQuery);

        if ($usersResult->num_rows > 0) {
            // Create separate arrays for admin and regular users
            $adminUsers = array();
            $regularUsers = array();

            while ($row = $usersResult->fetch_assoc()) {
                if ($row['is_admin'] == 1) {
                    $adminUsers[] = $row;
                } else {
                    $regularUsers[] = $row;
                }
            }

            // Merge the arrays, placing admin users first
            $mergedUsers = array_merge($adminUsers, $regularUsers);

            // Output the user table
            echo '<div class="table-wrapper">';
            echo '<table class="user-table">';
            echo '<tr><th>Username</th><th>Action</th></tr>';

            foreach ($mergedUsers as $user) {
                echo '<tr>';
                echo '<td>' . $user['username'] . '</td>';
                echo '<td>';
                // Show delete button for each user except the admin to themain king admin
                if ($_SESSION['is_admin'] && $user['is_admin'] != 1 &&   $_SESSION['username'] === "admin") {

                    if( ($user['username'] != "admin") && ($user['username'] != $_SESSION['username'])){
                        echo '<form action="delete_user_confirmation.php" method="POST">';
                        echo '<input type="hidden" name="user_id" value="' . $user['id'] . '">';
                        echo '<input type="submit" value="Delete" class="user-delete">';
    
                        echo '</form>';
    
                    }



                  
                    //check if the current user is an admin or not 
                
                   
                    echo '<form action="make_admin.php" method="POST">';
                    echo '<input type="hidden" name="promoted_user" value="' . $user['id'] . '">';
                    echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
                    echo '<input type="submit" value="Promote to Admin" class="user-promote">';
                    echo '</form>';
                    
              

                   
                }
                //checks for admin properties and outputs info based on that
                else if ($_SESSION['is_admin'] && $user['is_admin'] != 0 && $username === "admin") {

                    //give the admin the option to delete anyone who is not the king admin
                    if( ($user['username'] != "admin") && ($user['username'] !=   $_SESSION['username'])){
                    echo '<form action="delete_user_confirmation.php" method="POST">';
                    echo '<input type="hidden" name="user_id" value="' . $user['id'] . '">';
                    echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
                    echo '<input type="submit" value="Delete" class="user-delete">';

                    echo '</form>';

                    }

                    // if the highlighted user is not an admin

                    //outputs demote buttom 
                    if( ($user['username'] != "admin") && ($user['username'] !=   $_SESSION['username'])){
                    echo '<form action="demote_admin.php" method="POST">';
                    echo '<input type="hidden" name="demoted_user" value="' . $user['id'] . '">';
                    echo '<input type="submit" value="Demote to Normal User" class="user-demote">';
                    echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
                    echo '</form>';
                    }
                }
                else if($_SESSION['is_admin'] && $user['is_admin'] != 1 && $username != "admin"){



                    if( ($user['username'] != "admin") && (  $_SESSION['username']!=$user['username'] )){
                        echo '<form action="delete_user_confirmation.php" method="POST">';
                        echo '<input type="hidden" name="user_id" value="' . $user['id'] . '">';
                        echo '<input type="submit" value="Delete" class="user-delete">';
                        echo '</form>';
                    }
                



                    
                    //check if the current user is an admin or not 
                
                  //outputs promote to admin button 
                    echo '<form action="make_admin.php" method="POST">';
                    echo '<input type="hidden" name="promoted_user" value="' . $user['id'] . '">';
                    echo '<input type="submit" value="Promote to Admin" class="user-promote">';
                    echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
                    echo '</form>';
                    
                   







                }
                else if ($_SESSION['is_admin'] && $user['is_admin'] != 0 && $username != "admin") {

                    //give the admin the option to delete anyone who is not the king admin
                    if( ($user['username'] != "admin") && (  $_SESSION['username']!=$user['username'] )){
                    echo '<form action="delete_user_confirmation.php" method="POST">';
                    echo '<input type="hidden" name="user_id" value="' . $user['id'] . '">';
                    echo '<input type="submit" value="Delete" class="user-delete">';
                    echo '</form>';

                    }

                    // if the highlighted user is not an admin

                    //outputs demote button
                    if( ($user['username'] != "admin") && (  $_SESSION['username']!=$user['username'] )){
                    echo '<form action="demote_admin.php" method="POST">';
                    echo '<input type="hidden" name="demoted_user" value="' . $user['id'] . '">';
                    echo '<input type="submit" value="Demote to Normal User" class="user-demote">';
                    echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
                    echo '</form>';
                    }
                }







                echo '</td>';
                echo '</tr>';
            }

            echo '</table>';
            echo '</div>';
        } else {
            echo '<p>No users found.</p>';
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