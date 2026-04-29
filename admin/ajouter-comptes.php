<?php
include("../includes/header.php");
require_once("../includes/fonctions-auth.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $users = lireUtilisateurs();

    $users[] = [
        "username" => $username,
        "password" => $password
    ];

    enregistrerUtilisateurs($users);

    echo "Utilisateur ajouté";
}
?>

<h2>Ajouter un compte</h2>

<form method="POST">
    Non d'utilisateur : <input type="text" name="username"><br><br>
    Mot de passe : <input type="password" name="password"><br><br>
    <button>Ajouter</button>
</form>

<?php include("../includes/footer.php"); ?>