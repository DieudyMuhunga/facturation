<?php
/**
 * auth/login.php
 * Etape 1 du flux de connexion : verification identifiant + mot de passe.
 * Si OK -> redirection vers verify-2fa.php (etape 2).
 *
 * IMPORTANT : config.php est charge EN PREMIER afin que toutes les constantes
 * (dont CODE_2FA_EXPIRATION) soient connues immediatement.
 * Cela supprime aussi le soulignement rouge dans VSCode.
 */

require_once '../config/config.php';
if (!defined('CODE_2FA_EXPIRATION')) { define('CODE_2FA_EXPIRATION', 300); }

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Deja pleinement connecte (les 2 etapes validees)
if (isset($_SESSION['user'])
    && isset($_SESSION['2fa_valide'])
    && $_SESSION['2fa_valide'] === true) {
    header('Location: ../index.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $identifiant  = isset($_POST['identifiant'])  ? trim($_POST['identifiant'])  : '';
    $mot_de_passe = isset($_POST['mot_de_passe']) ? trim($_POST['mot_de_passe']) : '';

    if ($identifiant === '' || $mot_de_passe === '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $contenu      = file_get_contents(UTILISATEURS_FILE);
        $utilisateurs = json_decode($contenu, true);
        if (!is_array($utilisateurs)) {
            $utilisateurs = [];
        }

        $trouve = false;

        foreach ($utilisateurs as $u) {
            if ($u['identifiant'] === $identifiant && $u['mot_de_passe'] === $mot_de_passe) {

                // Compte desactive ?
                if (isset($u['actif']) && $u['actif'] === false) {
                    $erreur = 'Ce compte est desactive.';
                    break;
                }

                // Identifiants corrects : preparer la session 2FA
                $_SESSION['2fa_user']   = $u;
                $_SESSION['2fa_code']   = strval(rand(100000, 999999));
                $_SESSION['2fa_expire'] = time() + CODE_2FA_EXPIRATION;  // constante definie dans config.php
                $_SESSION['2fa_valide'] = false;

                $trouve = true;
                break;
            }
        }

        if ($trouve && $erreur === '') {
            header('Location: verify-2fa.php');
            exit;
        } elseif (!$trouve && $erreur === '') {
            $erreur = 'Identifiant ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Facturation</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #fff;
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,.25);
            padding: 44px 40px 36px;
        }
        .logo { text-align: center; font-size: 42px; margin-bottom: 6px; }
        h1    { text-align: center; font-size: 20px; color: #1e3a5f; margin-bottom: 28px; }

        label {
            display: block;
            font-size: 13px;
            color: #555;
            margin-bottom: 5px;
            font-weight: 600;
        }
        input[type=text],
        input[type=password] {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 15px;
            margin-bottom: 18px;
            transition: border-color .2s, box-shadow .2s;
        }
        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }
        button {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 7px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }
        button:hover { background: #1d4ed8; }
        .erreur {
            background: #fee2e2;
            color: #b91c1c;
            padding: 11px 14px;
            border-radius: 7px;
            font-size: 13px;
            margin-bottom: 18px;
            border-left: 3px solid #b91c1c;
        }
        .security-note {
            text-align: center;
            margin-top: 18px;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">🧾</div>
    <h1>Système de Facturation</h1>

    <?php if ($erreur !== ''): ?>
        <div class="erreur">⚠️ <?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="identifiant">Identifiant</label>
        <input type="text"
               id="identifiant"
               name="identifiant"
               autofocus
               required
               autocomplete="username"
               value="<?= htmlspecialchars(isset($_POST['identifiant']) ? $_POST['identifiant'] : '') ?>">

        <label for="mot_de_passe">Mot de passe</label>
        <input type="password"
               id="mot_de_passe"
               name="mot_de_passe"
               required
               autocomplete="current-password">

        <button type="submit">Se connecter</button>
    </form>

    <p class="security-note">🔒 Connexion sécurisée à double facteur</p>
</div>
</body>
</html>
