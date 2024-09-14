<?php
session_start();
require '/home/ec2-user/config/mod3/config.php';

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to the login page if not logged in
    header('Location: ../login/login.php');
    exit;
}

//check the token
if(!hash_equals($_SESSION['token'], $_POST['token'])){
    die("Request forgery detected");
}

// Check if the promoted_user parameter is provided
if (!isset($_POST['demoted_user'])) {
    // Redirect to the home page if promoted_user ID is not provided
    header('Location: /news.php');
    exit;
}


// Prepare a select statement to fetch the comment details
$selectSql = "SELECT * FROM users WHERE id = ?";
if ($selectStmt = $conn->prepare($selectSql)) {
    // Bind the promoted_user parameter
    $selectStmt->bind_param("i",$_POST['demoted_user']);

    // Execute the select statement
    $selectStmt->execute();

    // Store the result
    $selectResult = $selectStmt->get_result();

    // Check if the user exists
    if ($selectResult->num_rows === 1) {


        // Fetch the user data
        $userData = $selectResult->fetch_assoc();
        echo "<br>USER DATA FOUND ";
    
            echo "<br>User ID    ".$userData['is_admin'];
        


    } else {
        echo "<br>user to be demoted not found.";
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







<?php
require '/home/ec2-user/config/mod3/config.php';


// Prepare an update statement for the user
$updateSql = "UPDATE users SET is_admin = ? WHERE id = ?";
if ($updateStmt = $conn->prepare($updateSql)) {
    // Bind the parameters
    $isadmin = 0;
    $updateStmt->bind_param("ii",     $isadmin, $userData['id']);

    // Execute the update statement
    if ($updateStmt->execute()) {
        // Close the update statement
        $updateStmt->close();

        // Close the database connection
        $conn->close();

        // Redirect back to the users page
        
        header('Location:../users/users.php?');
        exit;
    } else {
        echo "Error: " . $updateStmt->error;
    }


    // Close the update statement
    $updateStmt->close();
} else {
    echo "Error: " . $conn->error;
}

// Close the database connection
$conn->close();
?>


