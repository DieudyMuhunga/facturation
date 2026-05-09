<?php
require_once 'auth/session.php';
require_once 'config/config.php';
require_once 'includes/fonctions-factures.php';
require_once 'includes/fonctions-produits.php';

$page_titre = 'Accueil';
$page_icone = '🏠';

$user = $_SESSION['user'];

// Statistiques rapides
$factures = lire_factures();
$produits = lire_produits();

$nb_factures_auj = 0;
$ca_auj          = 0;
$aujourd_hui     = date('d/m/Y');

foreach ($factures as $f) {
    if (isset($f['date']) && strpos($f['date'], $aujourd_hui) === 0) {
        $nb_factures_auj++;
        $ca_auj += isset($f['total_ttc']) ? floatval($f['total_ttc']) : 0;
    }
}

$stock_bas = 0;
foreach ($produits as $p) {
    if (isset($p['quantite']) && intval($p['quantite']) <= 2) { $stock_bas++; }
}

require_once 'includes/header.php';
?>

<?php if ($stock_bas > 0): ?>
<div class="alerte-avertissement">
    ⚠️ <strong><?= $stock_bas ?> produit<?= $stock_bas > 1 ? 's' : '' ?></strong>
    en stock bas (≤ 2 unites).
    <a href="modules/produits/liste.php" style="color:#92400e;font-weight:600;">Voir les produits</a>
</div>
<?php endif; ?>

<!-- KPI rapides -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.07);padding:22px 20px;border-left:4px solid #2563eb;">
        <div style="font-size:13px;color:#6b7280;margin-bottom:4px;">Factures aujourd'hui</div>
        <div style="font-size:28px;font-weight:700;color:#2563eb;"><?= $nb_factures_auj ?></div>
    </div>
    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.07);padding:22px 20px;border-left:4px solid #16a34a;">
        <div style="font-size:13px;color:#6b7280;margin-bottom:4px;">CA du jour (TTC)</div>
        <div style="font-size:28px;font-weight:700;color:#16a34a;"><?= number_format($ca_auj, 0, ',', ' ') ?> <span style="font-size:14px;">CDF</span></div>
    </div>
    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.07);padding:22px 20px;border-left:4px solid #7c3aed;">
        <div style="font-size:13px;color:#6b7280;margin-bottom:4px;">Produits catalogue</div>
        <div style="font-size:28px;font-weight:700;color:#7c3aed;"><?= count($produits) ?></div>
    </div>
    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.07);padding:22px 20px;border-left:4px solid #b45309;">
        <div style="font-size:13px;color:#6b7280;margin-bottom:4px;">Total factures</div>
        <div style="font-size:28px;font-weight:700;color:#b45309;"><?= count($factures) ?></div>
    </div>
</div>

<!-- Menu principal -->
<h2 style="font-size:16px;color:#6b7280;margin-bottom:16px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">
    Acces rapide
</h2>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;">
    <?php
    $menus = [
        ['📦', 'Produits',        'Gerer le catalogue et les stocks',    'modules/produits/liste.php',               '#2563eb'],
        ['🧾', 'Nouvelle facture','Creer et enregistrer une facture',     'modules/facturation/nouvelle-facture.php', '#16a34a'],
        ['📊', 'Rapport journalier','Ventes et CA du jour',               'modules/rapports/rapport-journalier.php',  '#7c3aed'],
        ['📅', 'Rapport mensuel', 'Synthese mensuelle des ventes',        'modules/rapports/rapport-mensuel.php',     '#0f766e'],
    ];
    if ($user['role'] === 'super_admin') {
        $menus[] = ['👥', 'Comptes utilisateurs', 'Ajouter ou supprimer des comptes', 'modules/admin/gestion-comptes.php', '#b45309'];
    }
    foreach ($menus as $m): ?>
    <a href="<?= $m[3] ?>" style="
        background:#fff;
        border-radius:10px;
        box-shadow:0 2px 8px rgba(0,0,0,.07);
        padding:26px 22px;
        text-decoration:none;
        color:#1e3a5f;
        display:block;
        border-top:4px solid <?= $m[4] ?>;
        transition:box-shadow .2s, transform .2s;
        "
        onmouseover="this.style.boxShadow='0 6px 18px rgba(0,0,0,.13)';this.style.transform='translateY(-2px)'"
        onmouseout="this.style.boxShadow='0 2px 8px rgba(0,0,0,.07)';this.style.transform='translateY(0)'"
    >
        <div style="font-size:36px;margin-bottom:10px;"><?= $m[0] ?></div>
        <div style="font-size:16px;font-weight:700;margin-bottom:5px;"><?= $m[1] ?></div>
        <div style="font-size:13px;color:#6b7280;"><?= $m[2] ?></div>
    </a>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
