<?php
require_once('connect.php'); // Connect to the db.

function getUser($email, $db) {
    $query = "
    SELECT 
        entity_user.id_user AS USER_UID, 
        entity_user.email AS EMAIL, 
        entity_user.password AS PASSWORD, 
        entity_user.is_admin AS IS_ADMIN, 
        entity_user.rank AS RANK, 
        entity_user.surveys AS SURVEYS,
        enum_rank.rank AS RANK_TITLE
    FROM entity_user 
    JOIN enum_rank 
    ON entity_user.rank = enum_rank.id_rank 
    WHERE entity_user.email = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // Validate email and password are not empty
    if (empty($email) || empty($password)) {
        $error = "Email and Password are required.";
    } else {
        // Check if user exists
        $userData = getUser($email, $conn);
        if ($userData) {
            if (password_verify($password, $userData['PASSWORD'])) {
                // Password is correct
                setcookie('user_data', json_encode($userData), time() + 3600, "/");
                $_SESSION['user_data'] = $userData;
                echo "<script>window.parent.location.href = '../home.php';</script>";
                exit();
            } else {
                // Invalid password
                $error = "Invalid email or password.";
            }
        } else {
            // User not found
            $error = "Invalid email or password.";
        }
    }
    
    // Redirect back to the login form with error
    if (isset($error)) {
        header("Location: ../login.html?error=" . urlencode($error));
        exit();
    }
}
?>
