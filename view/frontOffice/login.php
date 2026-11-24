<?php
require_once __DIR__ . '/../../control/session.php';
start_app_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/navigation.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
    <main class="card">
        <h1>Log in</h1>
        <p class="sub">Enter your details to access your account.</p>

        <p id="error" class="error" role="alert" hidden></p>

        <form id="login-form" data-login-area="frontoffice" novalidate>
            <label for="login">Name</label>
            <input type="text" id="login" name="login" autocomplete="username" autofocus>

            <label for="mdp">Password</label>
            <input type="password" id="mdp" name="mdp" autocomplete="current-password">

            <a class="forgot" href="forgot_password.html">Forgot your password?</a>

            <button type="submit" class="submit">Log in</button>
        </form>

        <p class="signup">No account yet? <a href="register.php">Sign up</a></p>
        <p class="signup"><a href="../BackOffice/login.php">Connexion administrateur</a></p>
    </main>

    <script src="../assets/js/login.js"></script>
</body>
</html>