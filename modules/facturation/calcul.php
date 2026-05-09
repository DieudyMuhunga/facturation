<?php
/**
 * calcul.php — fonctions de calcul pour la facturation.
 * Ce fichier est inclus par les autres fichiers du module facturation.
 */

require_once '../../config/config.php';

/**
 * Calcule total HT, TVA et total TTC à partir d'un tableau de lignes.
 *
 * @param  array $lignes  Tableau de lignes : chaque ligne a 'prix_unitaire' et 'quantite'.
 * @return array          ['total_ht', 'tva', 'total_ttc']
 */
function calculer_facture(array $lignes) {
    $total_ht = 0.0;
    foreach ($lignes as $ligne) {
        $pu  = isset($ligne['prix_unitaire']) ? floatval($ligne['prix_unitaire']) : 0;
        $qte = isset($ligne['quantite'])      ? intval($ligne['quantite'])        : 0;
        $total_ht += $pu * $qte;
    }
    $tva       = $total_ht * TVA;
    $total_ttc = $total_ht + $tva;

    return [
        'total_ht'  => round($total_ht,  2),
        'tva'       => round($tva,        2),
        'total_ttc' => round($total_ttc,  2),
    ];
}
