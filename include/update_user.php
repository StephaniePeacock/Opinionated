<?php
require_once('connect.php');

$id = $_POST['id'];
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$is_admin = $_POST['is_admin'];
$rank = $_POST['rank'];
$surveys = $_POST['surveys'];

// Check if email has been changed
$stmt = $conn->prepare("SELECT `email` FROM `entity_user` WHERE `id_user` = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($existingEmail);
$stmt->fetch();
$stmt->close();

if ($email !== $existingEmail) {
    // Email has been changed, validate and check uniqueness
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = "Invalid email format";
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM `entity_user` WHERE `email` = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $response['error'] = "Email already exists";
        echo json_encode($response);
        exit;
    }
}

// Check if password has been provided
if(isset($_POST['password']) && !empty($_POST['password'])){
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $passwordpattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{7,}$/';
    
    function validatePassword($password, $passwordpattern) {
        return preg_match($passwordpattern, $password);
    }
    
    if (!validatePassword($password, $passwordpattern)) {
        $response['error'] = "Password must be at least 7 characters long and contain an uppercase, lowercase letter, and a number.";
        echo json_encode($response);
        exit;
    }

    // Hash the password
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);
}

// Update user
$query = "UPDATE `entity_user` SET `email` = ?, `is_admin` = ?, `rank` = ?, `surveys` = ?";
$types = "siii";
$params = array($email, $is_admin, $rank, $surveys);

// Add password to query and parameters if it has been changed
if(isset($password_hashed)){
    $query .= ", `password` = ?";
    $types .= "s";
    $params[] = $password_hashed;
}

$query .= " WHERE `id_user` = ?";
$types .= "i";
$params[] = $id;

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);

$response = array(); // Create an empty array for the response

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = "User updated successfully";
} else {
    $response['error'] = "Error updating user: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Return the response as JSON
echo json_encode($response);
?>
