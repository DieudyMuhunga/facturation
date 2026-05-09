<?php
require_once '../../auth/session.php';
require_once '../../config/config.php';
require_once '../../includes/fonctions-produits.php';
require_once '../../includes/fonctions-factures.php';
require_once 'calcul.php';

// Vérifier qu'il y a des lignes
if (!isset($_SESSION['facture']) || empty($_SESSION['facture'])) {
    header('Location: nouvelle-facture.php');
    exit;
}

$lignes  = $_SESSION['facture'];
$totaux  = calculer_facture($lignes);

// Générer identifiant unique et informations de la facture
$id      = generer_id_facture();
$date    = date('d/m/Y H:i');
$vendeur = $_SESSION['user']['nom_complet'] ?? 'Utilisateur';

// Mettre à jour les stocks
$erreurs_stock = [];
foreach ($lignes as $ligne) {
    $ok = deduire_stock($ligne['code_barre'], intval($ligne['quantite']));
    if (!$ok) {
        $erreurs_stock[] = $ligne['nom'] ?? $ligne['code_barre'];
    }
}

// Enregistrer la facture dans factures.json
$facture = [
    'id'         => $id,
    'date'       => $date,
    'vendeur'    => $vendeur,
    'lignes'     => $lignes,
    'total_ht'   => $totaux['total_ht'],
    'tva'        => $totaux['tva'],
    'total_ttc'  => $totaux['total_ttc'],
];

$factures   = lire_factures();
$factures[] = $facture;
sauvegarder_factures($factures);

// Vider la facture de la session
$_SESSION['facture'] = [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture <?= htmlspecialchars($id) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        header { background: #2563eb; color: #fff; padding: 14px 28px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: #bfdbfe; text-decoration: none; }
        header a:hover { color: #fff; }
        main { max-width: 680px; margin: 36px auto; padding: 0 20px; }
        .facture { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,.1); padding: 36px; }
        .entete { display: flex; justify-content: space-between; margin-bottom: 28px; }
        .entete h1 { font-size: 24px; color: #2563eb; }
        .entete .meta { text-align: right; font-size: 13px; color: #555; line-height: 1.7; }
        .info-societe { margin-bottom: 24px; font-size: 14px; color: #333; line-height: 1.6; }
        .info-societe strong { font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th { background: #2563eb; color: #fff; padding: 10px 12px; text-align: left; font-size: 13px; }
        td { padding: 9px 12px; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
        tr:last-child td { border-bottom: none; }
        td.right, th.right { text-align: right; }
        .totaux { border-top: 2px solid #e5e7eb; padding-top: 14px; font-size: 14px; float: right; min-width: 260px; }
        .totaux div { display: flex; justify-content: space-between; padding: 4px 0; }
        .totaux .ttc { font-weight: bold; font-size: 17px; color: #2563eb; border-top: 1px solid #e5e7eb; padding-top: 8px; margin-top: 4px; }
        .clearfix::after { content: ''; display: block; clear: both; }
        .actions { margin-top: 28px; display: flex; gap: 12px; }
        .btn { padding: 10px 20px; border-radius: 5px; text-decoration: none; font-size: 14px; cursor: pointer; border: none; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: #f0f2f5; color: #333; }
        .btn-secondary:hover { background: #e2e8f0; }
        .avertissement { background: #fef3c7; color: #92400e; padding: 10px 14px; border-radius: 5px; font-size: 13px; margin-bottom: 20px; }
        @media print {
            header, .actions { display: none; }
            body { background: #fff; }
            main { margin: 0; padding: 0; max-width: 100%; }
            .facture { box-shadow: none; }
        }
    </style>
</head>
<body>
<header>
    <span>🧾 Facture enregistrée</span>
    <a href="../../index.php">← Accueil</a>
</header>
<main>
    <?php if (!empty($erreurs_stock)): ?>
    <div class="avertissement">
        ⚠️ Stock insuffisant pour : <?= implode(', ', array_map('htmlspecialchars', $erreurs_stock)) ?>.
        La facture est enregistrée mais le stock n'a pas été déduit pour ces articles.
    </div>
    <?php endif; ?>

    <div class="facture">
        <div class="entete">
            <div>
                <h1>🧾 FACTURE</h1>
                <div style="font-size:13px;color:#888;margin-top:4px;">N° <?= htmlspecialchars($id) ?></div>
            </div>
            <div class="meta">
                <div><strong>Date :</strong> <?= htmlspecialchars($date) ?></div>
                <div><strong>Vendeur :</strong> <?= htmlspecialchars($vendeur) ?></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Code barre</th>
                    <th>Désignation</th>
                    <th class="right">P.U. HT</th>
                    <th class="right">Qté</th>
                    <th class="right">Total HT</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lignes as $ligne): ?>
                <tr>
                    <td><?= htmlspecialchars($ligne['code_barre']) ?></td>
                    <td><?= htmlspecialchars($ligne['nom']) ?></td>
                    <td class="right"><?= number_format($ligne['prix_unitaire'], 2, ',', ' ') ?> CDF</td>
                    <td class="right"><?= intval($ligne['quantite']) ?></td>
                    <td class="right"><?= number_format($ligne['prix_unitaire'] * $ligne['quantite'], 2, ',', ' ') ?> CDF</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="clearfix">
            <div class="totaux">
                <div><span>Total HT</span><span><?= number_format($totaux['total_ht'],  2, ',', ' ') ?> CDF</span></div>
                <div><span>TVA (<?= TVA * 100 ?>%)</span><span><?= number_format($totaux['tva'],       2, ',', ' ') ?> CDF</span></div>
                <div class="ttc"><span>Total TTC</span><span><?= number_format($totaux['total_ttc'], 2, ',', ' ') ?> CDF</span></div>
            </div>
        </div>
    </div>

    <div class="actions">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimer</button>
        <a class="btn btn-secondary" href="nouvelle-facture.php">+ Nouvelle facture</a>
        <a class="btn btn-secondary" href="../../index.php">Accueil</a>
    </div>
</main>
</body>
</html>
