<?php
require_once '../../auth/session.php';
require_once '../../config/config.php';
require_once '../../includes/fonctions-auth.php';

if (!est_super_admin()) {
    header('Location: ../../index.php');
    exit;
}

$identifiant = isset($_GET['identifiant']) ? trim($_GET['identifiant']) : '';

if ($identifiant === '' || $identifiant === 'admin') {
    header('Location: gestion-comptes.php?msg=Suppression+impossible');
    exit;
}

$utilisateurs = lire_utilisateurs();
$nouveaux     = [];

foreach ($utilisateurs as $u) {
    if ($u['identifiant'] !== $identifiant) {
        $nouveaux[] = $u;
    }
}

sauvegarder_utilisateurs($nouveaux);

header('Location: gestion-comptes.php?msg=Compte+supprimé');
exit;
