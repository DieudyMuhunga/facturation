<?php
require_once '../../auth/session.php';
require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';

if (!est_super_admin()) {
    header('Location: ../../index.php');
    exit;
}

$utilisateurs = lire_utilisateurs();
$message = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des comptes</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        header { background: linear-gradient(90deg, #1e3a5f, #2563eb); color: #fff; padding: 0 28px; height: 56px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        header a { color: rgba(255,255,255,.85); text-decoration: none; font-size: 14px; }
        header a:hover { color: #fff; }
        main { max-width: 1000px; margin: 28px auto; padding: 0 20px; }
        .entete-page { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        h2 { font-size: 22px; color: #1e3a5f; }
        .btn { display: inline-block; background: #2563eb; color: #fff; padding: 9px 18px; text-decoration: none; border-radius: 5px; font-size: 14px; border: none; cursor: pointer; }
        .btn:hover { background: #1d4ed8; }
        .btn-danger { background: #dc2626; }
        .btn-danger:hover { background: #b91c1c; }
        .alerte { padding: 12px 16px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; }
        .alerte.succes { background: #d1fae5; color: #065f46; border-left: 3px solid #10b981; }
        .alerte.erreur { background: #fee2e2; color: #b91c1c; border-left: 3px solid #ef4444; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        thead { background: #f3f4f6; border-bottom: 1px solid #e5e7eb; }
        th { padding: 12px 16px; text-align: left; font-weight: 600; color: #1f2937; font-size: 13px; }
        td { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: #f9fafb; }
        em { color: #9ca3af; font-style: italic; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: 600; }
        .badge-caissier { background: #e0e7ff; color: #3730a3; }
        .badge-manager { background: #fef3c7; color: #92400e; }
        .badge-admin { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>
<header>
    <strong>👤 Gestion des comptes</strong>
    <a href="../../index.php">← Accueil</a>
</header>

<main>
    <div class="entete-page">
        <h2>👤 Gestion des comptes</h2>
        <a class="btn" href="ajouter-compte.php">+ Ajouter un compte</a>
    </div>

    <?php if ($message === 'ajoute'): ?>
        <div class="alerte succes">✅ Compte ajouté avec succès.</div>
    <?php elseif ($message === 'supprime'): ?>
        <div class="alerte succes">✅ Compte supprimé.</div>
    <?php elseif ($message === 'erreur'): ?>
        <div class="alerte erreur">❌ Une erreur s'est produite.</div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Identifiant</th>
                <th>Nom complet</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Permissions</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($utilisateurs)): ?>
            <tr><td colspan="6" style="text-align:center;color:#888;padding:20px;">Aucun utilisateur.</td></tr>
        <?php else: ?>
            <?php foreach ($utilisateurs as $u): 
                $role_label = '';
                $role_class = '';
                if ($u['role'] === 'super_admin') {
                    $role_label = 'Super Admin';
                    $role_class = 'badge-admin';
                } elseif ($u['role'] === 'manager') {
                    $role_label = 'Manager';
                    $role_class = 'badge-manager';
                } else {
                    $role_label = 'Caissier';
                    $role_class = 'badge-caissier';
                }
                
                $permissions = '';
                if ($u['role'] === 'super_admin') {
                    $permissions = 'Tous les accès';
                } elseif ($u['role'] === 'manager') {
                    $permissions = 'Produits, Stock, Rapports, Factures';
                } else {
                    $permissions = 'Codes-barres, Factures';
                }
            ?>
            <tr>
                <td><?= htmlspecialchars($u['identifiant']) ?></td>
                <td><?= htmlspecialchars($u['nom_complet']) ?></td>
                <td><span class="badge <?= $role_class ?>"><?= $role_label ?></span></td>
                <td><?= (isset($u['actif']) && $u['actif']) ? '✅ Actif' : '❌ Inactif' ?></td>
                <td><small><?= $permissions ?></small></td>
                <td>
                    <?php if ($u['identifiant'] !== $_SESSION['user']['identifiant']): ?>
                        <a class="btn btn-danger" href="supprimer-compte.php?identifiant=<?= urlencode($u['identifiant']) ?>"
                           onclick="return confirm('Supprimer ce compte ?')">Supprimer</a>
                    <?php else: ?>
                        <em>Compte actuel</em>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</main>
</body>
</html>
