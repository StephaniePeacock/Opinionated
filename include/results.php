<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('connect.php'); // Connect to the db

$customAnswerId = 46;

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $survey_id = intval($_GET['id']);

    // Fetch survey data
    $query = $conn->prepare("SELECT title, description FROM entity_survey WHERE id_survey = ?");
    if (!$query) {
        echo json_encode(["error" => true, "message" => "Error preparing survey query"]);
        exit();
    }
    $query->bind_param("i", $survey_id);
    if (!$query->execute()) {
        echo json_encode(["error" => true, "message" => "Error executing survey query"]);
        exit();
    }
    $query->bind_result($title, $description);
    $query->fetch();
    $query->close();

    // Fetch questions and answers
    $questions_query = $conn->prepare("
        SELECT q.id_question, q.question_text, a.id_answer, a.answer_text, a.is_custom, a.response_count 
        FROM entity_question q
        JOIN xref_survey_question sq ON q.id_question = sq.id_question
        LEFT JOIN xref_question_answer qa ON q.id_question = qa.id_question
        LEFT JOIN entity_answer a ON qa.id_answer = a.id_answer
        WHERE sq.id_survey = ?
    ");
    if (!$questions_query) {
        echo json_encode(["error" => true, "message" => "Error preparing questions query"]);
        exit();
    }
    $questions_query->bind_param("i", $survey_id);
    if (!$questions_query->execute()) {
        echo json_encode(["error" => true, "message" => "Error executing questions query"]);
        exit();
    }
    $questions_result = $questions_query->get_result();

    $questions = [];
    while ($row = $questions_result->fetch_assoc()) {
        $question_id = $row['id_question'];
        if (!isset($questions[$question_id])) {
            $questions[$question_id] = [
                'id_question' => $question_id,
                'text' => $row['question_text'],
                'predefinedAnswers' => [],
                'customAnswers' => []
            ];
        }
        $answer = [
            'text' => $row['answer_text'],
            'count' => $row['response_count']
        ];
        if ($row['is_custom'] && $row['id_answer'] != $customAnswerId) {
            $questions[$question_id]['customAnswers'][] = $answer;
        } else {
            $questions[$question_id]['predefinedAnswers'][] = $answer;
        }
    }
    $questions_query->close();

    echo json_encode([
        'survey' => [
            'title' => $title,
            'description' => $description
        ],
        'questions' => array_values($questions)
    ]);

    $conn->close();
} else {
    echo json_encode(["error" => true, "message" => "Invalid request"]);
}
?>
