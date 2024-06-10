<?php
require_once('connect.php'); // Connect to the db

$customAnswerId = 46; // Custom answer ID that should not be deleted
$user_id = intval($_POST['id']);

$conn->begin_transaction();
try {
    // Get all responses by the user
    $stmt = $conn->prepare("SELECT id_question, id_answer FROM xref_survey_question_answer_user WHERE id_user = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $responses = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($responses as $response) {
        $question_id = $response['id_question'];
        $answer_id = $response['id_answer'];

        // Decrement the response count
        $stmt = $conn->prepare("UPDATE entity_answer SET response_count = response_count - 1 WHERE id_answer = ?");
        $stmt->bind_param("i", $answer_id);
        $stmt->execute();
        $stmt->close();

        // Check if the answer is a custom answer and if its count is now 0
        if ($answer_id != $customAnswerId) {
            $stmt = $conn->prepare("SELECT response_count, is_custom FROM entity_answer WHERE id_answer = ?");
            $stmt->bind_param("i", $answer_id);
            $stmt->execute();
            $stmt->bind_result($response_count, $is_custom);
            $stmt->fetch();
            $stmt->close();

            if ($is_custom && $response_count <= 0) {
                // Delete the custom answer if its count is 0
                $stmt = $conn->prepare("DELETE FROM entity_answer WHERE id_answer = ?");
                $stmt->bind_param("i", $answer_id);
                $stmt->execute();
                $stmt->close();

                // Delete the link between the question and the custom answer
                $stmt = $conn->prepare("DELETE FROM xref_question_answer WHERE id_question = ? AND id_answer = ?");
                $stmt->bind_param("ii", $question_id, $answer_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Delete all responses by the user from xref_survey_question_answer_user
    $stmt = $conn->prepare("DELETE FROM xref_survey_question_answer_user WHERE id_user = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Delete the user
    $stmt = $conn->prepare("DELETE FROM entity_user WHERE id_user = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    echo "User and all related data deleted successfully";
} catch (Exception $e) {
    $conn->rollback();
    echo "Failed to delete user: " . $e->getMessage();
}

$conn->close();
?>
