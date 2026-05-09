<?php
// Chemins absolus vers les fichiers JSON
define('UTILISATEURS_FILE', __DIR__ . '/../data/utilisateurs.json');
define('PRODUITS_FILE',     __DIR__ . '/../data/produits.json');
define('FACTURES_FILE',     __DIR__ . '/../data/factures.json');

// Taux de TVA
define('TVA', 0.18);

// Duree de validite du code 2FA en secondes (5 minutes)
define('CODE_2FA_EXPIRATION', 300);

// Racine absolue du projet
define('ROOT_PATH', realpath(__DIR__ . '/..'));
