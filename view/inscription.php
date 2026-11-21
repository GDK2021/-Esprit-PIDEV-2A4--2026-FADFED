<?php
include '../controller/userC.php';

$userC = new UserC();

if ($_POST["role"] == "patient") {

    $user = new patient(
        0,
        $_POST["nom"],
        $_POST["prenom"],
        $_POST["age"],
        $_POST["genre"],
        $_POST["role"],
        $_POST["email"],
        $_POST["pseudo"],
        $_POST["mdp"],
        $_POST["num_telephone"],
        0,
        $_POST["allergies"]
    );
    $userC->inscription($user);

} elseif ($_POST["role"] == "medecin") {

    $user = new pro(
        0,
        $_POST["nom"],
        $_POST["prenom"],
        $_POST["age"],
        $_POST["genre"],
        $_POST["role"],
        $_POST["email"],
        $_POST["pseudo"],
        $_POST["mdp"],
        $_POST["num_telephone"],
        0,
        $_POST["specialite"],
        $_POST["gouvernorat"],
        $_POST["adresse_cabinet"],
        $_POST["tarif"]
    );
    $userC->inscription($user);

} elseif ($_POST["role"] == "admin") {

    $user = new admin(
        0,
        $_POST["nom"],
        $_POST["prenom"],
        $_POST["age"],
        $_POST["genre"],
        $_POST["role"],
        $_POST["email"],
        $_POST["pseudo"],
        $_POST["mdp"],
        $_POST["num_telephone"],
        0,
        $_POST["niveau_acces"]
    );
    $userC->inscription($user);
}

header('Location: inscription.html');
?>
