<?php
require_once('connect.php'); // Connect to the db

$customAnswerId = 46; // Custom answer ID that should not be deleted

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $conn->begin_transaction();
    try {
        // Get all questions related to the survey
        $stmt = $conn->prepare("SELECT id_question FROM xref_survey_question WHERE id_survey = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Delete answers related to each question, except the custom answer
        foreach ($questions as $question) {
            $question_id = $question['id_question'];

            // Get all answers related to the question
            $stmt = $conn->prepare("SELECT id_answer FROM xref_question_answer WHERE id_question = ?");
            $stmt->bind_param("i", $question_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $answers = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Delete the xref_question_answer entries and the answers themselves, except the custom answer
            foreach ($answers as $answer) {
                $answer_id = $answer['id_answer'];

                // Delete from xref_question_answer
                $stmt = $conn->prepare("DELETE FROM xref_question_answer WHERE id_question = ? AND id_answer = ?");
                $stmt->bind_param("ii", $question_id, $answer_id);
                $stmt->execute();
                $stmt->close();

                if ($answer_id != $customAnswerId) {
                    // Delete from entity_answer
                    $stmt = $conn->prepare("DELETE FROM entity_answer WHERE id_answer = ?");
                    $stmt->bind_param("i", $answer_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            // Delete from xref_survey_question_answer_user
            $stmt = $conn->prepare("DELETE FROM xref_survey_question_answer_user WHERE id_question = ?");
            $stmt->bind_param("i", $question_id);
            $stmt->execute();
            $stmt->close();

            // Delete from xref_survey_question
            $stmt = $conn->prepare("DELETE FROM xref_survey_question WHERE id_survey = ? AND id_question = ?");
            $stmt->bind_param("ii", $id, $question_id);
            $stmt->execute();
            $stmt->close();

            // Delete from entity_question
            $stmt = $conn->prepare("DELETE FROM entity_question WHERE id_question = ?");
            $stmt->bind_param("i", $question_id);
            $stmt->execute();
            $stmt->close();
        }

        // Delete from xref_survey_question_answer_user for the survey
        $stmt = $conn->prepare("DELETE FROM xref_survey_question_answer_user WHERE id_survey = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Finally, delete the survey itself
        $stmt = $conn->prepare("DELETE FROM entity_survey WHERE id_survey = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        echo "Survey and all related data deleted successfully";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Failed to delete survey: " . $e->getMessage();
    }
} else {
    echo "Invalid request";
}

$conn->close();
?>
