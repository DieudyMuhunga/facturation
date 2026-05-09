<?php
require_once '../../auth/session.php';
require_once '../../config/config.php';
require_once '../../includes/fonctions-produits.php';
require_once 'calcul.php';

if (!isset($_SESSION['facture']) || !is_array($_SESSION['facture'])) {
    $_SESSION['facture'] = [];
}

$erreur = '';

// Vider la facture
if (isset($_GET['action']) && $_GET['action'] === 'vider') {
    $_SESSION['facture'] = [];
    header('Location: nouvelle-facture.php');
    exit;
}

// Retirer une ligne
if (isset($_GET['action']) && $_GET['action'] === 'retirer' && isset($_GET['index'])) {
    $index = intval($_GET['index']);
    if (isset($_SESSION['facture'][$index])) {
        array_splice($_SESSION['facture'], $index, 1);
    }
    header('Location: nouvelle-facture.php');
    exit;
}

// Ajouter un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_barre = isset($_POST['code_barre']) ? trim($_POST['code_barre']) : '';
    $quantite   = isset($_POST['quantite']) ? trim($_POST['quantite']) : '';

    if ($code_barre === '' || $quantite === '') {
        $erreur = 'Veuillez renseigner le code barre et la quantite.';
    } elseif (!ctype_digit($quantite) || intval($quantite) <= 0) {
        $erreur = 'La quantite doit etre un entier superieur a 0.';
    } else {
        $produits = lire_produits();
        $produit = trouver_produit_par_code($produits, $code_barre);

        if ($produit === null) {
            $erreur = 'Produit introuvable (code : ' . htmlspecialchars($code_barre) . ').';
        } elseif ($produit['quantite'] < intval($quantite)) {
            $erreur = 'Stock insuffisant. Disponible : ' . $produit['quantite'] . '.';
        } else {
            $deja = false;
            foreach ($_SESSION['facture'] as &$ligne) {
                if ($ligne['code_barre'] === $code_barre) {
                    $nv_qte = $ligne['quantite'] + intval($quantite);
                    if ($produit['quantite'] < $nv_qte) {
                        $erreur = 'Stock insuffisant pour cette quantite totale. Disponible : ' . $produit['quantite'] . '.';
                    } else {
                        $ligne['quantite'] = $nv_qte;
                    }
                    $deja = true;
                    break;
                }
            }
            unset($ligne);

            if (!$deja && $erreur === '') {
                $_SESSION['facture'][] = [
                    'code_barre'    => $produit['code_barre'],
                    'nom'           => $produit['nom'],
                    'prix_unitaire' => $produit['prix'],
                    'quantite'      => intval($quantite),
                ];
            }
        }
    }
}

