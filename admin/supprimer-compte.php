<?php
require_once("../includes/fonctions-auth.php");

if (isset($_GET["id"])) {

    $users = lireUtilisateurs();

    unset($users[$_GET["id"]]);

    enregistrerUtilisateurs(array_values($users));
}

header("Location: gestion-compte.php");
exit;