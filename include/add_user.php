<?php
require_once('connect.php'); // Connect to the db.

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
        $error = "Password must be at least 7 characters long and contain an uppercase, lowercase letter, and a number.";
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
                $error = "Account already exists.";
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
                        echo "<script>
                                alert('User added successfully');
                                window.location.href = '../register.html';
                              </script>";
                        exit();
                    } else {
                        $error = "Unable to add user to database.";
                    }
                }
            }
        }
    }

    // Determine redirect URL based on user_data cookie
    $redirect_url = '../register.html';
    if (isset($_COOKIE['user_data'])) {
        $user_data = json_decode($_COOKIE['user_data'], true);
        // Debugging: Check the cookie content
        error_log('User Data Cookie: ' . print_r($user_data, true));
        if (isset($user_data['IS_ADMIN']) && $user_data['IS_ADMIN'] == 1) {
            $redirect_url = '../userMgmt.html';
        }
    } else {
        // Debugging: Check if the cookie is not set
        error_log('User Data Cookie is not set.');
    }

    // Redirect back to the add user form with error
    if (isset($error)) {
        echo "<script>

                window.location.href = '$redirect_url?error=" . urlencode($error) . "';
              </script>";
        exit();
    }
}
?>
