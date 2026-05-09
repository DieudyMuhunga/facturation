<?php
require_once '../../auth/session.php';
require_once '../../config/config.php';
require_once '../../includes/fonctions-produits.php';

$code_barre = isset($_GET['code_barre']) ? trim($_GET['code_barre']) : '';
$produit    = null;

if ($code_barre !== '') {
    $produits = lire_produits();
    $produit = trouver_produit_par_code($produits, $code_barre);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rechercher un produit — Facturation</title>
    <!-- ZXing pour le scanner -->
    <script src="https://unpkg.com/@zxing/library@0.19.2/umd/index.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        header {
            background: linear-gradient(90deg, #1e3a5f, #2563eb);
            color: #fff;
            padding: 0 24px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        header a { color: rgba(255,255,255,.8); text-decoration: none; font-size: 14px; }
        header a:hover { color: #fff; }

        main { max-width: 540px; margin: 36px auto; padding: 0 18px; }
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            padding: 28px;
            margin-bottom: 20px;
        }
        h2 { font-size: 16px; color: #1e3a5f; margin-bottom: 18px; font-weight: 700; }

        /* Scanner controls */
        .scanner-controls {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
            flex-wrap: wrap;
            align-items: center;
        }
        select#select-camera {
            flex: 1;
            padding: 8px 10px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 13px;
        }
        #video-scanner {
            width: 100%;
            max-height: 200px;
            border-radius: 8px;
            background: #000;
            display: none;
            margin-bottom: 12px;
            object-fit: cover;
        }

        /* Formulaire */
        label { display: block; font-size: 13px; color: #555; margin-bottom: 5px; font-weight: 600; }
        .input-row { display: flex; gap: 8px; margin-bottom: 0; }
        .input-row input { flex: 1; }
        input[type=text] {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 14px;
            transition: border-color .2s;
        }
        input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }

        /* Boutons */
        .btn {
            display: inline-block;
            padding: 9px 16px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            transition: opacity .15s;
        }
        .btn:hover { opacity: .88; }
        .btn-primaire   { background: #2563eb; color: #fff; }
        .btn-orange     { background: #ea580c; color: #fff; }
        .btn-secondaire { background: #e5e7eb; color: #374151; }

        /* Resultat */
        .result-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f2f5;
            font-size: 14px;
        }
        .result-row:last-child { border-bottom: none; }
        .result-label { color: #6b7280; font-size: 13px; }
        .result-val   { font-weight: 600; color: #1e3a5f; }
        .stock-ok  { color: #16a34a; }
        .stock-bas { color: #b45309; }
        .stock-zero{ color: #dc2626; }
        .introuvable {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px 14px;
            border-radius: 7px;
            font-size: 14px;
            margin-top: 14px;
        }
    </style>
</head>
<body>
<header>
    <strong>🔍 Rechercher un produit</strong>
    <nav>
        <a href="liste.php" style="margin-right:16px;">📦 Liste</a>
        <a href="../../index.php">← Accueil</a>
    </nav>
</header>

<main>
    <div class="card">
        <h2>Recherche par code barre</h2>

        <!-- Scanner camera -->
        <div class="scanner-controls">
            <button id="btn-scanner"      class="btn btn-orange">📷 Scanner</button>
            <button id="btn-stop-scanner" class="btn btn-secondaire" style="display:none;">⏹ Arreter</button>
            <select id="select-camera" title="Choisir la camera"></select>
        </div>
        <video id="video-scanner" playsinline></video>

        <!-- Formulaire de recherche -->
        <form method="GET" action="" id="form-recherche">
            <label for="code_barre">Code barre</label>
            <div class="input-row">
                <input type="text"
                       id="code_barre"
                       name="code_barre"
                       placeholder="Ex : 1234567890"
                       value="<?= htmlspecialchars($code_barre) ?>"
                       autocomplete="off"
                       required>
                <button type="submit" class="btn btn-primaire">Rechercher</button>
            </div>
        </form>

        <!-- Résultat -->
        <?php if ($code_barre !== ''): ?>
            <?php if ($produit !== null): ?>
                <div style="margin-top:20px;">
                    <div class="result-row">
                        <span class="result-label">Code barre</span>
                        <span class="result-val"><?= htmlspecialchars($produit['code_barre']) ?></span>
                    </div>
                    <div class="result-row">
                        <span class="result-label">Nom</span>
                        <span class="result-val"><?= htmlspecialchars($produit['nom']) ?></span>
                    </div>
                    <div class="result-row">
                        <span class="result-label">Prix unitaire</span>
                        <span class="result-val"><?= number_format($produit['prix'], 2, ',', ' ') ?> CDF</span>
                    </div>
                    <div class="result-row">
                        <span class="result-label">Stock disponible</span>
                        <?php
                            $q   = intval($produit['quantite']);
                            $cls = $q === 0 ? 'stock-zero' : ($q <= 2 ? 'stock-bas' : 'stock-ok');
                            $ico = $q === 0 ? '🔴' : ($q <= 2 ? '⚠️' : '✅');
                        ?>
                        <span class="result-val <?= $cls ?>"><?= $ico ?> <?= $q ?> unites</span>
                    </div>
                    <div class="result-row">
                        <span class="result-label">Prix TTC</span>
                        <span class="result-val"><?= number_format($produit['prix'] * (1 + TVA), 2, ',', ' ') ?> CDF</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="introuvable">❌ Aucun produit trouve pour le code : <strong><?= htmlspecialchars($code_barre) ?></strong></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<script src="../../js/scanner.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Scanner.init('video-scanner', 'code_barre', {
        btnDemarrer  : 'btn-scanner',
        btnArreter   : 'btn-stop-scanner',
        selectCamera : 'select-camera',
        autoSubmit   : true   // soumet le formulaire GET automatiquement
    });
});
</script>
</body>
</html>
