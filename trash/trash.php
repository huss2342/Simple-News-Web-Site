<?php
session_start();
require '/home/ec2-user/config/mod3/config.php';
include 'auto_remover.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Redirect to the login page or home page if not logged in or not an admin
    header('Location: ../login/login.php');
    exit;
}

// Retrieve the deleted users, stories, and comments
$deletedItems = [
    'users' => "SELECT 'user' AS item_type, id AS item_id, deleted_at FROM users WHERE deleted = 1",
    'stories' => "SELECT 'story' AS item_type, id AS item_id, user_id, deleted_at FROM stories WHERE deleted = 1",
    'comments' => "SELECT 'comment' AS item_type, id AS item_id, user_id, story_id, deleted_at FROM comments WHERE deleted = 1"
];
?>

<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <title>Trash</title>
    <link rel="stylesheet" href="../base.css">
    <link rel="stylesheet" href="trash.css">
</head>

<body>
    <header>
        <h1>Trash</h1>
        <?php
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            echo '<div class="login-message">Logged in as: ' . $_SESSION['username'] . '</div>';
        }
        ?>
    </header>

    <nav>
        <ul>
            <li><a href="../news.php">Home</a></li>
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <li><a href="../story/submit_story.php">Submit Story</a></li>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                    <li><a href="../users/users.php">Users</a></li>
                    <li><a href="trash.php">Trash</a></li>
                <?php endif; ?>
                <li><a href="../logout/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="../login/login.php">Login</a></li>
                <li><a href="../register/register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <main>
        <?php
        // Check if there is an error message to display
        if (isset($_SESSION['error'])) {
            echo "<div class='error-message'>{$_SESSION['error']}</div>"; // Display the error message
            unset($_SESSION['error']); // Remove the error message from the session data
        }
        ?>
        <?php
        foreach ($deletedItems as $item => $query) {
            echo '<h2>Deleted ' . ucfirst($item) . '</h2>';
            $result = $conn->query($query);
            displayDeletedItems($result, $conn);
        }
        ?>
    </main>

    <footer>
        <p>&copy; 2023 Simple News. All rights reserved.</p>
    </footer>

    <?php
    // Close the database connection
    $conn->close();
    ?>
</body>

</html>

<?php
// Function to display deleted items
function displayDeletedItems($result, $conn)
{
    if ($result->num_rows > 0) {
        echo '<table>';
        echo '<tr><th>Summary</th><th>Owner</th><th>Time left</th><th>Actions</th></tr>';
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . getItemData($row['item_type'], $row['item_id'], $conn) . '</td>';

            // Modify this line to handle the case when 'user_id' key is not available in the row
            $userId = isset($row['user_id']) ? $row['user_id'] : null;

            // Check the time left for permanent deletion
            $timeLeft = getTimeLeftForPermanentDeletion($row['deleted_at']);
            if ($timeLeft === 'Ready for permanent deletion') {
                 handleRemoval($row['item_type'], $row['item_id'], $conn);
            } else {
                echo '<td>' . getItemOwner($row['item_type'], $userId, $conn) . '</td>';
                echo '<td class="time-left">' . $timeLeft . '</td>';
                echo '<td>';
                echo '<form action="restore_item.php" method="POST">';
                echo '<input type="hidden" name="item_type" value="' . $row['item_type'] . '">';
                echo '<input type="hidden" name="item_id" value="' . $row['item_id'] . '">';
                echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
                echo '<button type="submit" class="restore-button">Restore</button>';
                echo '</form>';
                echo '<form action="remove_item.php" method="POST">';
                echo '<input type="hidden" name="item_id" value="' . $row['item_id'] . '">';
                echo '<input type="hidden" name="item_type" value="' . $row['item_type'] . '">';
                echo '<input type="hidden" name="token" value="' . $_SESSION['token'] . '">';
                echo '<button type="submit" class="remove-button">Remove</button>';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
        }

        echo '</table>';
    } else {
        echo '<p>No items found in the trash.</p>';
    }
}

// Function to get the time left for permanent deletion WITHOUT JAVSCRIPT
function getTimeLeftForPermanentDeletion($deletedAt) {
    // inspired from https://www.php.net/manual/en/datetime.modify.php
    $deletedAt = new DateTime($deletedAt);
    $deletionEnd = clone $deletedAt; // Clone to not modify the original datetime
    $deletionEnd->modify('+28 days'); // Add 28 days to the deletion time to get the end time
    
    $now = new DateTime();
    
    if ($now >= $deletionEnd) {
        return 'Ready for permanent deletion';
    }
    
    $interval = $now->diff($deletionEnd); // Difference between now and the end time
    return "{$interval->d} days, {$interval->h} hours, {$interval->i} minutes, {$interval->s} seconds left";
}



// Function to get item data based on its type
function getItemData($itemType, $itemId, $conn)
{
    switch ($itemType) {
        case 'story':
            // Retrieve the story title from the 'stories' table
            $query = "SELECT title FROM stories WHERE id = $itemId";
            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['title'];
            }
            break;
        case 'comment':
            // Retrieve the comment text from the 'comments' table
            $query = "SELECT comment FROM comments WHERE id = $itemId";
            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['comment'];
            }
            break;
        case 'user':
            // Retrieve the username of the user from the 'users' table
            $query = "SELECT username FROM users WHERE id = $itemId";
            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['username'];
            }
            break;
    }
    return 'N/A';
}



// Function to get the owner of an item
function getItemOwner($itemType, $userId, $conn)
{
    if ($userId === null || $userId === '') {
        return 'admin'; //to enforce dominance
    }
    switch ($itemType) {

        case 'story':
        case 'comment':
            // Retrieve the username of the story/comment owner from the 'users' table
            if ($userId !== null) { // Check if $userId is not null
                $query = "SELECT username FROM users WHERE id = $userId";
                $result = $conn->query($query);
                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    return $row['username'];
                }
            }
            break;
        case 'user':
            // Retrieve the username of the user from the 'users' table
            $query = "SELECT username FROM users WHERE id = $userId"; // Use $userId instead of $itemId
            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['username'];
            }
            break;
    }
    return 'N/A';
}


?>