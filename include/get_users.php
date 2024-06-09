<?php
require_once('connect.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="style-admin.css"> <!-- Include style-admin.css -->
</head>
<body>

<?php
$query = "SELECT `id_user` AS `USER_UID`, `email` AS `EMAIL`, `password` AS `PASSWORD`, `is_admin` AS `IS_ADMIN`, `rank` AS `RANK`, `surveys` AS `SURVEYS` FROM `entity_user`";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

echo '<table border="1">';
echo '<tr><th>Email</th><th>Password</th><th>Admin</th><th>Rank</th><th>Surveys</th><th> </th><th> </th></tr>';
while ($row = $result->fetch_assoc()) {
    echo '<tr id="user-' . htmlspecialchars($row['USER_UID']) . '">';
    echo "<td contenteditable='true' class='email'>" . htmlspecialchars($row['EMAIL']) . "</td>";
    echo "<td contenteditable='true' class='password'></td>";
    echo "<td contenteditable='true' class='is_admin'>" . htmlspecialchars($row['IS_ADMIN']) . "</td>";
    echo "<td contenteditable='true' class='rank'>" . htmlspecialchars($row['RANK']) . "</td>";
    echo "<td contenteditable='true' class='surveys'>" . htmlspecialchars($row['SURVEYS']) . "</td>";
    echo "<td><button onclick='updateUser(" . htmlspecialchars($row['USER_UID']) . ")'>Update</button></td>";
    echo "<td><button onclick='deleteUser(" . htmlspecialchars($row['USER_UID']) . ")'>Delete</button></td>";
    echo '</tr>';
}
echo '</table>';

$conn->close();
?>
