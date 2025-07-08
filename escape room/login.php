<?php
session_start();
require './dbcon.php';

// Als gebruiker al is ingelogd, stuur direct door naar index
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $naam = trim($_POST['naam']);
    $wachtwoord = $_POST['wachtwoord'];

    if (empty($naam) || empty($wachtwoord)) {
        $error = "Vul zowel naam als wachtwoord in.";
    } else {
        $stmt = $db_connection->prepare("SELECT * FROM profile WHERE naam = ?");
        $stmt->execute([$naam]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $wachtwoord === $user['wachtwoord']) {
            $_SESSION['user'] = $user['naam'];
            $_SESSION['is_admin'] = ($user['naam'] === 'admin');

            header("Location: index.php");
            exit;
        } else {
            $error = "Ongeldige gebruikersnaam of wachtwoord.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <title>Inloggen</title>
   <link rel="stylesheet" href="style.css">
  <style>
    body {
  margin: 0;
  font-family: 'Orbitron', sans-serif;
  background: linear-gradient(to right, #1a1a1a, #333333);
  color: #f0f0f0;
  height: 100vh;
  position: relative; /* Belangrijk! */
}

.topbar {
  position: absolute;
  top: 20px;
  right: 30px;
  z-index: 9999;
  background-color: rgba(0, 0, 0, 0.7); /* optioneel: geeft zichtbare achtergrond */
  padding: 10px 15px;
  border-radius: 8px;
}

.topbar a {
  margin-left: 15px;
  color: #f39c12;
  font-weight: bold;
  text-decoration: none;
}

.topbar a:hover {
  text-decoration: underline;
  color: #fff;
}


  a {
    color: black;
    text-decoration: none;
  }
  
  .container {
    text-align: center;
    padding: 2rem;
    background-color: rgba(0, 0, 0, 0.6);
    border: 2px solid #f39c12;
    border-radius: 16px;
    box-shadow: 0 0 20px rgba(243, 156, 18, 0.6);
  }

  nav img {
    width: 170px;
    height: 85px;
  }

  h1 {
    font-size: 3rem;
    color: #f39c12;
    margin-bottom: 1rem;
  }
  
  .box {
    width: 100px;
    height: 100px;
    background-color: #c8db34;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-weight: bold;
    border-radius: 8px;
  }
  
  .box:hover {
    background-color: #2980b9;
  }
  
  .overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
  }
  
  .modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 20px;
    border-radius: 10px;
    display: none;
    width: 300px;
  }
  
  button {
    margin-top: 10px;
    padding: 5px 10px;
    cursor: pointer;
  }

  .start-button {
    display: inline-block;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    color: #fff;
    background-color: #e67e22;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    transition: background-color 0.3s ease;
  }
  
  .start-button:hover {
    background-color: #d35400;

    .end-screen {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: #111;
  color: white;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  font-size: 2em;
}

.hidden {
  display: none;
}

  }

  </style>
</head>
<body>

  <h2>Inloggen</h2>

  <?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <label>Naam:</label>
    <input type="text" name="naam" required>

    <label>Wachtwoord:</label>
    <input type="password" name="wachtwoord" required>

    <button type="submit">Inloggen</button>
  </form>

  <div class="register-link">
    Nog geen account? <a href="register.php">Registreer hier</a>
  </div>

</body>
</html>

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


<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body {
      margin: 0;
      padding: 0;
      background-image: url('img/Schermafbeelding 2025-06-24 133424.png');
      background-size: cover;
      background-position: center 47%;
      background-repeat: no-repeat;
      font-family: Arial, sans-serif;
      overflow: hidden;
    }


    .overlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background: rgba(0, 0, 0, 0.6);
      z-index: 100;
    }
</style>
   
</body>
</html>
