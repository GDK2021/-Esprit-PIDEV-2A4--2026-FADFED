<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, false, 'Méthode non autorisée.');
}

function registrationPostString(string $name): string
{
    $value = $_POST[$name] ?? '';
    return is_string($value) ? trim($value) : '';
}

$nom       = registrationPostString('nom');
$prenom    = registrationPostString('prenom');
$age       = registrationPostString('age');
$genre     = registrationPostString('genre');
$bloodtype = registrationPostString('bloodtype');
$role      = registrationPostString('role');
$email     = registrationPostString('email');
$tel       = registrationPostString('num_telephone');
$pseudo    = registrationPostString('pseudo');
$login     = registrationPostString('login');
$mdp       = $_POST['mdp'] ?? '';
$mdpConfirmation = $_POST['mdp_confirmation'] ?? '';

if (!is_string($mdp)) {
    $mdp = '';
}
if (!is_string($mdpConfirmation)) {
    $mdpConfirmation = '';
}

$errors = [];
if (!preg_match("/^[\p{L}' -]{2,50}$/u", $nom)) {
    $errors[] = 'Nom invalide.';
}
if (!preg_match("/^[\p{L}' -]{2,50}$/u", $prenom)) {
    $errors[] = 'Prénom invalide.';
}
if (!ctype_digit($age) || (int) $age > 120) {
    $errors[] = 'Âge invalide.';
}
if (!in_array($genre, ['Homme', 'Femme'], true)) {
    $errors[] = 'Genre invalide.';
}
if (!in_array($bloodtype, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], true)) {
    $errors[] = 'Groupe sanguin invalide.';
}
if (!in_array($role, ['patient', 'pro'], true)) {
    $errors[] = 'Type de compte invalide.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    $errors[] = 'Adresse e-mail invalide.';
}
if (!preg_match('/^\d{8}$/', $tel)) {
    $errors[] = 'Téléphone invalide (8 chiffres).';
}
if (!preg_match('/^[\w.-]{3,30}$/', $pseudo)) {
    $errors[] = 'Pseudo invalide (3 à 30 caractères : lettres, chiffres, point, tiret ou tiret bas).';
}
if (!preg_match('/^[\w.-]{3,30}$/', $login)) {
    $errors[] = 'Identifiant invalide (3 à 30 caractères : lettres, chiffres, point, tiret ou tiret bas).';
}
if (strlen($mdp) < 8) {
    $errors[] = 'Mot de passe trop court (8 caractères minimum).';
}
if (!hash_equals($mdp, $mdpConfirmation)) {
    $errors[] = 'Les mots de passe ne correspondent pas.';
}

if ($errors) {
    jsonResponse(422, false, implode(' ', $errors));
}

try {
    $stmt = getPDO()->prepare(
        'INSERT INTO user
            (nom, prenom, age, genre, bloodtype, role, email, pseudo, login, mdp, num_telephone, date_inscription)
         VALUES
            (:nom, :prenom, :age, :genre, :bloodtype, :role, :email, :pseudo, :login, :mdp, :tel, CURDATE())'
    );
    $stmt->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':age' => (int) $age,
        ':genre' => $genre,
        ':bloodtype' => $bloodtype,
        ':role' => $role,
        ':email' => $email,
        ':pseudo' => $pseudo,
        ':login' => $login,
        ':mdp' => password_hash($mdp, PASSWORD_DEFAULT),
        ':tel' => (int) $tel,
    ]);

    jsonResponse(201, true, 'Votre compte a été créé avec succès.');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        jsonResponse(409, false, 'Cette adresse e-mail, ce pseudo ou cet identifiant est déjà utilisé.');
    }
    serverError($e, 'Erreur serveur lors de la création du compte.');
}
