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
        session_destroy();
        header('Location: admin.php');
        exit;
    case 'account_info':
        header('Location: accountInfo.php');
        exit;
    case 'update_account':
        header('Location: updateAccount.php');
        exit;
    case 'logout':
        header('Location: logout.php');
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
    <div class ="header"></div>
    <div class="container">
        <form id="main-menu" method ="post" target="iframe_content">
            <?php if ($is_admin): ?>
                <button type="button" onclick="submitForm('admin')">Admin Menu</button>
            <?php endif; ?>
            <button type="button" onclick="submitForm('account_info')">Account Info</button>
            <button type="button" onclick="submitForm('survey_list')">Take Survey</button>
            <button type="button" onclick="submitForm('update_account')">Update Account</button>
            <button type="button" onclick="submitForm('logout')">Logout</button>
            <input type="hidden" name="action" id="action_input">
        </form>
        <iframe name="iframe_content" frameborder="0"></iframe>
    </div>

    <script>
        function submitForm(action) {
            if (action === 'logout') {
                window.location.href = 'login.html';
            } else{
                document.getElementById("action_input").value = action;
                document.getElementById("main-menu").submit();
            }
        }
    </script>
</body>
</html>

