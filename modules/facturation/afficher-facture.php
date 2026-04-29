<?php
include("../../includes/header.php");
require_once("../../includes/fonctions-factures.php");

$factures = lireFactures();
?>

<h2>Factures</h2>

<?php foreach ($factures as $facture): ?>

<hr>

<p>Date : <?= $facture['date'] ?></p>
<p>Heure : <?= $facture['heure'] ?></p>

<?php foreach ($facture['articles'] as $a): ?>
<p><?= $a['nom'] ?> | <?= $a['quantite'] ?> | <?= $a['total'] ?></p>
<?php endforeach; ?>

<p>Total HT : <?= $facture['total_ht'] ?></p>
<p>TVA : <?= $facture['tva'] ?></p>
<p>Total TTC : <?= $facture['total_ttc'] ?></p>

<?php endforeach; ?>

<?php include("../../includes/footer.php"); ?>