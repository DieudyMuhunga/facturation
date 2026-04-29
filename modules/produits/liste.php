<?php
include("../../includes/header.php");
$fichier = __DIR__ . "/../../data/produits.json";

$data = file_get_contents($fichier);
$produits = json_decode($data, true);

if ($produits == null) {
    echo "Aucun produit disponible";
    exit;
}
?>

<h2>Liste des produits</h2>

<table border="1">
<tr>
    <th>Code barre</th>
    <th>Nom</th>
    <th>Prix</th>
    <th>Stock</th>
</tr>

<?php foreach ($produits as $produit): ?>
<tr>
    <td><?= $produit['code_barre'] ?></td>
    <td><?= $produit['nom'] ?></td>
    <td><?= $produit['prix_unitaire_ht'] ?> CDF</td>
    <td><?= $produit['quantite_stock'] ?></td>
</tr>
<?php endforeach; ?>

</table>