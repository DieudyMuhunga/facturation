<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// L'utilisateur est considere connecte seulement si les 2 etapes sont validees
$pleinement_connecte = isset($_SESSION['user'])
                    && isset($_SESSION['2fa_valide'])
                    && $_SESSION['2fa_valide'] === true;

if (!$pleinement_connecte) {
    // Calculer le prefixe relatif vers auth/login.php
    $caller_depth = 0;
    if (isset($_SERVER['SCRIPT_FILENAME'])) {
        $script_dir  = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
        $project_dir = realpath(__DIR__ . '/..');
        if ($script_dir && $project_dir) {
            $relative     = str_replace($project_dir, '', $script_dir);
            $relative     = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative), DIRECTORY_SEPARATOR);
            $caller_depth = ($relative === '') ? 0 : substr_count($relative, DIRECTORY_SEPARATOR) + 1;
        }
    }
    $prefix = str_repeat('../', $caller_depth);
    header('Location: ' . $prefix . 'auth/login.php');
    exit;
}
