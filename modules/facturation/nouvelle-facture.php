<?php
include("../../includes/header.php");
require_once('../../includes/fonctions-factures.php');

if (!isset($_SESSION['facture'])) {
    $_SESSION['facture'] = [];
}


// 🔴 SUPPRIMER PRODUIT
if (isset($_POST['supprimer'])) {
    $index = $_POST['supprimer'];
    unset($_SESSION['facture'][$index]);
    $_SESSION['facture'] = array_values($_SESSION['facture']);
}

// 🟢 AJOUT PRODUIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {

    $code = $_POST['code_barre'] ?? '';
    $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 0;

    if (!empty($code) && $quantite > 0) {

        $data = file_get_contents($fichier);
        $produits = json_decode($data, true);

        foreach ($produits as $index => $produit) {

            if ($produit['code_barre'] == $code) {

                if ($produit['quantite_stock'] < $quantite) {
                    echo "Stock insuffisant<br>";
                    break;
                }

                $prix = $produit['prix_unitaire_ht'];
                $total = $prix * $quantite;

                $_SESSION['facture'][] = [
                    "nom" => $produit['nom'],
                    "prix" => $prix,
                    "quantite" => $quantite,
                    "total" => $total
                ];

                $produits[$index]['quantite_stock'] -= $quantite;

                file_put_contents($fichier, json_encode($produits, JSON_PRETTY_PRINT));

                break;
            }
        }
    }
}

// 🔵 VALIDER FACTURE
if (isset($_POST['valider'])) {

    $data = file_get_contents($fichier_factures);
    $factures = json_decode($data, true);

    if ($factures == null) {
        $factures = [];
    }

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

    $factures[] = $facture;

    file_put_contents($fichier_factures, json_encode($factures, JSON_PRETTY_PRINT));

    $_SESSION['facture'] = [];

    echo "<b>Facture enregistrée avec succès</b><br><br>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Facturation</title>
</head>
<script src="https://unpkg.com/html5-qrcode"></script>

<div id="reader" style="width:300px"></div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/minified/html5-qrcode.min.js"></script>
<script>
function startScanner() {
    const html5QrCode = new Html5Qrcode("reader");

    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: 250 },
        function(decodedText) {
            document.querySelector('input[name="code_barre"]').value = decodedText;
            html5QrCode.stop();
        }
    ).catch(err => console.log(err));
}
</script>

<button type="button" onclick="startScanner()">Scanner</button>
<body>

<h2>Ajouter un produit</h2>

<form method="POST" action="calcul.php">
    Code barre : <input type="text" name="code_barre"><br><br>
    Quantité : <input type="number" name="quantite"><br><br>
    <button type="submit" name="ajouter">Ajouter</button>
</form>

<hr>

<?php if (!empty($_SESSION['facture'])): ?>

<h2>Facture</h2>

<table border="1">
<tr>
    <th>Produit</th>
    <th>Prix</th>
    <th>Quantité</th>
    <th>Total</th>
    <th>Action</th>
</tr>

<?php
$total_ht = 0;

foreach ($_SESSION['facture'] as $index => $ligne):
    $total_ht += $ligne['total'];
?>
<tr>
    <td><?= $ligne['nom'] ?></td>
    <td><?= $ligne['prix'] ?></td>
    <td><?= $ligne['quantite'] ?></td>
    <td><?= $ligne['total'] ?></td>
    <td>
        <form method="POST">
            <input type="hidden" name="supprimer" value="<?= $index ?>">
            <button>❌</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>

</table>

<?php
$tva = $total_ht * 0.18;
$total_ttc = $total_ht + $tva;
?>

<h3>Total HT : <?= $total_ht ?> CDF</h3>
<h3>TVA : <?= $tva ?> CDF</h3>
<h3>Total TTC : <?= $total_ttc ?> CDF</h3>

<div id="scanner" style="width:300px;"></div>

<form method="POST">
    <button name="valider">Valider la facture</button>
</form>

<?php endif; ?>

<br>
<a href="../produits/liste.php">📄 Voir les factures</a>

<?php include("../../includes/footer.php");?>
</body>
</html>
