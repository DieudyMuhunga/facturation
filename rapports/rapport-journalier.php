<?php
include("../includes/header.php");
require_once("../includes/fonctions-factures.php");

$factures = lireFactures();
$month = date("Y-m");

foreach ($factures as $f) {
    if (substr($f['date'], 0, 7) == $month) {
        echo "<p>Total: " . $f['total_ttc'] . "</p>";
    }
}

include("../includes/footer.php");
require_once("../includes/fonctions-factures.php");

$factures = lireFactures();
$today = date("Y-m-d");

foreach ($factures as $f) {
    if ($f['date'] == $today) {
        echo "<p>Total: " . $f['total_ttc'] . "</p>";
    }
}

include("../includes/footer.php");
?>