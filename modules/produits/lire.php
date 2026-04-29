<?php
require_once("../../includes/header.php");

$fichier = "../../data/produits.json";

$produitExiste = false;
$code = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $code = $_POST["code_barre"] ?? "";

    $data = file_get_contents($fichier);
    $produits = json_decode($data, true) ?? [];

    foreach ($produits as $p) {
        if ($p["code_barre"] == $code) {
            $produitExiste = true;
            break;
        }
    }
}
?>

<h2>Scanner un code-barres</h2>

<div id="reader" style="width:300px;"></div>
<button onclick="startScanner()">Scanner</button>

<br><br>

<form method="POST" id="form_scan">
    Code barre :
    <input type="text" name="code_barre" id="code_barre" required>
    <button type="submit">Rechercher</button>
</form>

<hr>

<?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>

    <?php if ($produitExiste): ?>
        <p style="color:red;">❌ Produit déjà existant</p>
    <?php else: ?>
        <h3>✔ Ajouter nouveau produit</h3>

        <form method="POST" action="enregistrer.php">
            <input type="hidden" name="code_barre" value="<?= htmlspecialchars($code) ?>">

            Nom : <input type="text" name="nom" required><br><br>
            Prix : <input type="number" name="prix" required><br><br>
            Stock : <input type="number" name="stock" required><br><br>

            <button type="submit">Enregistrer</button>
        </form>
    <?php endif; ?>

<?php endif; ?>

<!-- 🔥 LIBRAIRIE STABLE -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
let scanner;

function startScanner() {

    if (scanner) {
        scanner.stop().catch(() => {});
    }

    scanner = new Html5Qrcode("reader");

    const config = {
        fps: 10,
        qrbox: 250,

        // 🚨 UNIQUEMENT CODE-BARRES
        formatsToSupport: [
            Html5QrcodeSupportedFormats.EAN_13,
            Html5QrcodeSupportedFormats.EAN_8,
            Html5QrcodeSupportedFormats.UPC_A,
            Html5QrcodeSupportedFormats.UPC_E,
            Html5QrcodeSupportedFormats.CODE_128
        ]
    };

    scanner.start(
        { facingMode: "environment" },
        config,
        (decodedText) => {

            document.getElementById("code_barre").value = decodedText;

            scanner.stop().then(() => {
                document.getElementById("form_scan").submit();
            });

        },
        (error) => {
            // ignore erreurs normales
        }
    ).catch(err => {
        alert("Erreur caméra: " + err);
    });
}
</script>