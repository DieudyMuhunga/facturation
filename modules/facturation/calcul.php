<?php
include("../../includes/header.php");
require_once("../../includes/fonctions-factures.php");

$total_ht = 0;

foreach ($_SESSION['facture'] as $ligne) {
    $total_ht += $ligne['total'];
}

$tva = $total_ht * 0.18;
$total_ttc = $total_ht + $tva;

$facture = [
    "date" => date("Y-m-d"),
    "heure" => date("H:i:s"),
    "articles" => $_SESSION['facture'],
    "total_ht" => $total_ht,
    "tva" => $tva,
    "total_ttc" => $total_ttc
];

// ✅ sauvegarde via fonction
enregistrerFacture($facture);

// vider session
$_SESSION['facture'] = [];

// redirection
header("Location: afficher-facture.php");
exit;
?>