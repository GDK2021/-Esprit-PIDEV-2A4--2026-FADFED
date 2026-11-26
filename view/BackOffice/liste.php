<?php
require_once __DIR__ . '/../../control/userControl.php';
$authenticatedUser = current_authenticated_user();

if ($authenticatedUser === null) {
    header('Location: login.php');
    exit;
}

if ($authenticatedUser['role'] !== 'admin') {
    http_response_code(403);
    exit('Accès réservé aux administrateurs.');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
    <link rel="stylesheet" href="../assets/css/liste.css">
    <link rel="stylesheet" href="../assets/css/navigation.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
<main>
    <section class="card">
        <div class="top">
            <h1>Gestion des utilisateurs</h1>
            <button type="button" id="btn-ouvrir">Ajouter un utilisateur</button>
        </div>

        <form class="bar" id="form-recherche" role="search">
            <input type="search" id="recherche-id" inputmode="numeric" placeholder="ID" aria-label="Rechercher par ID">
            <button type="submit">Rechercher</button>
            <button type="button" class="secondary" id="btn-reset">Tout afficher</button>
            <span class="count" id="compteur"></span>
        </form>

        <div class="wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Nom</th><th>Prénom</th><th>Âge</th><th>Genre</th><th>Groupe</th>
                        <th>Rôle</th><th>Email</th><th>Pseudo</th><th>Login</th><th>Téléphone</th>
                        <th>Inscription</th><th>Action</th>
                    </tr>
                </thead>
                <tbody id="corps"></tbody>
            </table>
        </div>
        <div class="empty" id="vide" hidden>Aucun utilisateur à afficher.</div>
        <div class="msg" id="msg" role="status" aria-live="polite"></div>
    </section>
</main>

<dialog id="dlg-ajout" aria-labelledby="titre-ajout">
    <h2 id="titre-ajout">Ajouter un utilisateur</h2>
    <form id="form-ajout" novalidate>
        <div class="grid">
            <div class="field">
                <label for="nom">Nom</label>
                <input id="nom" name="nom" type="text" autocomplete="off">
                <span class="error" data-for="nom"></span>
            </div>
            <div class="field">
                <label for="prenom">Prénom</label>
                <input id="prenom" name="prenom" type="text" autocomplete="off">
                <span class="error" data-for="prenom"></span>
            </div>
            <div class="field">
                <label for="age">Âge</label>
                <input id="age" name="age" type="number" min="0" max="120">
                <span class="error" data-for="age"></span>
            </div>
            <div class="field">
                <label for="genre">Genre</label>
                <select id="genre" name="genre">
                    <option value="">Choisir…</option>
                    <option value="Homme">Homme</option>
                    <option value="Femme">Femme</option>
                </select>
                <span class="error" data-for="genre"></span>
            </div>
            <div class="field">
                <label for="bloodtype">Groupe sanguin</label>
                <select id="bloodtype" name="bloodtype">
                    <option value="">Choisir…</option>
                    <option>A+</option><option>A-</option>
                    <option>B+</option><option>B-</option>
                    <option>AB+</option><option>AB-</option>
                    <option>O+</option><option>O-</option>
                </select>
                <span class="error" data-for="bloodtype"></span>
            </div>
            <div class="field">
                <label for="role">Rôle</label>
                <select id="role" name="role">
                    <option value="">Choisir…</option>
                    <option value="patient">Patient</option>
                    <option value="pro">Professionnel</option>
                    <option value="admin">Administrateur</option>
                </select>
                <span class="error" data-for="role"></span>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" autocomplete="off">
                <span class="error" data-for="email"></span>
            </div>
            <div class="field">
                <label for="num_telephone">Téléphone (8 chiffres)</label>
                <input id="num_telephone" name="num_telephone" type="tel" inputmode="numeric" maxlength="8" autocomplete="off">
                <span class="error" data-for="num_telephone"></span>
            </div>
            <div class="field">
                <label for="pseudo">Pseudo</label>
                <input id="pseudo" name="pseudo" type="text" autocomplete="off">
                <span class="error" data-for="pseudo"></span>
            </div>
            <div class="field">
                <label for="login">Login</label>
                <input id="login" name="login" type="text" autocomplete="off">
                <span class="error" data-for="login"></span>
            </div>
            <div class="field full">
                <label for="mdp">Mot de passe (8 caractères minimum)</label>
                <input id="mdp" name="mdp" type="password" autocomplete="new-password">
                <span class="error" data-for="mdp"></span>
            </div>
        </div>
        <div class="msg ko" id="msg-ajout" role="status" aria-live="polite"></div>
        <div class="actions">
            <button type="button" class="secondary" id="btn-annuler">Annuler</button>
            <button type="submit" id="btn-ajout">Ajouter l'utilisateur</button>
        </div>
    </form>
</dialog>

<script src="../assets/js/liste.js"></script>
</body>
</html>