<?php
// Connexion PDO partagée par ajout.php et sup.php
// Adapte ces 4 valeurs à ta base de données
const DB_HOST = 'localhost';
const DB_NAME = 'web_app';
const DB_USER = 'root';
const DB_PASS = '';

// true pendant le développement : affiche la vraie erreur MySQL dans la page.
// Mets false avant la mise en ligne.
const DEBUG = true;

function getPDO(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}

// Réponse JSON + arrêt du script
function jsonResponse(int $status, bool $success, string $message): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// Seul un admin connecté peut appeler ajout.php / sup.php
// (suppose que la page de login fait : $_SESSION['role'] = 'admin'; $_SESSION['id_user'] = ...)
function requireAdmin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (($_SESSION['role'] ?? '') !== 'admin') {
        jsonResponse(403, false, "Accès refusé : réservé à l'administrateur.");
    }
}

// Erreur serveur : logge toujours, et montre le détail MySQL si DEBUG est actif
function serverError(PDOException $e, string $message): never
{
    error_log($e->getMessage());
    jsonResponse(500, false, DEBUG ? $message . ' [' . $e->getMessage() . ']' : $message);
}
