<?php
require_once __DIR__ . '/../../control/userControl.php';
$authenticatedUser = current_authenticated_user();

if ($authenticatedUser !== null && $authenticatedUser['role'] === 'admin') {
    header('Location: liste.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion administrateur — FADFED</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/navigation.css">
</head>
<body>
    <header class="site-header">
        <nav class="navigation" aria-label="Navigation principale">
            <a class="wordmark" href="../frontOffice/index.php#accueil" aria-label="FADFED, accueil">FADFED<span>.</span></a>
            <div class="nav-links">
                <a href="../frontOffice/index.php#accueil">Retour au site</a>
                <a href="../frontOffice/login.php">Connexion utilisateur</a>
            </div>
        </nav>
    </header>
    <main class="card">
        <h1>Administration</h1>
        <p class="sub">Connectez-vous avec un compte administrateur pour accéder au back-office.</p>
        <p id="error" class="error" role="alert" hidden></p>
        <form id="login-form" data-login-area="backoffice" novalidate>
            <label for="login">Identifiant administrateur</label>
            <input type="text" id="login" name="login" autocomplete="username" required autofocus>
            <label for="mdp">Mot de passe</label>
            <input type="password" id="mdp" name="mdp" autocomplete="current-password" required>
            <button type="submit" class="submit">Se connecter</button>
        </form>
    </main>
    <script src="../assets/js/login.js"></script>
</body>
</html>
