<?php
require_once __DIR__ . '/config.php';

requireAdmin();

try {
    // Le mot de passe (mdp) n'est volontairement jamais renvoyé
    $users = getPDO()->query(
        'SELECT id_user, nom, prenom, age, genre, bloodtype, role, email, pseudo, login,
                num_telephone, date_inscription
         FROM user ORDER BY id_user DESC'
    )->fetchAll();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'users' => $users], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    serverError($e, 'Erreur serveur lors du chargement des utilisateurs.');
}
