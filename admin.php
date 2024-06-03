<?php
$action = $_POST['action'] ?? '';
switch ($action) {
    case 'userMgmt':
        session_destroy();
        header('Location: userMgmt.html');
        exit;
    case 'surveyMgmt':
        header('Location: surveyMgmt.php');
        exit;
    default:
        break;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Menu</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <form id="main-menu" method="post" target="iframe_content">
            <button type="button" onclick="submitForm('userMgmt')">User Management</button>
            <button type="button" onclick="submitForm('surveyMgmt')">Survey Management</button>
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
