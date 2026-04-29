<?php
require_once(__DIR__ . '/../auth/session.php');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Facturation</title>
</head>
<body>

<h2>Application</h2>

<nav>
    <a href="/facturation/index.php">Accueil</a> |
    <a href="/facturation/modules/produits/liste.php">Produits</a> |
    <a href="/facturation/modules/facturation/nouvelle-facture.php">Facture</a> |
    <a href="/facturation/rapports/rapport-journalier.php">Rapport J</a> |
    <a href="/facturation/rapports/rapport-mensuel.php">Rapport M</a> |
    <a href="/facturation/admin/gestion-compte.php">Admin</a> |
    <a href="/facturation/auth/logout.php">Deconnexion</a>
</nav>

<hr>