<?php
require_once '../../auth/session.php';
require_once '../../config/config.php';
require_once '../../includes/fonctions-factures.php';

$page_titre = 'Rapport journalier';
$page_icone = '📊';
$breadcrumb = ['Rapports' => null, 'Journalier' => null];

// Date selectionnee (aujourd'hui par defaut)
$date_choisie = isset($_GET['date']) ? trim($_GET['date']) : date('Y-m-d');
// Validation format date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_choisie)) {
    $date_choisie = date('Y-m-d');
}

// Charger toutes les factures
$toutes_factures = lire_factures();

// Filtrer par date (le champ 'date' est au format d/m/Y H:i)
$factures_du_jour = [];
foreach ($toutes_factures as $f) {
    // Extraire la date (les 10 premiers caracteres : dd/mm/YYYY)
    if (isset($f['date'])) {
        $date_facture = DateTime::createFromFormat('d/m/Y H:i', $f['date']);
        if ($date_facture && $date_facture->format('Y-m-d') === $date_choisie) {
            $factures_du_jour[] = $f;
        }
    }
}

// Calculer les totaux du jour
$total_ht_jour  = 0;
$total_tva_jour = 0;
$total_ttc_jour = 0;
$nb_articles    = 0;

foreach ($factures_du_jour as $f) {
    $total_ht_jour  += isset($f['total_ht'])  ? $f['total_ht']  : 0;
    $total_tva_jour += isset($f['tva'])        ? $f['tva']       : 0;
    $total_ttc_jour += isset($f['total_ttc']) ? $f['total_ttc'] : 0;
    if (isset($f['lignes']) && is_array($f['lignes'])) {
        foreach ($f['lignes'] as $ligne) {
            $nb_articles += isset($ligne['quantite']) ? intval($ligne['quantite']) : 0;
        }
    }
}

require_once '../../includes/header.php';
?>

<!-- Sélecteur de date -->
<div class="card">
    <form method="GET" action="" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
        <label style="font-size:14px;font-weight:600;color:#555;">Choisir une date :</label>
        <input type="date" name="date" value="<?= htmlspecialchars($date_choisie) ?>"
               max="<?= date('Y-m-d') ?>"
               style="padding:8px 12px;border:1px solid #d1d5db;border-radius:7px;font-size:14px;">
        <button type="submit" class="btn btn-primaire">Afficher</button>
        <?php if ($date_choisie !== date('Y-m-d')): ?>
            <a href="rapport-journalier.php" class="btn btn-secondaire">Aujourd'hui</a>
        <?php endif; ?>
    </form>
</div>

<!-- KPI du jour -->
<?php
$date_affichee = DateTime::createFromFormat('Y-m-d', $date_choisie);
$date_label    = $date_affichee ? $date_affichee->format('d/m/Y') : $date_choisie;
?>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;">
    <?php
    $kpis = [
        ['📄', 'Factures emises',  count($factures_du_jour), '', '#2563eb'],
        ['📦', 'Articles vendus',  $nb_articles,             '', '#16a34a'],
        ['💵', 'Total HT',         number_format($total_ht_jour,  2, ',', ' '), ' CDF', '#7c3aed'],
        ['🧾', 'TVA collectee',    number_format($total_tva_jour, 2, ',', ' '), ' CDF', '#b45309'],
        ['💰', 'Total TTC',        number_format($total_ttc_jour, 2, ',', ' '), ' CDF', '#0f766e'],
    ];
    foreach ($kpis as $k): ?>
    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.07);padding:20px 18px;">
        <div style="font-size:28px;margin-bottom:6px;"><?= $k[0] ?></div>
        <div style="font-size:12px;color:#6b7280;margin-bottom:4px;"><?= $k[1] ?></div>
        <div style="font-size:22px;font-weight:700;color:<?= $k[4] ?>;"><?= $k[2] ?><?= $k[3] ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Liste des factures du jour -->
