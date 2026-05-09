<?php
require_once '../../auth/session.php';
require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';

if (!est_super_admin()) {
    header('Location: ../../index.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant  = isset($_POST['identifiant'])  ? trim($_POST['identifiant'])  : '';
    $mot_de_passe = isset($_POST['mot_de_passe']) ? trim($_POST['mot_de_passe']) : '';
    $nom_complet  = isset($_POST['nom_complet'])  ? trim($_POST['nom_complet'])  : '';
    $role         = isset($_POST['role'])         ? trim($_POST['role'])         : 'caissier';

    if ($identifiant === '' || $mot_de_passe === '' || $nom_complet === '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $utilisateurs = lire_utilisateurs();

        if (identifiant_existe($utilisateurs, $identifiant)) {
            $erreur = 'Cet identifiant existe déjà.';
        } else {
            $utilisateurs[] = [
                'identifiant'  => $identifiant,
                'mot_de_passe' => $mot_de_passe,
                'role'         => $role,
                'nom_complet'  => $nom_complet,
                'actif'        => true,
            ];
            sauvegarder_utilisateurs($utilisateurs);
            header('Location: gestion-comptes.php?msg=Compte+créé+avec+succès');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un compte</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        header { background: #2563eb; color: #fff; padding: 14px 28px; display: flex; justify-content: space-between; align-items: center; }
        header a { color: #bfdbfe; text-decoration: none; }
        header a:hover { color: #fff; }
        main { max-width: 500px; margin: 40px auto; padding: 0 20px; }
        .card { background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        h2 { font-size: 18px; margin-bottom: 24px; color: #333; }
        label { display: block; margin-bottom: 5px; font-size: 14px; color: #555; }
        input, select { width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; margin-bottom: 16px; }
        button { background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; font-size: 14px; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .erreur { background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 5px; margin-bottom: 16px; font-size: 14px; }
        a.retour { display: inline-block; margin-top: 14px; color: #2563eb; font-size: 14px; text-decoration: none; }
    </style>
</head>
<body>
<header>
    <span>➕ Ajouter un compte</span>
    <a href="gestion-comptes.php">← Retour</a>
</header>
<main>
    <div class="card">
        <h2>Nouveau compte</h2>
        <?php if ($erreur): ?>
            <div class="erreur"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label>Identifiant</label>
            <input type="text" name="identifiant" required
                   value="<?= htmlspecialchars(isset($_POST['identifiant']) ? $_POST['identifiant'] : '') ?>">

            <label>Mot de passe</label>
            <input type="text" name="mot_de_passe" required
                   value="<?= htmlspecialchars(isset($_POST['mot_de_passe']) ? $_POST['mot_de_passe'] : '') ?>">

            <label>Nom complet</label>
            <input type="text" name="nom_complet" required
                   value="<?= htmlspecialchars(isset($_POST['nom_complet']) ? $_POST['nom_complet'] : '') ?>">

            <label>Rôle</label>
            <select name="role">
                <option value="caissier">Caissier (codes-barres, factures)</option>
                <option value="manager">Manager (produits, stock, rapports)</option>
            </select>

            <button type="submit">Créer le compte</button>
        </form>
    </div>
</main>
</body>
</html>
