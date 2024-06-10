<?php
session_start();
if (!isset($_SESSION['user_data'])) {
    header("Location: login.html");
    exit();
}
$userData = $_SESSION['user_data'];
$is_admin = $userData['IS_ADMIN'];

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'admin':
        header('Location: admin.php');
        exit;
    case 'account_info':
        header('Location: account.html');
        exit;
    case 'survey_list':
        header('Location: surveysList.html');
        exit;
    default:
        break;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Opinionated - Home</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <div class="container">
        <div class="header">
            <img src="include/logo.png" alt="Opinionated Logo" class="banner">
        </div>
        <br><br><br>
        <form id="main-menu" method ="post" target="iframe_content">
            <?php if ($is_admin): ?>
                <button type="button" onclick="submitForm('admin')">Admin Menu</button>
            <?php endif; ?>
            <button type="button" onclick="submitForm('account_info')">Account Info</button>
            <button type="button" onclick="submitForm('survey_list')">Take Survey</button>
            <button type="button" onclick="logout()">Logout</button>
            <input type="hidden" name="action" id="action_input">
        </form>
        <iframe name="iframe_content" frameborder="0"></iframe>
    </div>

    <script>
        function submitForm(action) {
            document.getElementById("action_input").value = action;
            document.getElementById("main-menu").submit();
        }

        function logout() {
            // Submit the form to logout.php
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'include/logout.php';
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>

