<?php
require_once '../../auth/session.php';
require_once '../../config/config.php';
require_once '../../includes/fonctions-produits.php';

$erreur  = '';
$succes  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_barre = isset($_POST['code_barre']) ? trim($_POST['code_barre']) : '';
    $nom        = isset($_POST['nom'])        ? trim($_POST['nom'])        : '';
    $prix       = isset($_POST['prix'])       ? trim($_POST['prix'])       : '';
    $quantite   = isset($_POST['quantite'])   ? trim($_POST['quantite'])   : '';

    if ($code_barre === '' || $nom === '' || $prix === '' || $quantite === '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } elseif (!is_numeric($prix) || floatval($prix) < 0) {
        $erreur = 'Le prix doit être un nombre positif.';
    } elseif (!ctype_digit($quantite) || intval($quantite) < 0) {
        $erreur = 'La quantité doit être un entier positif.';
    } else {
        $produits = lire_produits();

        // Vérifier doublon code barre
        foreach ($produits as $p) {
            if ($p['code_barre'] === $code_barre) {
                $erreur = 'Un produit avec ce code barre existe déjà.';
                break;
            }
        }

        if ($erreur === '') {
            $produits[] = [
                'code_barre' => $code_barre,
                'nom'        => $nom,
                'prix'       => round(floatval($prix), 2),
                'quantite'   => intval($quantite),
            ];
            sauvegarder_produits($produits);
            $succes = 'Produit enregistré avec succès.';
            // Vider le formulaire
            $_POST = [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrer un produit</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        header { background: #2563eb; color: #fff; padding: 14px 28px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: #bfdbfe; text-decoration: none; }
        header a:hover { color: #fff; }
        main { max-width: 500px; margin: 40px auto; padding: 0 20px; }
        .card { background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        h2 { font-size: 18px; margin-bottom: 22px; color: #333; }
        label { display: block; margin-bottom: 5px; font-size: 14px; color: #555; }
        input { width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; margin-bottom: 16px; }
        button { background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; font-size: 14px; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .erreur  { background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 5px; margin-bottom: 16px; font-size: 14px; }
        .succes  { background: #d1fae5; color: #065f46; padding: 10px; border-radius: 5px; margin-bottom: 16px; font-size: 14px; }
        .liens   { margin-top: 16px; font-size: 14px; }
        .liens a { color: #2563eb; text-decoration: none; margin-right: 14px; }
    </style>
</head>
<body>
<header>
    <span>📦 Enregistrer un produit</span>
    <a href="liste.php">← Liste des produits</a>
</header>
<main>
    <div class="card">
        <h2>Nouveau produit</h2>
        <?php if ($erreur !== ''): ?>
            <div class="erreur"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>
        <?php if ($succes !== ''): ?>
            <div class="succes"><?= htmlspecialchars($succes) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label>Code barre</label>
            <input type="text" name="code_barre" required
                   value="<?= htmlspecialchars(isset($_POST['code_barre']) ? $_POST['code_barre'] : '') ?>">

            <label>Nom du produit</label>
            <input type="text" name="nom" required
                   value="<?= htmlspecialchars(isset($_POST['nom']) ? $_POST['nom'] : '') ?>">

            <label>Prix unitaire (CDF)</label>
            <input type="number" name="prix" min="0" step="0.01" required
                   value="<?= htmlspecialchars(isset($_POST['prix']) ? $_POST['prix'] : '') ?>">

            <label>Quantité en stock</label>
            <input type="number" name="quantite" min="0" step="1" required
                   value="<?= htmlspecialchars(isset($_POST['quantite']) ? $_POST['quantite'] : '') ?>">

            <button type="submit">Enregistrer</button>
        </form>
        <div class="liens">
            <a href="liste.php">Voir la liste des produits</a>
            <a href="../../index.php">Accueil</a>
        </div>
    </div>
</main>
</body>
</html>
