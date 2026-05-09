<?php
require_once '../../auth/session.php';
require_once '../../config/config.php';
require_once '../../includes/fonctions-produits.php';

$produits = lire_produits();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des produits</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        header { background: #2563eb; color: #fff; padding: 14px 28px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: #bfdbfe; text-decoration: none; }
        header a:hover { color: #fff; }
        main { max-width: 900px; margin: 32px auto; padding: 0 20px; }
        h2 { font-size: 20px; margin-bottom: 18px; color: #333; }
        .actions { margin-bottom: 16px; }
        a.btn-primary { background: #2563eb; color: #fff; padding: 9px 18px; border-radius: 5px; text-decoration: none; font-size: 14px; }
        a.btn-primary:hover { background: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
        th { background: #2563eb; color: #fff; padding: 12px 14px; text-align: left; font-size: 14px; }
        td { padding: 11px 14px; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        tr:last-child td { border-bottom: none; }
        .stock-bas { color: #b91c1c; font-weight: bold; }
        .vide { text-align: center; color: #888; padding: 30px; }
    </style>
</head>
<body>
<header>
    <span>📦 Liste des produits</span>
    <a href="../../index.php">← Accueil</a>
</header>
<main>
    <h2>Catalogue produits</h2>
    <div class="actions">
        <a class="btn-primary" href="enregistrer.php">+ Nouveau produit</a>
    </div>
    <?php if (empty($produits)): ?>
        <p class="vide">Aucun produit enregistré.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Code barre</th>
                <th>Nom</th>
                <th>Prix unitaire</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($produits as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['code_barre']) ?></td>
                <td><?= htmlspecialchars($p['nom']) ?></td>
                <td><?= number_format($p['prix'], 2, ',', ' ') ?> CDF</td>
                <td class="<?= $p['quantite'] <= 2 ? 'stock-bas' : '' ?>">
                    <?= intval($p['quantite']) ?>
                    <?= $p['quantite'] <= 2 ? ' ⚠️' : '' ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</main>
</body>
</html>
