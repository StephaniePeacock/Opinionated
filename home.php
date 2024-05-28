<?php
require_once('include/connect.php'); // Connect to the db.

function getUser($email, $db) {
    $query = "SELECT `id_user` AS `USER_UID`, `email` AS `EMAIL`, `password` AS `PASSWORD`, `admin` AS `IS_ADMIN`, `rank`, `surveys` FROM `entity_users` WHERE `email` = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}
if(isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $userData = getUser($email,$conn);
} else {
    $userData['IS_ADMIN'] = false;
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
            <form id="main-menu" method ="post" target="content-frame">
                <?php if ($userData["IS_ADMIN"]): ?>
                    <button type="button" onclick="submitForm('admin')">Admin Menu</button>
                <?php endif; ?>
                <button type="button" onclick="submitForm('accountInfo')">Account Info</button>
                <button type="button" onclick="submitForm('surveyList')">Take Survey</button>
                <button type="button" onclick="submitForm('updateAccount')">Update Account</button>
                <button type="button" onclick="submitForm('logout')">Logout</button>
            </form>
            <iframe name="iframe_content" frameborder="0"></iframe>
        </div>
    </body>
</html>
