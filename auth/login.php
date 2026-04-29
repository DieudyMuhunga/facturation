<?php
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', 0);
session_start();

require_once("../includes/fonctions-auth.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"] ?? '');
    $password = trim($_POST["password"] ?? '');

    $users = lireUtilisateurs();
    $found = false;

    foreach ($users as $user) {
        if ($user["username"] === $username && $user["password"] === $password) {
            $found = true;
            break;
        }
    }

    if ($found) {

        $otp = rand(1000, 9999);

        $_SESSION["tmp_user"] = $username;
        $_SESSION["otp"] = $otp;

        header("Location: verify.php ?otp=" . $otp . "&user=" . $username);
        exit;

    } else {
        $erreur = "Identifiants incorrects";
    }
}
?>

<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Login</title></head>
<link rel="stylesheet" href="../css/style.css">
<body>

<h2>Connexion</h2>

<?php if (isset($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>

<form method="POST">
    Username : <input type="text" name="username" required><br><br>
    Password : <input type="password" name="password" required><br><br>
    <button type="submit">Se connecter</button>
</form>

</body>
</html>