<?php
require_once '../../auth/session.php';
require_once '../../config/config.php';
require_once '../../includes/fonctions-factures.php';

$page_titre = 'Rapport mensuel';
$page_icone = '📅';
$breadcrumb = ['Rapports' => null, 'Mensuel' => null];

// Mois et annee selectionnes (mois courant par defaut)
$annee_choisie = isset($_GET['annee']) ? intval($_GET['annee']) : intval(date('Y'));
$mois_choisi   = isset($_GET['mois'])  ? intval($_GET['mois'])  : intval(date('m'));

// Validation
if ($mois_choisi < 1 || $mois_choisi > 12) { $mois_choisi = intval(date('m')); }
if ($annee_choisie < 2000 || $annee_choisie > 2100) { $annee_choisie = intval(date('Y')); }

$mois_format = sprintf('%02d', $mois_choisi);

// Noms des mois
$noms_mois = [
    1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Aout',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre'
];

// Charger toutes les factures
$toutes_factures = lire_factures();

// Filtrer par mois/annee
$factures_mois = [];
foreach ($toutes_factures as $f) {
    if (isset($f['date'])) {
        $d = DateTime::createFromFormat('d/m/Y H:i', $f['date']);
        if ($d && $d->format('Y') === strval($annee_choisie) && $d->format('m') === $mois_format) {
            $factures_mois[] = $f;
        }
    }
}

// Totaux globaux du mois
$total_ht_mois  = 0;
$total_tva_mois = 0;
$total_ttc_mois = 0;
$nb_articles    = 0;

// Statistiques par jour (pour le graphe textuel)
$par_jour = [];
$nb_jours = cal_days_in_month(CAL_GREGORIAN, $mois_choisi, $annee_choisie);
for ($j = 1; $j <= $nb_jours; $j++) {
    $par_jour[$j] = ['nb_factures' => 0, 'total_ttc' => 0];
}

// Statistiques par produit
$par_produit = [];

foreach ($factures_mois as $f) {
    $ht  = isset($f['total_ht'])  ? floatval($f['total_ht'])  : 0;
    $tva = isset($f['tva'])        ? floatval($f['tva'])       : 0;
    $ttc = isset($f['total_ttc']) ? floatval($f['total_ttc']) : 0;

    $total_ht_mois  += $ht;
    $total_tva_mois += $tva;
    $total_ttc_mois += $ttc;

    // Par jour
    $d = DateTime::createFromFormat('d/m/Y H:i', $f['date']);
    if ($d) {
        $jour = intval($d->format('j'));
        $par_jour[$jour]['nb_factures']++;
        $par_jour[$jour]['total_ttc'] += $ttc;
    }

    // Par produit
    if (isset($f['lignes']) && is_array($f['lignes'])) {
        foreach ($f['lignes'] as $l) {
            $nom = isset($l['nom']) ? $l['nom'] : 'Inconnu';
            $qte = isset($l['quantite']) ? intval($l['quantite']) : 0;
            $ttc_ligne = isset($l['prix_unitaire']) ? $l['prix_unitaire'] * $qte * (1 + TVA) : 0;
            $nb_articles += $qte;
            if (!isset($par_produit[$nom])) {
                $par_produit[$nom] = ['quantite' => 0, 'total_ttc' => 0];
            }
            $par_produit[$nom]['quantite']  += $qte;
            $par_produit[$nom]['total_ttc'] += round($ttc_ligne, 2);
        }
    }
}

// Trier produits par total TTC desc
arsort_by_key($par_produit, 'total_ttc');

function arsort_by_key(&$array, $key) {
    uasort($array, function($a, $b) use ($key) {
        return $b[$key] <=> $a[$key];
    });
}
arsort_by_key($par_produit, 'total_ttc');

// Valeur max par jour pour le graphe
$max_ttc_jour = 0;
foreach ($par_jour as $d) {
    if ($d['total_ttc'] > $max_ttc_jour) { $max_ttc_jour = $d['total_ttc']; }
}

require_once '../../includes/header.php';
?>

