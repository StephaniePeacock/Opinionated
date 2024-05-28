<?php
require_once('../connect.php'); // Connect to the db.
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);
    $passwordpattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{7,}$/';

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    }
    // Validate password
    elseif (!preg_match($passwordpattern, $password)) {
        $error = "Must be at least 7 characters long and contain an uppercase, lowercase, & number.";
    }
    // Confirm passwords match
    elseif ($password !== $confirm_password) {
        $error = "Passwords must match.";
    }
    // Check for duplicate account
    else {
        $stmt = $conn->prepare("SELECT * FROM `entity_user` WHERE `email` = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = "Account already exists - try again to login.";
            } else {
                // Create new account
                $stmt->close();
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO `entity_user` (`email`, `password`, `rank`, `surveys`) VALUES (?, ?, ?, ?)");
                if ($stmt) {
                    $rank = 0;
                    $surveys = 0;
                    $stmt->bind_param("ssii", $email, $password_hashed, $rank, $surveys);
                    if ($stmt->execute()) {
                        header("Location: ../login.html");
                        exit();
                    } else {
                        $error = "Unable to add user to database.";
                    }
                }
            }
        }
    }
    
    // Redirect back to the registration form with error
    if (isset($error)) {
        header("Location: ../register.html?error=" . urlencode($error));
        exit();
    }
}
?>
