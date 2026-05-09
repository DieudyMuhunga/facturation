<?php
//  includes/fonctions-produits.php — Fonctions produits

function lire_produits(): array {
    $contenu = file_get_contents(PRODUITS_FILE);
    $data    = json_decode($contenu, true);
    return is_array($data) ? $data : [];
}

function sauvegarder_produits(array $produits): void {
    file_put_contents(PRODUITS_FILE, json_encode($produits, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function trouver_produit_par_code(array $produits, string $code): ?array {
    foreach ($produits as $p) {
        if (isset($p['code_barre']) && $p['code_barre'] === $code) {
            return $p;
        }
    }
    return null;
}

function code_barre_existe(array $produits, string $code): bool {
    return trouver_produit_par_code($produits, $code) !== null;
}

function mettre_a_jour_stock(array &$produits, string $code, int $quantite_vendue): bool {
    foreach ($produits as &$p) {
        if (isset($p['code_barre']) && $p['code_barre'] === $code) {
            if ($p['quantite'] < $quantite_vendue) {
                return false; // stock insuffisant
            }
            $p['quantite'] -= $quantite_vendue;
            return true;
        }
    }
    return false;
}

function deduire_stock(string $code, int $quantite): bool {
    $produits = lire_produits();
    $ok = mettre_a_jour_stock($produits, $code, $quantite);
    if ($ok) {
        sauvegarder_produits($produits);
    }
    return $ok;
}
