<?php
require_once __DIR__ . '/config.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, false, 'Méthode non autorisée.');
}

$id = trim($_POST['id_user'] ?? '');

if (!ctype_digit($id) || (int)$id < 1) {
    jsonResponse(422, false, 'ID invalide.');
}
$id = (int)$id;

// L'admin ne peut pas supprimer son propre compte
if ($id === (int)($_SESSION['id_user'] ?? 0)) {
    jsonResponse(403, false, 'Tu ne peux pas supprimer ton propre compte.');
}

try {
    $stmt = getPDO()->prepare('DELETE FROM user WHERE id_user = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        jsonResponse(404, false, "Aucun utilisateur avec l'ID $id.");
    }
    jsonResponse(200, true, "Utilisateur n°$id supprimé.");
} catch (PDOException $e) {
    if ($e->getCode() === '23000') { // clé étrangère : l'utilisateur est référencé ailleurs
        jsonResponse(409, false, 'Suppression impossible : cet utilisateur est lié à d\'autres données.');
    }
    serverError($e, 'Erreur serveur lors de la suppression.');
}
