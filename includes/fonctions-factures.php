<?php

function lireFactures() {
    $fichier = __DIR__ . "/../data/factures.json";

    if (!file_exists($fichier)) return [];

    $data = file_get_contents($fichier);
    return json_decode($data, true) ?? [];
}

function enregistrerFacture($facture) {
    $factures = lireFactures();
    $factures[] = $facture;

    file_put_contents(
        __DIR__ . "/../data/factures.json",
        json_encode($factures, JSON_PRETTY_PRINT)
    );
}
?>