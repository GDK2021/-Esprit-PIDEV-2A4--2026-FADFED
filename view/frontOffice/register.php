<?php
require_once __DIR__ . '/../../control/userControl.php';
start_app_session();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — FADFED</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/navigation.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body class="registration-page">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>
    <main>
        <section class="registration-card" aria-labelledby="registration-title">
            <p class="registration-eyebrow">Bienvenue chez FADFED</p>
            <h1 id="registration-title">Créer mon compte</h1>
            <p class="registration-intro">Renseignez vos informations pour rejoindre notre communauté.</p>

            <p id="registration-message" class="registration-message" role="status" aria-live="polite" hidden></p>

            <form id="registration-form" novalidate>
                <div class="registration-fields">
                    <div class="registration-field">
                        <label for="nom">Nom</label>
                        <input id="nom" name="nom" type="text" autocomplete="family-name" minlength="2" maxlength="50" required>
                    </div>
                    <div class="registration-field">
                        <label for="prenom">Prénom</label>
                        <input id="prenom" name="prenom" type="text" autocomplete="given-name" minlength="2" maxlength="50" required>
                    </div>
                    <div class="registration-field">
                        <label for="age">Âge</label>
                        <input id="age" name="age" type="number" min="0" max="120" required>
                    </div>
                    <div class="registration-field">
                        <label for="genre">Genre</label>
                        <select id="genre" name="genre" required>
                            <option value="">Choisir une option</option>
                            <option value="Femme">Femme</option>
                            <option value="Homme">Homme</option>
                        </select>
                    </div>
                    <div class="registration-field">
                        <label for="bloodtype">Groupe sanguin</label>
                        <select id="bloodtype" name="bloodtype" required>
                            <option value="">Choisir une option</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    <div class="registration-field">
                        <label for="role">Type de compte</label>
                        <select id="role" name="role" required>
                            <option value="patient">Patient</option>
                            <option value="pro">Professionnel</option>
                        </select>
                    </div>
                    <div class="registration-field">
                        <label for="email">Adresse e-mail</label>
                        <input id="email" name="email" type="email" autocomplete="email" maxlength="100" required>
                    </div>
                    <div class="registration-field">
                        <label for="num_telephone">Téléphone (8 chiffres)</label>
                        <input id="num_telephone" name="num_telephone" type="tel" inputmode="numeric" autocomplete="tel" pattern="[0-9]{8}" maxlength="8" required>
                    </div>
                    <div class="registration-field">
                        <label for="pseudo">Pseudo</label>
                        <input id="pseudo" name="pseudo" type="text" autocomplete="nickname" minlength="3" maxlength="30" pattern="[\w.-]{3,30}" required>
                    </div>
                    <div class="registration-field">
                        <label for="login">Identifiant de connexion</label>
                        <input id="login" name="login" type="text" autocomplete="username" minlength="3" maxlength="30" pattern="[\w.-]{3,30}" required>
                    </div>
                    <div class="registration-field">
                        <label for="mdp">Mot de passe</label>
                        <input id="mdp" name="mdp" type="password" autocomplete="new-password" minlength="8" required>
                    </div>
                    <div class="registration-field">
                        <label for="mdp_confirmation">Confirmer le mot de passe</label>
                        <input id="mdp_confirmation" name="mdp_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                    </div>
                </div>
                <button class="registration-submit" type="submit">Créer mon compte</button>
            </form>

            <p class="registration-login">Déjà inscrit ? <a href="login.php">Se connecter</a></p>
        </section>
    </main>
    <script src="../assets/js/register.js"></script>
</body>
</html>
