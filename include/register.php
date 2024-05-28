<?php
require_once ('../connect.php'); // Connect to the db.

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);
    $passwordpattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{7,}$/';
    //make sure the data is good - check email formatting
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["register_error"] = "Invalid email format";
        header("Location: ../register.html");
        exit;
    }
    //now verify password meets requirements
    elseif (!preg_match($passwordpattern, $password)) {
        $_SESSION["register_error"] = "Must be at least 7 characters long and contain an uppercase, lowercase, & number.";
        header("Location: ../register.html");
        exit;
    }
    //verify the password and confirm match
    elseif ($password !== $confirm_password) {
        $_SESSION["register_error"] = "Passwords must match.";
        header("Location: ../register.html");
        exit;
    }
    //no duplicate accounts
    else {
        $stmt = $conn->prepare("SELECT * FROM `entity_user` WHERE `email` = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $_SESSION["register_error"] = "Account already exists - try again to login.";
                header("Location: ../register.html");
                exit;
            } //we met all requirements, let's save to the db!
            else {
                //admin is defaulted in db
                $rank = 0;
                $surveys = 0;
                $stmt->close();
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO `entity_user` (`email`,`password`,`rank`,`surveys`) VALUES (?,?,?,?)");
                if ($stmt) {
                    $stmt->bind_param("ssii", $email, $password_hashed, $rank, $surveys);
                    if ($stmt->execute()) {
                        unset($_SESSION['register_error']);
                        $_SESSION["register_success"] = "Registration successful! Please log in.";
                        header("Location: ../login.php");
                        exit();
                    } else {
                        $_SESSION["register_error"] = "Unable to add user to database.";
                        header("Location: ../register.html");
                        exit;
                    }
                }
            }
        }
    }
}
?>