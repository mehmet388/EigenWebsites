<?php
require 'dbcon.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $naam = trim($_POST['naam']);
    $wachtwoord = $_POST['wachtwoord'];

    if (empty($naam) || empty($wachtwoord)) {
        $error = "Vul alle velden in.";
    } else {
        // Controleer of de gebruiker al bestaat
        $stmt = $db_connection->prepare("SELECT * FROM profile WHERE naam = ?");
        $stmt->execute([$naam]);

        if ($stmt->rowCount() > 0) {
            $error = "Naam is al in gebruik. Kies een andere.";
        } else {
            // Hash wachtwoord en sla op
$hashedWachtwoord = $wachtwoord; // sla letterlijk op wat de gebruiker intypt


            $insert = $db_connection->prepare("INSERT INTO profile (naam, wachtwoord) VALUES (?, ?)");
            $insert->execute([$naam, $hashedWachtwoord]);

            header("Location: login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Registreren</title>
</head>
<body>
  <h2>Registreren</h2>
  <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
  <form method="post">
    <label>Naam:</label><br>
    <input type="text" name="naam" required><br><br>

    <label>Wachtwoord:</label><br>
    <input type="password" name="wachtwoord" required><br><br>

    <button type="submit">Registreren</button>
  </form>
  <p>Heb je al een account? <a href="login.php">Inloggen</a></p>
</body>
</html>