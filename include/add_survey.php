<?php
require_once('connect.php'); // Connect to the db.

$customAnswerId = 46; // Custom answer

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $questions = json_decode($_POST['questions'], true);

    // Validate inputs
    if (empty($title)) {
        echo json_encode(["success" => false, "message" => "Title is required."]);
        exit;
    }

    if (empty($description)) {
        echo json_encode(["success" => false, "message" => "Description is required."]);
        exit;
    }

    if (empty($questions)) {
        echo json_encode(["success" => false, "message" => "At least one question is required."]);
        exit;
    }

    foreach ($questions as $question) {
        if (empty($question['answers'])) {
            echo json_encode(["success" => false, "message" => "Each question must have at least one answer."]);
            exit;
        }
    }

    $conn->begin_transaction();
    try {
        // Insert survey
        $stmt = $conn->prepare("INSERT INTO entity_survey (title, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $description);
        $stmt->execute();
        $survey_id = $stmt->insert_id;
        $stmt->close();

        foreach ($questions as $question) {
            // Insert question
            $stmt = $conn->prepare("INSERT INTO entity_question (question_text) VALUES (?)");
            $stmt->bind_param("s", $question['text']);
            $stmt->execute();
            $question_id = $stmt->insert_id;
            $stmt->close();

            // Link question to survey
            $stmt = $conn->prepare("INSERT INTO xref_survey_question (id_survey, id_question) VALUES (?, ?)");
            $stmt->bind_param("ii", $survey_id, $question_id);
            $stmt->execute();
            $stmt->close();

            foreach ($question['answers'] as $answer) {
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

        $conn->commit();
        echo json_encode(["success" => true, "message" => "Survey created successfully!"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Failed to create survey: " . $e->getMessage()]);
    }
}

$conn->close();
?>
