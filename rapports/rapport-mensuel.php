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
?>