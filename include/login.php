<?php
require_once('../connect.php'); // Connect to the db.

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // Validate email and password are not empty
    if (empty($email) || empty($password)) {
        $error = "Email and Password are required.";
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT `password` FROM `entity_user` WHERE `email` = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($hashed_password);
                $stmt->fetch();
                if (password_verify($password, $hashed_password)) {
                    // Password is correct
                    $_SESSION['user'] = $email;
                    echo "<script>window.parent.location.href = '../home.html';</script>";
                    exit();
                } else {
                    // Invalid password
                    $error = "Invalid email or password.";
                }
            } else {
                // User not found
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Database error.";
        }
    }
    
    // Redirect back to the login form with error
    if (isset($error)) {
        header("Location: ../login.html?error=" . urlencode($error));
        exit();
    }
}
?>
