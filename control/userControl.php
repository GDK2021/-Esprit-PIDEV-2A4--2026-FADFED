<?php

require_once __DIR__ . "/config.php";

class UserController
{
    // Returns the user's data (without the password) if the credentials
    // are correct, or null if they are not.
    public function login(string $login, string $mdp): ?array
    {
        $db = getPDO();

        $stmt = $db->prepare("SELECT * FROM user WHERE login = :login");
        $stmt->execute(["login" => $login]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($mdp, $row["mdp"])) {
            unset($row["mdp"]);
            return $row;
        }

        return null;
    }
}