<?php
require_once __DIR__ . '/../../control/session.php';

if (current_authenticated_user() === null) {
    header('Location: login.php');
    exit;
}

$csrfToken = htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8');
ob_start();
?>
<section class="profile-page">
    <header class="profile-heading">
        <p class="profile-eyebrow">Mon espace</p>
        <h1>Mon profil</h1>
        <p>Consultez et mettez à jour les informations de votre compte.</p>
    </header>

    <p id="profile-message" class="profile-message" role="status" aria-live="polite" hidden></p>

    <form id="profile-form" class="profile-card" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <section class="profile-photo-section" aria-labelledby="photo-title">
            <div class="profile-photo-preview" id="profile-photo-preview" aria-hidden="true">?</div>
            <div class="profile-photo-control">
                <h2 id="photo-title">Photo de profil</h2>
                <p>JPEG, PNG ou WebP, 2 Mo maximum.</p>
                <label class="profile-file-label" for="photo">Choisir une photo</label>
                <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp">
                <span id="photo-name" class="profile-file-name">Aucune nouvelle photo sélectionnée</span>
            </div>
        </section>

        <div class="profile-fields">
            <div class="profile-field">
                <label for="nom">Nom</label>
                <input id="nom" name="nom" type="text" minlength="2" maxlength="50" required>
            </div>
            <div class="profile-field">
                <label for="prenom">Prénom</label>
                <input id="prenom" name="prenom" type="text" minlength="2" maxlength="50" required>
            </div>
            <div class="profile-field">
                <label for="age">Âge</label>
                <input id="age" name="age" type="number" min="0" max="120" required>
            </div>
            <div class="profile-field">
                <label for="genre">Genre</label>
                <select id="genre" name="genre" required>
                    <option value="Femme">Femme</option>
                    <option value="Homme">Homme</option>
                </select>
            </div>
            <div class="profile-field">
                <label for="bloodtype">Groupe sanguin</label>
                <select id="bloodtype" name="bloodtype" required>
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
            <div class="profile-field">
                <label for="email">Adresse e-mail</label>
                <input id="email" name="email" type="email" maxlength="100" required>
            </div>
            <div class="profile-field">
                <label for="num_telephone">Téléphone (8 chiffres)</label>
                <input id="num_telephone" name="num_telephone" type="tel" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" required>
            </div>
            <div class="profile-field">
                <label for="pseudo">Pseudo affiché</label>
                <input id="pseudo" name="pseudo" type="text" minlength="3" maxlength="30" pattern="[\w.-]{3,30}" required>
            </div>
            <div class="profile-field">
                <label for="login">Identifiant de connexion</label>
                <input id="login" name="login" type="text" minlength="3" maxlength="30" pattern="[\w.-]{3,30}" required>
            </div>
            <div class="profile-field profile-readonly">
                <span>Type de compte</span>
                <output id="profile-role">—</output>
            </div>
            <div class="profile-field profile-readonly">
                <span>Date d’inscription</span>
                <output id="profile-registration-date">—</output>
            </div>
        </div>

        <div class="profile-actions">
            <button type="submit" class="profile-save-button" id="profile-save">Enregistrer les modifications</button>
        </div>
    </form>
</section>
<script src="../assets/js/profile.js" defer></script>
<?php
$pageContent = ob_get_clean();
require __DIR__ . '/../layout.php';
