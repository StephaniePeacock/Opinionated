<?php
require_once('connect.php'); // Connect to the db

$customAnswerId = 46; // Custom answer ID

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update the survey
    $survey_id = $_POST['survey_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $questions = json_decode($_POST['questions'], true);

    $conn->begin_transaction();
    try {
        // Update survey
        $stmt = $conn->prepare("UPDATE entity_survey SET title = ?, description = ? WHERE id_survey = ?");
        $stmt->bind_param("ssi", $title, $description, $survey_id);
        $stmt->execute();
        $stmt->close();

        // Update questions and answers
        foreach ($questions as $question) {
            $question_id = $question['id_question'];
            // Update question
            $stmt = $conn->prepare("UPDATE entity_question SET question_text = ? WHERE id_question = ?");
            $stmt->bind_param("si", $question['text'], $question_id);
            $stmt->execute();
            $stmt->close();

            // Fetch existing answers for the question
            $stmt = $conn->prepare("SELECT id_answer FROM xref_question_answer WHERE id_question = ?");
            $stmt->bind_param("i", $question_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $existingAnswers = [];
            while ($row = $result->fetch_assoc()) {
                $existingAnswers[] = $row['id_answer'];
            }
            $stmt->close();

            // Update or insert answers
            foreach ($question['answers'] as $answer) {
                if (in_array($answer['id_answer'], $existingAnswers)) {
                    // Update existing answer
                    $stmt = $conn->prepare("UPDATE entity_answer SET answer_text = ?, is_custom = ? WHERE id_answer = ?");
                    $stmt->bind_param("sii", $answer['text'], $answer['is_custom'], $answer['id_answer']);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    if ($answer['is_custom'] && $answer['id_answer'] == $customAnswerId) {
                        // Link existing custom answer
                        $stmt = $conn->prepare("INSERT INTO xref_question_answer (id_question, id_answer) VALUES (?, ?)");
                        $stmt->bind_param("ii", $question_id, $customAnswerId);
                    } else {
                        // Insert new answer
                        $stmt = $conn->prepare("INSERT INTO entity_answer (answer_text, is_custom, response_count) VALUES (?, ?, ?)");
                        $stmt->bind_param("sii", $answer['text'], $answer['is_custom'], $answer['response_count']);
                        $stmt->execute();
                        $answer_id = $stmt->insert_id;
                        $stmt->close();

                        // Link answer to question
                        $stmt = $conn->prepare("INSERT INTO xref_question_answer (id_question, id_answer) VALUES (?, ?)");
                        $stmt->bind_param("ii", $question_id, $answer_id);
                    }
                    $stmt->execute();
                    $stmt->close();
                }
            }

            // Delete answers that were removed
            foreach ($existingAnswers as $existingAnswer) {
                if (!in_array($existingAnswer, array_column($question['answers'], 'id_answer')) && $existingAnswer != $customAnswerId) {
                    // Unlink answer from question
                    $stmt = $conn->prepare("DELETE FROM xref_question_answer WHERE id_question = ? AND id_answer = ?");
                    $stmt->bind_param("ii", $question_id, $existingAnswer);
                    $stmt->execute();
                    $stmt->close();

                    // Delete answer
                    $stmt = $conn->prepare("DELETE FROM entity_answer WHERE id_answer = ?");
                    $stmt->bind_param("i", $existingAnswer);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }

        $conn->commit();
        echo "Survey updated successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Failed to update survey: " . $e->getMessage();
    }

    $conn->close();
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    // Fetch the survey data for editing
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
        WHERE sq.id_survey = ? AND (a.id_answer = ? OR a.is_custom = 0) ");
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
}
?>
