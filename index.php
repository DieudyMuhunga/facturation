<?php
    include("includes/header.php");
?>

<h2>Tableau de bord</h2>

<p>Bienvenue sur l'application de gestion.</p>

<ul>
    <li><a href="modules/produits/enregistrer.php">➕ Ajouter un produit</a></li>
    <li><a href="modules/produits/liste.php">📋 Liste des produits</a></li>
    <li><a href="modules/produits/lire.php">Scanner un produit</a></li>
</ul>

    <hr>

<ul>
    <li><a href="modules/facturation/nouvelle-facture.php">🧾 Nouvelle facture</a></li>
    <li><a href="modules/facturation/afficher-facture.php">📄 Voir les factures</a></li>
</ul>

    <hr>

<ul>
    <li><a href="rapports/rapport-journalier.php">📊 Rapport journalier</a></li>
    <li><a href="rapports/rapport-mensuel.php">📈 Rapport mensuel</a></li>
</ul>

    <hr>

<ul>
    <li><a href="admin/gestion-compte.php">👤 Gestion des comptes</a></li>
</ul>

<?php include("includes/footer.php"); ?>
