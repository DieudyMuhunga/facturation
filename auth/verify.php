<?php
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', 0);
session_start();

// DEBUG TEMPORAIRE
if (!isset($_SESSION["otp"])) {
    echo "<p style='color:red;'>Session OTP manquante</p>";
}

// Affichage OTP (test uniquement)
if (isset($_SESSION["otp"])) {
    echo "<p style='color:green;'>Code OTP : <b>" . $_SESSION["otp"] . "</b></p>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["otp"]) && isset($_SESSION["otp"])) {

        if ($_POST["otp"] == $_SESSION["otp"]) {

            $_SESSION["user"] = $_SESSION["tmp_user"];

            unset($_SESSION["otp"]);
            unset($_SESSION["tmp_user"]);

            header("Location: /facturation/index.php");
            exit;

        } else {
            $erreur = "Code incorrect";
        }

    } else {
        $erreur = "Session expirée";
    }
}
?>

<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>OTP</title></head>
<body>

<h2>Vérification OTP</h2>

<?php if (isset($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>

<form method="POST">
    Code OTP : <input type="text" name="otp" required><br><br>
    <button type="submit">Valider</button>
</form>

</body>
</html>