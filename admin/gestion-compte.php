<?php include("../includes/header.php"); ?>
<?php require_once("../includes/fonctions-auth.php"); ?>

<h2>Liste des utilisateurs</h2>

<?php
$users = lireUtilisateurs();

foreach ($users as $index => $user) {
    echo $user["username"] . " 
    <a href='supprimer-compte.php?id=$index'>Supprimer</a><br>";
}
?>

<br>
<a href="ajouter-comptes.php">Ajouter un utilisateur</a>

<?php include("../includes/footer.php"); ?>