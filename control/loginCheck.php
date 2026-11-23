<?php

session_start();
header("Content-Type: application/json");

require_once "UserController.php";

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

session_regenerate_id(true);
$_SESSION["id_user"] = $user["id_user"];
$_SESSION["role"]    = $user["role"];

// Change these pages to your real ones
switch ($user["role"]) {
    case "admin": $redirect = "../View/admin_dashboard.html"; break;
    case "pro":   $redirect = "../View/pro_dashboard.html";   break;
    default:      $redirect = "../View/home.html";
}

echo json_encode(["success" => true, "redirect" => $redirect]);