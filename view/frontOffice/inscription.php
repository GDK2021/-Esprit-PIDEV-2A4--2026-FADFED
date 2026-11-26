<?php
require_once __DIR__ . '/../../control/userControl.php';
start_app_session();

ob_start();
?>

<div class="card">

    <h2>Inscription</h2>

    <!-- novalidate : supprime les bulles du navigateur, la validation est faite en JS -->
    <form action="inscription.php" method="POST" novalidate>

        <label>Rôle</label>
        <select name="role" id="role">
            <option value="">-- Choisir un rôle --</option>
            <option value="patient">Patient</option>
            <option value="medecin">Médecin (Pro)</option>
            <option value="admin">Admin</option>
        </select>

        <label>Nom</label>
        <input type="text" name="nom" placeholder="Votre nom">

        <label>Prénom</label>
        <input type="text" name="prenom" placeholder="Votre prénom">

        <label>Age</label>
        <input type="number" name="age" placeholder="Votre âge">

        <label>Genre</label>
        <select name="genre">
            <option value="">-- Choisir --</option>
            <option value="Homme">Homme</option>
            <option value="Femme">Femme</option>
        </select>

        <label>Email</label>
        <input type="email" name="email" placeholder="exemple@email.com">

        <label>Pseudo</label>
        <input type="text" name="pseudo" placeholder="Votre pseudo">

        <label>Mot de passe</label>
        <input type="password" name="mdp" placeholder="8 caractères minimum">

        <label>Confirmer le mot de passe</label>
        <input type="password" name="confirmation" placeholder="Retapez le mot de passe">

        <label>Numéro de téléphone</label>
        <input type="tel" name="num_telephone" maxlength="8" inputmode="numeric" placeholder="8 chiffres">


        <div id="patient" class="champs">

            <h3>Informations du patient</h3>

            <label>Allergies</label>
            <textarea name="allergies" placeholder="Facultatif"></textarea>

        </div>


        <div id="medecin" class="champs">

            <h3>Informations professionnelles</h3>

            <label>Spécialité</label>
            <input type="text" name="specialite" placeholder="Ex : Cardiologie">

            <label>Gouvernorat</label>
            <input type="text" name="gouvernorat" placeholder="Ex : Tunis">

            <label>Adresse du cabinet</label>
            <input type="text" name="adresse_cabinet" placeholder="Adresse complète">

            <label>Tarif</label>
            <input type="number" name="tarif" placeholder="Tarif de la consultation">

        </div>


        <div id="admin" class="champs">

            <h3>Informations administrateur</h3>

            <label>Niveau d'accès</label>
            <input type="text" name="niveau_acces" placeholder="Ex : Super admin">

        </div>


        <button type="submit">S'inscrire</button>

    </form>

</div>



<script src="../assets/js/controlesaisie.js"></script>
<?php
$pageContent = ob_get_clean();
require __DIR__ . '/../layout.php';
