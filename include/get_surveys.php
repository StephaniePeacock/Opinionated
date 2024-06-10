<?php
require_once('connect.php'); // Connect to the db

$query = "SELECT id_survey, title, description FROM entity_survey";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Title</th><th>Description</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr id='survey-{$row['id_survey']}'>";
        echo "<td class='title'>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td class='description'>" . htmlspecialchars($row['description']) . "</td>";
        echo "<td><button onclick='modifySurvey({$row['id_survey']})'>Modify</button></td>";
        echo "<td><button onclick='deleteSurvey({$row['id_survey']})'>Delete</button></td>";
        echo "<td><button onclick='window.location.href=\"results.html?id={$row['id_survey']}\"'>Results</button></td>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No surveys found.";
}

$conn->close();
?>
