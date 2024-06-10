<?php
require_once('connect.php'); // Connect to the db

$customAnswerId = 46; // Custom answer ID

header('Content-Type: application/json'); // Ensure the content type is JSON

// Check if user_id is set in the session, if not try to get it from the cookie
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_COOKIE['user_data'])) {
    $user_data = json_decode($_COOKIE['user_data'], true);
    if (isset($user_data['USER_UID'])) {
        $user_id = $user_data['USER_UID'];
    } else {
        echo json_encode(["success" => false, "message" => "User not authenticated"]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "User not authenticated"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $survey_id = $_POST['survey_id'];
    $responses = json_decode($_POST['responses'], true);

    $conn->begin_transaction();
    try {
        foreach ($responses as $response) {
            $question_id = $response['question_id'];
            $answer_id = $response['answer_id'];
            $custom_text = $response['custom_text'];

            if ($answer_id == $customAnswerId && $custom_text) {
                // Check if the custom answer already exists for this question
                $stmt = $conn->prepare("SELECT a.id_answer FROM entity_answer a 
                                        JOIN xref_question_answer qa ON a.id_answer = qa.id_answer 
                                        WHERE qa.id_question = ? AND a.answer_text = ? AND a.is_custom = 1");
                $stmt->bind_param("is", $question_id, $custom_text);
                $stmt->execute();
                $stmt->bind_result($existing_answer_id);
                $stmt->fetch();
                $stmt->close();

                if ($existing_answer_id) {
                    // Increment response count for existing custom answer
                    $stmt = $conn->prepare("UPDATE entity_answer SET response_count = response_count + 1 WHERE id_answer = ?");
                    $stmt->bind_param("i", $existing_answer_id);
                    $stmt->execute();
                    $stmt->close();

                    $answer_id = $existing_answer_id;
                } else {
                    // Insert new custom answer
                    $stmt = $conn->prepare("INSERT INTO entity_answer (answer_text, is_custom, response_count) VALUES (?, 1, 1)");
                    $stmt->bind_param("s", $custom_text);
                    $stmt->execute();
                    $answer_id = $stmt->insert_id;
                    $stmt->close();

                    // Link new custom answer to question
                    $stmt = $conn->prepare("INSERT INTO xref_question_answer (id_question, id_answer) VALUES (?, ?)");
                    $stmt->bind_param("ii", $question_id, $answer_id);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                // Increment response count for predefined answer
                $stmt = $conn->prepare("UPDATE entity_answer SET response_count = response_count + 1 WHERE id_answer = ?");
                $stmt->bind_param("i", $answer_id);
                $stmt->execute();
                $stmt->close();
            }

            // Record the user's response
            $stmt = $conn->prepare("INSERT INTO xref_survey_question_answer_user (id_user, id_survey, id_question, id_answer) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiii", $user_id, $survey_id, $question_id, $answer_id);
            $stmt->execute();
            $stmt->close();
        }

        // Increment the user's survey count
        $stmt = $conn->prepare("UPDATE entity_user SET surveys = surveys + 1 WHERE id_user = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // Check for rank up
        $stmt = $conn->prepare("SELECT surveys FROM entity_user WHERE id_user = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($surveys);
        $stmt->fetch();
        $stmt->close();

        $new_rank = intdiv($surveys, 5);
        $stmt = $conn->prepare("UPDATE entity_user SET rank = ? WHERE id_user = ?");
        $stmt->bind_param("ii", $new_rank, $user_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        echo json_encode(["success" => true, "message" => "Survey submitted successfully!"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Failed to submit survey: " . $e->getMessage()]);
    }

    $conn->close();
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $survey_id = intval($_GET['id']);

    // Fetch survey data
    $query = $conn->prepare("SELECT title, description FROM entity_survey WHERE id_survey = ?");
    $query->bind_param("i", $survey_id);
    $query->execute();
    $query->bind_result($title, $description);
    $query->fetch();
    $query->close();

    // Fetch questions and answers
    $questions_query = $conn->prepare("
        SELECT q.id_question, q.question_text, a.id_answer, a.answer_text, a.is_custom 
        FROM entity_question q
        JOIN xref_survey_question sq ON q.id_question = sq.id_question
        LEFT JOIN xref_question_answer qa ON q.id_question = qa.id_question
        LEFT JOIN entity_answer a ON qa.id_answer = a.id_answer
        WHERE sq.id_survey = ? AND (a.id_answer = ? OR a.is_custom = 0)");
    $questions_query->bind_param("ii", $survey_id, $customAnswerId);
    $questions_query->execute();
    $questions_result = $questions_query->get_result();

    $questions = [];
    while ($row = $questions_result->fetch_assoc()) {
        $question_id = $row['id_question'];
        if (!isset($questions[$question_id])) {
            $questions[$question_id] = [
                'id_question' => $question_id,
                'text' => $row['question_text'],
                'answers' => [] ];
        }
        $questions[$question_id]['answers'][] = [
            'id_answer' => $row['id_answer'],
            'text' => $row['answer_text'],
            'is_custom' => $row['is_custom'] ];
    }
    $questions_query->close();

    echo json_encode([
        'survey_id' => $survey_id,
        'title' => $title,
        'description' => $description,
        'questions' => array_values($questions) ]);

    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
