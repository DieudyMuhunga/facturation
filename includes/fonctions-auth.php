<?php
//  includes/fonctions-auth.php — Fonctions liées aux comptes

function lire_utilisateurs() {
    $contenu = file_get_contents(UTILISATEURS_FILE);
    $data    = json_decode($contenu, true);
    return is_array($data) ? $data : [];
}

function sauvegarder_utilisateurs(array $utilisateurs) {
    file_put_contents(UTILISATEURS_FILE, json_encode($utilisateurs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function identifiant_existe(array $utilisateurs, string $identifiant): bool {
    foreach ($utilisateurs as $u) {
        if ($u['identifiant'] === $identifiant) {
            return true;
        }
    }
    return false;
}

function est_super_admin(): bool {
    return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'super_admin';
}

// PERMISSIONS PAR RÔLE 
// Super Admin : toutes les permissions
// Manager : produits, stock, rapports, factures
// Caissier : codes-barres, factures

function peut_scanner_code_barre(): bool {
    $role = $_SESSION['user']['role'] ?? '';
    return in_array($role, ['caissier', 'manager', 'super_admin']);
}

function peut_creer_facture(): bool {
    $role = $_SESSION['user']['role'] ?? '';
    return in_array($role, ['caissier', 'manager', 'super_admin']);
}

function peut_consulter_facture(): bool {
    $role = $_SESSION['user']['role'] ?? '';
    return in_array($role, ['caissier', 'manager', 'super_admin']);
}

function peut_enregistrer_produits(): bool {
    $role = $_SESSION['user']['role'] ?? '';
    return in_array($role, ['manager', 'super_admin']);
}

function peut_modifier_stock(): bool {
    $role = $_SESSION['user']['role'] ?? '';
    return in_array($role, ['manager', 'super_admin']);
}

function peut_consulter_rapports(): bool {
    $role = $_SESSION['user']['role'] ?? '';
    return in_array($role, ['manager', 'super_admin']);
}

function peut_creer_comptes(): bool {
    return est_super_admin();
}

function peut_supprimer_comptes(): bool {
    return est_super_admin();
}