$lignes = $_SESSION['facture'];
$totaux = calculer_facture($lignes);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle facture — Facturation</title>
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
        main {
            max-width: 980px;
            margin: 28px auto;
            padding: 0 18px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }
        @media(max-width: 680px) { main { grid-template-columns: 1fr; } }
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
            padding: 26px;
        }
        h2 { font-size: 16px; color: #1e3a5f; margin-bottom: 18px; font-weight: 700; }
        label { display: block; font-size: 13px; color: #555; margin-bottom: 5px; font-weight: 600; }
        input[type=text], input[type=number] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 14px;
            margin-bottom: 14px;
            transition: border-color .2s;
        }
        input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .input-scanner {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 14px;
        }
        .input-scanner input { margin-bottom: 0; flex: 1; }
        .btn {
            display: inline-block;
            padding: 9px 16px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: opacity .15s;
            white-space: nowrap;
        }
        .btn:hover { opacity: .88; }
        .btn-primaire { background: #2563eb; color: #fff; }
        .btn-vert { background: #16a34a; color: #fff; }
        .btn-orange { background: #ea580c; color: #fff; }
        .btn-secondaire { background: #e5e7eb; color: #374151; }
        .btn-full { width: 100%; display: block; text-align: center; margin-top: 10px; padding: 11px; }
        #video-scanner {
            width: 100%;
            max-height: 220px;
            border-radius: 8px;
            background: #000;
            display: none;
            margin-bottom: 10px;
            object-fit: cover;
        }
        .scanner-controls {
            display: flex;
            gap: 8px;
            margin-bottom: 14px;
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
        .erreur { background: #fee2e2; color: #b91c1c; padding: 11px 14px; border-radius: 7px; font-size: 13px; margin-bottom: 14px; border-left: 3px solid #b91c1c; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #f0f4ff; color: #1e3a5f; padding: 9px 10px; text-align: left; font-size: 12px; }
        td { padding: 8px 10px; border-bottom: 1px solid #f0f2f5; }
        tr:last-child td { border-bottom: none; }
        a.retirer { color: #dc2626; text-decoration: none; font-size: 18px; line-height: 1; }
        a.retirer:hover { color: #b91c1c; }
        .totaux { margin-top: 16px; border-top: 2px solid #e5e7eb; padding-top: 12px; }
        .totaux .ligne { display: flex; justify-content: space-between; padding: 3px 0; font-size: 14px; }
        .totaux .ttc { font-size: 17px; font-weight: 700; color: #2563eb; margin-top: 6px; padding-top: 8px; border-top: 1px solid #e5e7eb; }
        .vide { text-align: center; color: #9ca3af; padding: 30px 0; font-size: 14px; }
    </style>
</head>
<body>
<header>
    <strong>🧾 Nouvelle facture</strong>
    <nav>
        <a href="../../modules/produits/liste.php" style="margin-right:16px;">📦 Produits</a>
        <a href="../../index.php">← Accueil</a>
    </nav>
</header>

<main>
    <div class="card">
        <h2>Ajouter un produit</h2>

        <?php if ($erreur !== ''): ?>
            <div class="erreur">⚠️ <?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <div class="scanner-controls">
            <button type="button" id="btn-scanner"      class="btn btn-orange">📷 Scanner</button>
            <button type="button" id="btn-stop-scanner" class="btn btn-secondaire" style="display:none;">⏹ Arreter</button>
            <select id="select-camera" title="Choisir la camera"></select>
        </div>

        <video id="video-scanner" playsinline></video>

        <form method="POST" action="" id="form-ajout">
            <label for="code_barre">Code barre</label>
            <div class="input-scanner">
                <input type="text"
                       id="code_barre"
                       name="code_barre"
                       placeholder="Saisir ou scanner..."
                       autocomplete="off"
                       required>
            </div>

            <label for="quantite">Quantite</label>
            <input type="number" id="quantite" name="quantite" min="1" value="1" required>

            <button type="submit" class="btn btn-primaire btn-full">+ Ajouter a la facture</button>
        </form>
    </div>

    <div class="card">
        <h2>Facture en cours
            <span style="font-size:12px;color:#9ca3af;font-weight:400;">(<?= count($lignes) ?> ligne<?= count($lignes) > 1 ? 's' : '' ?>)</span>
        </h2>

        <?php if (empty($lignes)): ?>
            <p class="vide">Aucun produit ajoute.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th style="text-align:right">P.U.</th>
                        <th style="text-align:right">Qte</th>
                        <th style="text-align:right">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($lignes as $i => $ligne): ?>
                    <tr>
                        <td><?= htmlspecialchars($ligne['nom']) ?></td>
                        <td style="text-align:right"><?= number_format($ligne['prix_unitaire'], 2, ',', ' ') ?></td>
                        <td style="text-align:right"><?= intval($ligne['quantite']) ?></td>
                        <td style="text-align:right;font-weight:600;"><?= number_format($ligne['prix_unitaire'] * $ligne['quantite'], 2, ',', ' ') ?></td>
                        <td style="text-align:center;">
                            <a class="retirer"
                               href="?action=retirer&index=<?= $i ?>"
                               title="Retirer">×</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="totaux">
                <div class="ligne"><span>Total HT</span><span><?= number_format($totaux['total_ht'], 2, ',', ' ') ?> CDF</span></div>
                <div class="ligne"><span>TVA (<?= TVA * 100 ?>%)</span><span><?= number_format($totaux['tva'], 2, ',', ' ') ?> CDF</span></div>
                <div class="ligne ttc"><span>Total TTC</span><span><?= number_format($totaux['total_ttc'], 2, ',', ' ') ?> CDF</span></div>
            </div>

            <a class="btn btn-vert btn-full" href="afficher-facture.php">✅ Valider et afficher la facture</a>
            <a class="btn btn-secondaire btn-full"
               href="?action=vider"
               onclick="return confirm('Vider toute la facture en cours ?')"
               style="margin-top:8px;">🗑 Vider la facture</a>
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
        autoSubmit   : true
    });
});
</script>
</body>
</html>
