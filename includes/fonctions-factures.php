<?php
//  includes/fonctions-factures.php — Fonctions facturation

function lire_factures(): array {
    $contenu = file_get_contents(FACTURES_FILE);
    $data    = json_decode($contenu, true);
    return is_array($data) ? $data : [];
}

function sauvegarder_factures(array $factures): void {
    file_put_contents(FACTURES_FILE, json_encode($factures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function calculer_totaux(array $lignes): array {
    $total_ht = 0;
    foreach ($lignes as $ligne) {
        $total_ht += $ligne['prix_unitaire'] * $ligne['quantite'];
    }
    $montant_tva = $total_ht * TVA;
    $total_ttc   = $total_ht + $montant_tva;

    return [
        'total_ht'    => round($total_ht, 2),
        'montant_tva' => round($montant_tva, 2),
        'total_ttc'   => round($total_ttc, 2),
    ];
}

function generer_id_facture(): string {
    return 'FAC-' . date('Ymd') . '-' . time();
}