<!-- Sélecteur de mois -->
<div class="card">
    <form method="GET" action="" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
        <label style="font-size:14px;font-weight:600;color:#555;">Mois :</label>
        <select name="mois" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:7px;font-size:14px;">
            <?php foreach ($noms_mois as $num => $nom): ?>
            <option value="<?= $num ?>" <?= $num === $mois_choisi ? 'selected' : '' ?>><?= $nom ?></option>
            <?php endforeach; ?>
        </select>

        <label style="font-size:14px;font-weight:600;color:#555;">Annee :</label>
        <select name="annee" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:7px;font-size:14px;">
            <?php for ($a = intval(date('Y')); $a >= 2020; $a--): ?>
            <option value="<?= $a ?>" <?= $a === $annee_choisie ? 'selected' : '' ?>><?= $a ?></option>
            <?php endfor; ?>
        </select>

        <button type="submit" class="btn btn-primaire">Afficher</button>
    </form>
</div>

<h2 style="font-size:18px;color:#1e3a5f;margin-bottom:18px;">
    Synthese — <?= $noms_mois[$mois_choisi] ?> <?= $annee_choisie ?>
</h2>

<!-- KPI mensuels -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:16px;margin-bottom:24px;">
    <?php
    $kpis = [
        ['📄', 'Factures emises',  count($factures_mois), '', '#2563eb'],
        ['📦', 'Articles vendus',  $nb_articles,          '', '#16a34a'],
        ['💵', 'Total HT',         number_format($total_ht_mois,  2, ',', ' '), ' CDF', '#7c3aed'],
        ['🧾', 'TVA collectee',    number_format($total_tva_mois, 2, ',', ' '), ' CDF', '#b45309'],
        ['💰', 'Total TTC',        number_format($total_ttc_mois, 2, ',', ' '), ' CDF', '#0f766e'],
        ['📈', 'Moyenne/facture',  count($factures_mois) > 0 ? number_format($total_ttc_mois / count($factures_mois), 2, ',', ' ') : '0,00', ' CDF', '#dc2626'],
    ];
    foreach ($kpis as $k): ?>
    <div style="background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.07);padding:18px 16px;">
        <div style="font-size:26px;margin-bottom:5px;"><?= $k[0] ?></div>
        <div style="font-size:12px;color:#6b7280;margin-bottom:3px;"><?= $k[1] ?></div>
        <div style="font-size:20px;font-weight:700;color:<?= $k[4] ?>;"><?= $k[2] ?><?= $k[3] ?></div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (!empty($factures_mois)): ?>

<!-- Graphe activite par jour -->
<div class="card">
    <h3 style="font-size:15px;color:#1e3a5f;margin-bottom:16px;">📊 Activite quotidienne — <?= $noms_mois[$mois_choisi] ?> <?= $annee_choisie ?></h3>
    <div style="overflow-x:auto;">
        <div style="display:flex;align-items:flex-end;gap:3px;height:120px;min-width:<?= $nb_jours * 24 ?>px;">
            <?php for ($j = 1; $j <= $nb_jours; $j++):
                $ttc  = $par_jour[$j]['total_ttc'];
                $pct  = $max_ttc_jour > 0 ? ($ttc / $max_ttc_jour) * 100 : 0;
                $h    = max(2, round($pct * 1.1));
                $col  = $par_jour[$j]['nb_factures'] > 0 ? '#2563eb' : '#e5e7eb';
                $tip  = $par_jour[$j]['nb_factures'] > 0
                        ? $j . '/' . $mois_format . ' : ' . $par_jour[$j]['nb_factures'] . ' facture(s) — ' . number_format($ttc, 0, ',', ' ') . ' CDF'
                        : $j . '/' . $mois_format . ' : aucune vente';
            ?>
            <div title="<?= htmlspecialchars($tip) ?>"
                 style="flex:1;background:<?= $col ?>;height:<?= $h ?>px;border-radius:3px 3px 0 0;min-height:2px;position:relative;cursor:pointer;">
            </div>
            <?php endfor; ?>
        </div>
        <!-- Légende jours -->
        <div style="display:flex;gap:3px;min-width:<?= $nb_jours * 24 ?>px;margin-top:4px;">
            <?php for ($j = 1; $j <= $nb_jours; $j++): ?>
            <div style="flex:1;text-align:center;font-size:10px;color:#9ca3af;"><?= $j ?></div>
            <?php endfor; ?>
        </div>
    </div>
    <p style="font-size:11px;color:#9ca3af;margin-top:8px;">Survolez les barres pour voir le detail. Bleu = jour avec ventes.</p>
