<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/config.php';

// Si pas en attente de 2FA, renvoyer au login
if (!isset($_SESSION['2fa_user']) || !isset($_SESSION['2fa_code'])) {
    header('Location: login.php');
    exit;
}

// Si deja valide, aller a l'accueil
if (isset($_SESSION['2fa_valide']) && $_SESSION['2fa_valide'] === true) {
    header('Location: ../index.php');
    exit;
}

// Verifier expiration
$expire = isset($_SESSION['2fa_expire']) ? $_SESSION['2fa_expire'] : 0;
$reste  = $expire - time();

if ($reste <= 0) {
    // Code expire : repartir de zero
    session_unset();
    header('Location: login.php?erreur=expire');
    exit;
}

$code_attendu = $_SESSION['2fa_code'];
$user         = $_SESSION['2fa_user'];
$erreur       = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code_saisi = isset($_POST['code']) ? trim($_POST['code']) : '';

    if ($code_saisi === '') {
        $erreur = 'Veuillez saisir le code.';
    } elseif ($code_saisi !== $code_attendu) {
        $erreur = 'Code incorrect. Veuillez reessayer.';
    } else {
        // Validation reussie
        $_SESSION['user']       = $user;
        $_SESSION['2fa_valide'] = true;

        // Nettoyer les donnees temporaires 2FA
        unset($_SESSION['2fa_user']);
        unset($_SESSION['2fa_code']);
        unset($_SESSION['2fa_expire']);

        header('Location: ../index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification — Double Authentification</title>
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
            max-width: 440px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,.25);
            padding: 40px;
        }
        .logo { text-align: center; font-size: 40px; margin-bottom: 6px; }
        h1 { text-align: center; font-size: 18px; color: #1e3a5f; margin-bottom: 6px; }
        .subtitle { text-align: center; font-size: 13px; color: #6b7280; margin-bottom: 28px; }

        /* Bandeau code */
        .code-box {
            background: linear-gradient(135deg, #1e3a5f, #2563eb);
            border-radius: 10px;
            padding: 22px 20px;
            text-align: center;
            margin-bottom: 24px;
            position: relative;
        }
        .code-box .label {
            font-size: 12px;
            color: rgba(255,255,255,.75);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .code-box .code {
            font-size: 40px;
            font-weight: 900;
            letter-spacing: 10px;
            color: #fff;
            font-family: 'Courier New', monospace;
        }
        .code-box .timer {
            margin-top: 12px;
            font-size: 12px;
            color: rgba(255,255,255,.7);
        }
        .code-box .timer span {
            font-weight: bold;
            color: #fbbf24;
        }

        /* Separateur */
        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
            color: #9ca3af;
            font-size: 12px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        label { display: block; font-size: 13px; color: #555; margin-bottom: 6px; }

        .input-code {
            width: 100%;
            padding: 14px;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 8px;
            text-align: center;
            font-family: 'Courier New', monospace;
            margin-bottom: 18px;
            transition: border-color .2s;
        }
        .input-code:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }

        button {
            width: 100%;
            padding: 13px;
            background: #16a34a;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }
        button:hover { background: #15803d; }

        .erreur {
            background: #fee2e2;
            color: #b91c1c;
            padding: 11px 14px;
            border-radius: 7px;
            font-size: 13px;
            margin-bottom: 18px;
        }
        .retour {
            display: block;
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: #6b7280;
            text-decoration: none;
        }
        .retour:hover { color: #2563eb; }
        .user-info {
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 20px;
        }
        .user-info strong { color: #1e3a5f; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">🔐</div>
    <h1>Double Authentification</h1>
    <p class="subtitle">Verification en deux etapes</p>

    <p class="user-info">
        Bienvenue <strong><?= htmlspecialchars($user['nom_complet']) ?></strong><br>
        Entrez le code affiche ci-dessous pour continuer.
    </p>

    <!-- Bandeau d'affichage du code -->
    <div class="code-box">
        <div class="label">Votre code de verification</div>
        <div class="code"><?= htmlspecialchars($code_attendu) ?></div>
        <div class="timer">
            Valide pendant <span id="countdown"><?= $reste ?></span> secondes
        </div>
    </div>

    <div class="divider">Saisissez ce code ci-dessous</div>

    <?php if ($erreur !== ''): ?>
        <div class="erreur">⚠️ <?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="code">Code a 6 chiffres</label>
        <input class="input-code" type="text" id="code" name="code"
               maxlength="6" pattern="[0-9]{6}" inputmode="numeric"
               placeholder="000000" autofocus autocomplete="off" required>
        <button type="submit">✅ Valider et acceder</button>
    </form>

    <a class="retour" href="login.php">← Retour a la page de connexion</a>
</div>

<script>
// Compte a rebours
var remaining = <?= $reste ?>;
var countdown = document.getElementById('countdown');
var interval  = setInterval(function() {
    remaining--;
    if (remaining <= 0) {
        clearInterval(interval);
        countdown.textContent = '0';
        // Recharger pour laisser le serveur gerer l'expiration
        window.location.href = 'login.php?erreur=expire';
    } else {
        countdown.textContent = remaining;
        if (remaining <= 30) {
            countdown.style.color = '#ef4444';
        }
    }
}, 1000);
</script>
</body>
</html>
