<?php
require_once('connect.php'); // Connect to the db

$user_id = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_COOKIE['user_data'])) {
    $user_data = json_decode($_COOKIE['user_data'], true);
    if (isset($user_data['USER_UID'])) {
        $user_id = $user_data['USER_UID'];
    } else {
        echo "User not authenticated";
        exit;
    }
} else {
    echo "User not authenticated";
    exit;
}

$completedSurveysQuery = $conn->prepare("SELECT DISTINCT id_survey FROM xref_survey_question_answer_user WHERE id_user = ?");
$completedSurveysQuery->bind_param("i", $user_id);
$completedSurveysQuery->execute();
$completedSurveysResult = $completedSurveysQuery->get_result();
$completedSurveys = [];
while ($row = $completedSurveysResult->fetch_assoc()) {
    $completedSurveys[] = $row['id_survey'];
}
$completedSurveysQuery->close();

$query = "SELECT id_survey, title, description FROM entity_survey";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Title</th><th>Description</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $completed = in_array($row['id_survey'], $completedSurveys);
        echo "<tr id='survey-{$row['id_survey']}'>";
        echo "<td class='title'>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td class='description'>" . htmlspecialchars($row['description']) . "</td>";
        echo "<td><button onclick='takeSurvey({$row['id_survey']})' " . ($completed ? "disabled" : "") . ">" . ($completed ? "Completed" : "Take Survey") . "</button></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No surveys found.";
}

$conn->close();
?>
