<?php

include __DIR__ . '/../controller/userC.php';

function inscription()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: inscription.html');
        exit;
    }

    // Récupération 
    $role = $_POST['role'] ?? '';
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $age = (int) ($_POST['age'] ?? 0);
    $genre = $_POST['genre'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $pseudo = trim($_POST['pseudo'] ?? '');
    $mdp = $_POST['mdp'] ?? '';
    $telephone = trim($_POST['num_telephone'] ?? '');
    $confirmation = $_POST['confirmation'] ?? '';

    // Vérif des champs communs
    if (
        $nom == '' || $prenom == '' || $email == '' ||
        $pseudo == '' || $mdp == '' || $telephone == ''
    ) {
        return "Veuillez remplir tous les champs obligatoires.";
    }

    if (!in_array($role, ['patient', 'medecin', 'admin'], true)) {
        return "Rôle invalide.";
    }

    if (!preg_match("/^[\p{L}\s'-]+$/u", $nom)) {
        return "Le nom ne doit contenir que des lettres.";
    }

    if (!preg_match("/^[\p{L}\s'-]+$/u", $prenom)) {
        return "Le prénom ne doit contenir que des lettres.";
    }

    if ($age < 1 || $age > 120) {
        return "Age invalide (entre 1 et 120).";
    }

    if (!in_array($genre, ['Homme', 'Femme'], true)) {
        return "Genre invalide.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Email invalide.";
    }

    if (strlen($pseudo) < 3) {
        return "Le pseudo doit contenir au moins 3 caractères.";
    }

    // Même règle que le formulaire : 8 caractères, avec lettres et chiffres
    if (strlen($mdp) < 8 || !preg_match('/[A-Za-z]/', $mdp) || !preg_match('/[0-9]/', $mdp)) {
        return "Le mot de passe doit contenir au moins 8 caractères, avec des lettres et des chiffres.";
    }

    if ($mdp !== $confirmation) {
        return "Les mots de passe ne correspondent pas.";
    }

    if (!preg_match('/^[0-9]{8}$/', $telephone)) {
        return "Le numéro de téléphone doit contenir 8 chiffres.";
    }

    // Info communes
    $id = 0;
    $date = date('Y-m-d H:i:s');

  
    $telephone = (int) $telephone;

    if ($role == 'patient') {

        $allergies = trim($_POST['allergies'] ?? '');

        $user = new patient(
            $id,
            $nom,
            $prenom,
            $age,
            $genre,
            $role,
            $email,
            $pseudo,
            $mdp,
            $telephone,
            $date,
            0,
            $allergies
        );
    }

    
    elseif ($role == 'medecin') {

        $specialite = trim($_POST['specialite'] ?? '');
        $gouvernorat = trim($_POST['gouvernorat'] ?? '');
        $adresse = trim($_POST['adresse_cabinet'] ?? '');
        $tarif = (int) ($_POST['tarif'] ?? 0);

        if (
            $specialite == '' || $gouvernorat == '' ||
            $adresse == '' || $tarif <= 0
        ) {
            return "Veuillez remplir les informations professionnelles (tarif supérieur à 0).";
        }

        $user = new pro(
            $id,
            $nom,
            $prenom,
            $age,
            $genre,
            $role,
            $email,
            $pseudo,
            $mdp,
            $telephone,
            $date,
            0,
            $specialite,
            $gouvernorat,
            $adresse,
            $tarif
        );
    }

    // Création d'un admin
    else {

        $niveau = trim($_POST['niveau_acces'] ?? '');

        if ($niveau == '') {
            return "Veuillez saisir le niveau d'accès.";
        }

        $user = new admin(
            $id,
            $nom,
            $prenom,
            $age,
            $genre,
            $role,
            $email,
            $pseudo,
            $mdp,
            $telephone,
            $date,
            0,
            $niveau
        );
    }

    $userC = new UserC();

    return $userC->inscription($user);
}

$resultat = inscription();

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Résultat de l'inscription</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="card resultat">

    <?php if ($resultat === true): ?>

        <h2 class="ok">Inscription réussie !</h2>
        <p>Votre compte a été créé.</p>

    <?php else: ?>

        <h2 class="ko">Échec de l'inscription</h2>
        <p><?= htmlspecialchars((string) $resultat) ?></p>
        <a href="inscription.html">Retour au formulaire</a>

    <?php endif; ?>

</div>

</body>
</html>