<div class="card">
    <h2 style="font-size:16px;color:#1e3a5f;margin-bottom:16px;">
        Factures du <?= htmlspecialchars($date_label) ?>
        <span style="font-size:13px;color:#6b7280;font-weight:400;">(<?= count($factures_du_jour) ?> facture<?= count($factures_du_jour) > 1 ? 's' : '' ?>)</span>
    </h2>

    <?php if (empty($factures_du_jour)): ?>
        <p style="color:#9ca3af;text-align:center;padding:30px;font-size:14px;">
            Aucune facture enregistree pour cette date.
        </p>
    <?php else: ?>
        <table class="tableau">
            <thead>
                <tr>
                    <th>N° Facture</th>
                    <th>Heure</th>
                    <th>Vendeur</th>
                    <th>Articles</th>
                    <th style="text-align:right">HT</th>
                    <th style="text-align:right">TVA</th>
                    <th style="text-align:right">TTC</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($factures_du_jour as $f):
                $nb = 0;
                if (isset($f['lignes'])) {
                    foreach ($f['lignes'] as $l) { $nb += intval($l['quantite']); }
                }
                // Extraire l'heure
                $heure = '';
                if (isset($f['date'])) {
                    $parts = explode(' ', $f['date']);
                    $heure = isset($parts[1]) ? $parts[1] : '';
                }
            ?>
                <tr>
                    <td><span class="badge-bleu"><?= htmlspecialchars($f['id']) ?></span></td>
                    <td><?= htmlspecialchars($heure) ?></td>
                    <td><?= htmlspecialchars(isset($f['vendeur']) ? $f['vendeur'] : '') ?></td>
                    <td><?= $nb ?></td>
                    <td style="text-align:right"><?= number_format(isset($f['total_ht'])  ? $f['total_ht']  : 0, 2, ',', ' ') ?></td>
                    <td style="text-align:right"><?= number_format(isset($f['tva'])        ? $f['tva']       : 0, 2, ',', ' ') ?></td>
                    <td style="text-align:right;font-weight:700;color:#2563eb;">
                        <?= number_format(isset($f['total_ttc']) ? $f['total_ttc'] : 0, 2, ',', ' ') ?> CDF
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc;">
                    <td colspan="4" style="padding:10px 14px;font-weight:700;font-size:13px;color:#1e3a5f;">TOTAUX</td>
                    <td style="text-align:right;padding:10px 14px;font-weight:700;"><?= number_format($total_ht_jour,  2, ',', ' ') ?></td>
                    <td style="text-align:right;padding:10px 14px;font-weight:700;"><?= number_format($total_tva_jour, 2, ',', ' ') ?></td>
                    <td style="text-align:right;padding:10px 14px;font-weight:700;color:#2563eb;"><?= number_format($total_ttc_jour, 2, ',', ' ') ?> CDF</td>
                </tr>
            </tfoot>
        </table>

        <!-- Détail des lignes de chaque facture -->
        <h3 style="font-size:14px;color:#1e3a5f;margin:28px 0 14px;">Detail par facture</h3>
        <?php foreach ($factures_du_jour as $f): ?>
        <div style="margin-bottom:20px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
            <div style="background:#f8fafc;padding:10px 14px;font-size:13px;color:#374151;display:flex;justify-content:space-between;">
                <strong><?= htmlspecialchars($f['id']) ?></strong>
                <span style="color:#6b7280;"><?= htmlspecialchars(isset($f['date']) ? $f['date'] : '') ?></span>
            </div>
            <?php if (isset($f['lignes']) && is_array($f['lignes'])): ?>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f0f2f5;">
                        <th style="padding:7px 12px;text-align:left;font-weight:600;color:#555;">Produit</th>
                        <th style="padding:7px 12px;text-align:right;font-weight:600;color:#555;">P.U.</th>
                        <th style="padding:7px 12px;text-align:right;font-weight:600;color:#555;">Qte</th>
                        <th style="padding:7px 12px;text-align:right;font-weight:600;color:#555;">Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($f['lignes'] as $l): ?>
                    <tr style="border-top:1px solid #f0f2f5;">
                        <td style="padding:7px 12px;"><?= htmlspecialchars($l['nom']) ?></td>
                        <td style="padding:7px 12px;text-align:right;"><?= number_format($l['prix_unitaire'], 2, ',', ' ') ?></td>
                        <td style="padding:7px 12px;text-align:right;"><?= intval($l['quantite']) ?></td>
                        <td style="padding:7px 12px;text-align:right;font-weight:600;">
                            <?= number_format($l['prix_unitaire'] * $l['quantite'], 2, ',', ' ') ?> CDF
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div style="display:flex;gap:12px;margin-top:4px;">
    <a href="rapport-mensuel.php" class="btn btn-secondaire">📅 Voir le rapport mensuel</a>
    <a href="<?= $_racine ?>index.php" class="btn btn-secondaire">← Accueil</a>
</div>

<?php require_once '../../includes/footer.php'; ?>
