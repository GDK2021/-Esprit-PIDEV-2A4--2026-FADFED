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

$login = trim($_POST["login"] ?? "");
$mdp   = $_POST["mdp"] ?? "";

if ($login === "" || $mdp === "") {
    echo json_encode(["success" => false, "message" => "Please enter your login and password."]);
    exit;
}

$controller = new UserController();
$user = $controller->login($login, $mdp);

if ($user === null) {
    echo json_encode(["success" => false, "message" => "Incorrect login or password."]);
    exit;
}

set_authenticated_user((int) $user["id_user"], (string) $user["role"]);

echo json_encode(["success" => true, "redirect" => "../view/index.php"]);