</div>

<!-- Tableau des ventes par produit -->
<div class="card">
    <h3 style="font-size:15px;color:#1e3a5f;margin-bottom:16px;">📦 Ventes par produit</h3>
    <?php if (empty($par_produit)): ?>
        <p style="color:#9ca3af;font-size:14px;">Aucun produit vendu ce mois.</p>
    <?php else: ?>
    <table class="tableau">
        <thead>
            <tr>
                <th>Rang</th>
                <th>Produit</th>
                <th style="text-align:right">Quantite vendue</th>
                <th style="text-align:right">CA TTC</th>
                <th style="text-align:right">Part du CA</th>
            </tr>
        </thead>
        <tbody>
        <?php $rang = 1; foreach ($par_produit as $nom => $stats): ?>
            <tr>
                <td>
                    <?php if ($rang === 1): ?>
                        <span style="font-size:18px;" title="1er">🥇</span>
                    <?php elseif ($rang === 2): ?>
                        <span style="font-size:18px;" title="2e">🥈</span>
                    <?php elseif ($rang === 3): ?>
                        <span style="font-size:18px;" title="3e">🥉</span>
                    <?php else: ?>
                        <span style="color:#9ca3af;"><?= $rang ?></span>
                    <?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($nom) ?></strong></td>
                <td style="text-align:right"><?= $stats['quantite'] ?></td>
                <td style="text-align:right;font-weight:600;"><?= number_format($stats['total_ttc'], 2, ',', ' ') ?> CDF</td>
                <td style="text-align:right">
                    <?php $pct = $total_ttc_mois > 0 ? round($stats['total_ttc'] / $total_ttc_mois * 100, 1) : 0; ?>
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px;">
                        <div style="width:60px;height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;">
                            <div style="width:<?= $pct ?>%;height:100%;background:#2563eb;border-radius:4px;"></div>
                        </div>
                        <span style="min-width:36px;"><?= $pct ?>%</span>
                    </div>
                </td>
            </tr>
        <?php $rang++; endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Liste complete des factures du mois -->
<div class="card">
    <h3 style="font-size:15px;color:#1e3a5f;margin-bottom:16px;">
        Liste des factures — <?= $noms_mois[$mois_choisi] ?> <?= $annee_choisie ?>
    </h3>
    <table class="tableau">
        <thead>
            <tr>
                <th>N° Facture</th>
                <th>Date</th>
                <th>Vendeur</th>
                <th style="text-align:right">HT</th>
                <th style="text-align:right">TVA</th>
                <th style="text-align:right">TTC</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($factures_mois as $f): ?>
            <tr>
                <td><span class="badge-bleu"><?= htmlspecialchars($f['id']) ?></span></td>
                <td><?= htmlspecialchars(isset($f['date']) ? $f['date'] : '') ?></td>
                <td><?= htmlspecialchars(isset($f['vendeur']) ? $f['vendeur'] : '') ?></td>
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
                <td colspan="3" style="padding:10px 14px;font-weight:700;font-size:13px;color:#1e3a5f;">TOTAUX DU MOIS</td>
                <td style="text-align:right;padding:10px 14px;font-weight:700;"><?= number_format($total_ht_mois,  2, ',', ' ') ?></td>
                <td style="text-align:right;padding:10px 14px;font-weight:700;"><?= number_format($total_tva_mois, 2, ',', ' ') ?></td>
                <td style="text-align:right;padding:10px 14px;font-weight:700;color:#2563eb;"><?= number_format($total_ttc_mois, 2, ',', ' ') ?> CDF</td>
            </tr>
        </tfoot>
    </table>
</div>

<?php else: ?>
<div class="card">
    <p style="color:#9ca3af;text-align:center;padding:40px;font-size:15px;">
        Aucune facture enregistree pour <?= $noms_mois[$mois_choisi] ?> <?= $annee_choisie ?>.
    </p>
</div>
<?php endif; ?>

<div style="display:flex;gap:12px;">
    <a href="rapport-journalier.php" class="btn btn-secondaire">📊 Rapport journalier</a>
    <a href="<?= $_racine ?>index.php" class="btn btn-secondaire">← Accueil</a>
</div>

<?php require_once '../../includes/footer.php'; ?>
