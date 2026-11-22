<?php
require_once __DIR__ . '/config.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, false, 'Méthode non autorisée.');
}

// ---------- récupération ----------
$nom       = trim($_POST['nom'] ?? '');
$prenom    = trim($_POST['prenom'] ?? '');
$age       = trim($_POST['age'] ?? '');
$genre     = trim($_POST['genre'] ?? '');
$bloodtype = trim($_POST['bloodtype'] ?? '');
$role      = trim($_POST['role'] ?? '');
$email     = trim($_POST['email'] ?? '');
$tel       = trim($_POST['num_telephone'] ?? '');
$pseudo    = trim($_POST['pseudo'] ?? '');
$login     = trim($_POST['login'] ?? '');
$mdp       = $_POST['mdp'] ?? '';

// ---------- validation côté serveur (ne jamais se fier au JS seul) ----------
$errors = [];
if (!preg_match("/^[\p{L}' -]{2,50}$/u", $nom))    $errors[] = 'Nom invalide.';
if (!preg_match("/^[\p{L}' -]{2,50}$/u", $prenom)) $errors[] = 'Prénom invalide.';
if (!ctype_digit($age) || (int)$age > 120)         $errors[] = 'Âge invalide.';
if (!in_array($genre, ['Homme', 'Femme'], true))   $errors[] = 'Genre invalide.';
if (!in_array($bloodtype, ['A+','A-','B+','B-','AB+','AB-','O+','O-'], true)) $errors[] = 'Groupe sanguin invalide.';
if (!in_array($role, ['patient', 'pro', 'admin'], true)) $errors[] = 'Rôle invalide.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))    $errors[] = 'Email invalide.';
if (!preg_match('/^\d{8}$/', $tel))                $errors[] = 'Téléphone invalide (8 chiffres).';
if (!preg_match('/^[\w.-]{3,30}$/', $pseudo))      $errors[] = 'Pseudo invalide.';
if (!preg_match('/^[\w.-]{3,30}$/', $login))       $errors[] = 'Login invalide.';
if (strlen($mdp) < 8)                              $errors[] = 'Mot de passe trop court (8 caractères minimum).';

if ($errors) {
    jsonResponse(422, false, implode(' ', $errors));
}

// ---------- insertion ----------
try {
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        'INSERT INTO user
            (nom, prenom, age, genre, bloodtype, role, email, pseudo, login, mdp, num_telephone, date_inscription)
         VALUES
            (:nom, :prenom, :age, :genre, :bloodtype, :role, :email, :pseudo, :login, :mdp, :tel, CURDATE())'
    );
    $stmt->execute([
        ':nom'       => $nom,
        ':prenom'    => $prenom,
        ':age'       => (int)$age,
        ':genre'     => $genre,
        ':bloodtype' => $bloodtype,
        ':role'      => $role,
        ':email'     => $email,
        ':pseudo'    => $pseudo,
        ':login'     => $login,
        ':mdp'       => password_hash($mdp, PASSWORD_DEFAULT), // mot de passe jamais stocké en clair
        ':tel'       => (int)$tel,
    ]);

    jsonResponse(201, true, 'Utilisateur ajouté avec succès (ID : ' . $pdo->lastInsertId() . ').');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') { // violation de contrainte UNIQUE
        jsonResponse(409, false, 'Email, pseudo ou login déjà utilisé.');
    }
    serverError($e, 'Erreur serveur lors de l\'ajout.');
}
