<?php

require_once __DIR__ . "/session.php";
start_app_session();
header("Content-Type: application/json");

require_once __DIR__ . "/userControl.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false]);
    exit;
}

$login = $_POST["login"] ?? "";
$mdp   = $_POST["mdp"] ?? "";
$area  = $_POST["area"] ?? "frontoffice";

if (!is_string($login) || !is_string($mdp) || !is_string($area)) {
    jsonResponse(400, false, "Données de connexion invalides.");
}

$login = trim($login);
if (!in_array($area, ["frontoffice", "backoffice"], true)) {
    jsonResponse(400, false, "Type de connexion invalide.");
}

if ($login === "" || $mdp === "") {
    echo json_encode(["success" => false, "message" => "Veuillez saisir votre identifiant et votre mot de passe."], JSON_UNESCAPED_UNICODE);
    exit;
}

$controller = new UserController();
$user = $controller->login($login, $mdp);

if ($user === null) {
    echo json_encode(["success" => false, "message" => "Identifiant ou mot de passe incorrect."], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($area === "backoffice") !== ($user["role"] === "admin")) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => $area === "backoffice"
            ? "Ce compte n'est pas un compte administrateur."
            : "Les administrateurs doivent utiliser la connexion du back-office.",
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

set_authenticated_user((int) $user["id_user"], (string) $user["role"]);

echo json_encode([
    "success" => true,
    "redirect" => $area === "backoffice" ? "liste.php" : "index.php",
]);