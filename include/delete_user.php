<?php
require_once('connect.php');

$id = $_POST['id'];

$query = "DELETE FROM `entity_user` WHERE `id_user` = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "User deleted successfully";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
