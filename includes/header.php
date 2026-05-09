<?php
/**
 * includes/header.php
 * Inclure APRES session.php et config.php.
 * Variables attendues :
 *   $page_titre  (string) — titre affiché dans l'onglet et l'en-tête
 *   $page_icone  (string) — emoji optionnel (défaut 🧾)
 */
if (!isset($page_titre))  { $page_titre = 'Facturation'; }
if (!isset($page_icone))  { $page_icone = '🧾'; }

$user = isset($_SESSION['user']) ? $_SESSION['user'] : ['nom_complet' => '', 'role' => ''];

// Prefixe vers la racine selon la profondeur du script appelant
$_racine = '';
if (isset($_SERVER['SCRIPT_FILENAME'])) {
    $script_dir  = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
    $project_dir = realpath(__DIR__ . '/..');
    if ($script_dir && $project_dir) {
        $relative    = str_replace($project_dir, '', $script_dir);
        $relative    = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative), DIRECTORY_SEPARATOR);
        $depth       = ($relative === '') ? 0 : substr_count($relative, DIRECTORY_SEPARATOR) + 1;
        $_racine     = str_repeat('../', $depth);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_icone . ' ' . $page_titre) ?> — Facturation</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        /* ── HEADER ── */
        .site-header {
            background: linear-gradient(90deg, #1e3a5f, #2563eb);
            color: #fff;
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            box-shadow: 0 2px 8px rgba(0,0,0,.18);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .site-header .brand {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: .3px;
            text-decoration: none;
            color: #fff;
        }
        .site-header .brand span { opacity: .75; font-weight: 400; margin-left: 6px; font-size: 14px; }
        .site-header nav a {
            color: rgba(255,255,255,.80);
            text-decoration: none;
            font-size: 14px;
            margin-left: 20px;
            transition: color .15s;
        }
        .site-header nav a:hover { color: #fff; }
        .site-header nav a.active { color: #fff; font-weight: 600; }
        .site-header .user-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: rgba(255,255,255,.80);
        }
        .site-header .user-bar .badge {
            background: rgba(255,255,255,.15);
            border-radius: 20px;
            padding: 3px 10px;
            font-size: 12px;
        }
        .site-header .user-bar a {
            color: #fbbf24;
            text-decoration: none;
            font-size: 13px;
        }
        .site-header .user-bar a:hover { text-decoration: underline; }

        /* ── BREADCRUMB ── */
        .breadcrumb {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 9px 28px;
            font-size: 13px;
            color: #6b7280;
        }
        .breadcrumb a { color: #2563eb; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb .sep { margin: 0 6px; }

        /* ── PAGE WRAPPER ── */
        .page-wrap {
            max-width: 1000px;
            margin: 32px auto;
            padding: 0 20px;
        }
        .page-titre {
            font-size: 22px;
            font-weight: 700;
            color: #1e3a5f;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── CARTES ── */
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,.07);
            padding: 28px;
            margin-bottom: 24px;
        }

        /* ── ALERTES ── */
        .alerte-succes  { background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 18px; }
        .alerte-erreur  { background: #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 18px; }
        .alerte-info    { background: #dbeafe; color: #1e40af; padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 18px; }
        .alerte-avertissement { background: #fef3c7; color: #92400e; padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 18px; }

        /* ── TABLEAUX ── */
        table.tableau { width: 100%; border-collapse: collapse; }
        table.tableau th {
            background: #1e3a5f;
            color: #fff;
            padding: 11px 14px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
        }
        table.tableau td {
            padding: 10px 14px;
            border-bottom: 1px solid #f0f2f5;
            font-size: 13px;
            color: #333;
        }
        table.tableau tr:last-child td { border-bottom: none; }
        table.tableau tr:hover td { background: #f8fafc; }

        /* ── BOUTONS ── */
        .btn {
            display: inline-block;
            padding: 9px 18px;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: opacity .15s;
        }
        .btn:hover { opacity: .88; }
        .btn-primaire   { background: #2563eb; color: #fff; }
        .btn-succes     { background: #16a34a; color: #fff; }
        .btn-danger     { background: #dc2626; color: #fff; }
        .btn-secondaire { background: #e5e7eb; color: #374151; }
        .btn-sm { padding: 5px 12px; font-size: 12px; }

        /* ── FORMULAIRES ── */
        .form-groupe { margin-bottom: 18px; }
        .form-groupe label { display: block; font-size: 13px; color: #555; margin-bottom: 5px; font-weight: 600; }
        .form-groupe input,
        .form-groupe select,
        .form-groupe textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 14px;
            transition: border-color .2s;
        }
        .form-groupe input:focus,
        .form-groupe select:focus,
        .form-groupe textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }

        /* ── BADGES ── */
        .badge-vert   { background: #d1fae5; color: #065f46; padding: 2px 9px; border-radius: 20px; font-size: 12px; }
        .badge-rouge  { background: #fee2e2; color: #b91c1c; padding: 2px 9px; border-radius: 20px; font-size: 12px; }
        .badge-bleu   { background: #dbeafe; color: #1e40af; padding: 2px 9px; border-radius: 20px; font-size: 12px; }
        .badge-jaune  { background: #fef3c7; color: #92400e; padding: 2px 9px; border-radius: 20px; font-size: 12px; }
    </style>
</head>
<body>

<!-- En-tête global -->
<header class="site-header">
    <a class="brand" href="<?= $_racine ?>index.php">
        🧾 Facturation <span>Pro</span>
    </a>

    <nav>
        <a href="<?= $_racine ?>modules/produits/liste.php">📦 Produits</a>
        <a href="<?= $_racine ?>modules/facturation/nouvelle-facture.php">🧾 Facture</a>
        <a href="<?= $_racine ?>modules/rapports/rapport-journalier.php">📊 Rapports</a>
        <?php if (isset($user['role']) && $user['role'] === 'super_admin'): ?>
        <a href="<?= $_racine ?>modules/admin/gestion-comptes.php">👥 Comptes</a>
        <?php endif; ?>
    </nav>

    <div class="user-bar">
        <span><?= htmlspecialchars($user['nom_complet']) ?></span>
        <span class="badge"><?= htmlspecialchars($user['role']) ?></span>
        <a href="<?= $_racine ?>auth/logout.php">Deconnexion</a>
    </div>
</header>

<!-- Fil d'Ariane -->
<?php if (isset($breadcrumb) && is_array($breadcrumb)): ?>
<div class="breadcrumb">
    <a href="<?= $_racine ?>index.php">Accueil</a>
    <?php foreach ($breadcrumb as $label => $lien): ?>
        <span class="sep">›</span>
        <?php if ($lien): ?>
            <a href="<?= htmlspecialchars($lien) ?>"><?= htmlspecialchars($label) ?></a>
        <?php else: ?>
            <span><?= htmlspecialchars($label) ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="page-wrap">
<?php if ($page_titre !== 'Accueil'): ?>
    <div class="page-titre"><?= $page_icone ?> <?= htmlspecialchars($page_titre) ?></div>
<?php endif; ?>
