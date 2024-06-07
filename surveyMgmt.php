<?php
$action = $_POST['action'] ?? '';
switch ($action) {
    case 'list':
        session_destroy();
        header('Location: surveylist.html');
        exit;
    case 'add':
        header('Location: create_survey.html');
        exit;
    case 'results':
        header('Location: results.html');
    default:
        break;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Survey Management</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <form id="main-menu" method="post" target="iframe_content">
            <button type="button" onclick="submitForm('list')">View & Modify</button>
            <button type="button" onclick="submitForm('add')">Add Survey</button>
            <button type="button" onclick="submitForm('results')">View Results</button>
            <input type="hidden" name="action" id="action_input">
        </form>
    </div>
    <script>
    function submitForm(action) {
        document.getElementById("action_input").value = action;
        document.getElementById("main-menu").submit();
    }
    </script>
</body>
</html>
