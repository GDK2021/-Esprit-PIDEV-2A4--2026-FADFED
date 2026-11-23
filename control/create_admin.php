<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/config.php';

$login = $argv[1] ?? 'admin';
if (!preg_match('/^[\w.-]{3,30}$/', $login)) {
    fwrite(STDERR, "Identifiant invalide (3 à 30 caractères).\n");
    exit(1);
}

try {
    $pdo = getPDO();
    $check = $pdo->prepare('SELECT role FROM user WHERE login = :login');
    $check->execute([':login' => $login]);
    if ($check->fetchColumn() !== false) {
        fwrite(STDERR, "\nCet identifiant existe déjà; aucun compte n'a été modifié.\n");
        exit(1);
    }

    fwrite(STDOUT, "Mot de passe du compte {$login}: ");
    $password = rtrim((string) fgets(STDIN), "\r\n");
    if (strlen($password) < 8) {
        fwrite(STDERR, "\nLe mot de passe doit contenir au moins 8 caractères.\n");
        exit(1);
    }

    $insert = $pdo->prepare(
        'INSERT INTO user
            (nom, prenom, age, genre, bloodtype, role, email, pseudo, login, mdp, num_telephone, date_inscription)
         VALUES
            (:nom, :prenom, 0, :genre, :bloodtype, :role, :email, :pseudo, :login, :mdp, 0, CURDATE())'
    );
    $insert->execute([
        ':nom' => 'Administrateur',
        ':prenom' => 'Test',
        ':genre' => 'Homme',
        ':bloodtype' => 'O+',
        ':role' => 'admin',
        ':email' => $login . '@fadfed.local',
        ':pseudo' => $login,
        ':login' => $login,
        ':mdp' => password_hash($password, PASSWORD_DEFAULT),
    ]);

    fwrite(STDOUT, "\nCompte administrateur créé. Vous pouvez vous connecter dans le back-office.\n");
} catch (PDOException $e) {
    fwrite(STDERR, "\nErreur lors de la création du compte administrateur: {$e->getMessage()}\n");
    exit(1);
}
