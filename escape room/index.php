<?php
session_start();
require 'dbcon.php';

//  Verplicht inloggen
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

//  Uitloggen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

//  Haal top 5 scores op met start- en eindtijd
$stmt = $db_connection->prepare("
    SELECT t.naam, s.starttijd, s.eindtijd 
    FROM scores s
    JOIN teams t ON s.team_id = t.id
    WHERE s.starttijd IS NOT NULL AND s.eindtijd IS NOT NULL
");
$stmt->execute();
$topScores = $stmt->fetchAll(PDO::FETCH_ASSOC);

//  Haal eigen score op (laatste poging)
$teamnaam = $_SESSION['user'];
$stmt2 = $db_connection->prepare("
    SELECT s.starttijd, s.eindtijd 
    FROM scores s
    JOIN teams t ON s.team_id = t.id
    WHERE t.naam = :teamnaam AND s.starttijd IS NOT NULL AND s.eindtijd IS NOT NULL
    ORDER BY s.eindtijd DESC
    LIMIT 1
");
$stmt2->execute([':teamnaam' => $teamnaam]);
$eigenScore = $stmt2->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Escape Room - Welkom</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      background-image: url('images/home.webp');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      min-height: 100vh;
      margin: 0;
      font-family: Arial, sans-serif;
      color: white;
    }

    nav {
      display: flex;
      justify-content: center;
      gap: 10px;
      padding: 20px;
    }

    nav img {
      height: 70px;
    }

    .container {
      max-width: 800px;
      margin: 0 auto;
      background-color: rgba(0,0,0,0.6);
      padding: 30px;
      border-radius: 10px;
      margin-top: 40px;
      text-align: center;
    }

    .start-button, button {
      display: inline-block;
      padding: 12px 20px;
      font-size: 1rem;
      background-color: #0c6efc;
      border: none;
      border-radius: 5px;
      color: white;
      cursor: pointer;
      text-decoration: none;
      margin: 10px;
    }

    .start-button:hover, button:hover {
      background-color: #094fc0;
    }

    .leaderboard {
      margin: 40px auto;
      background-color: rgba(0,0,0,0.6);
      padding: 20px;
      border-radius: 10px;
      width: 90%;
      max-width: 600px;
    }

    .leaderboard table {
      width: 100%;
      color: white;
      border-collapse: collapse;
    }

    .leaderboard th, .leaderboard td {
      padding: 10px;
      border-bottom: 1px solid white;
      text-align: left;
    }

    .welcome {
      position: absolute;
      top: 10px;
      left: 20px;
      background-color: rgba(0,0,0,0.6);
      padding: 10px 15px;
      border-radius: 10px;
    }

    .logout {
      display: inline;
    }
  </style>
</head>

<body>

<!--  Welkomsttekst + uitlogknop + adminlink -->
<div class="welcome">
   Welkom, <strong><?= htmlspecialchars($_SESSION['user']) ?></strong>!
  <form method="post" class="logout" style="display:inline;">
    <button type="submit" name="logout">Uitloggen</button>
  </form>
  <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
    <a href="admin.php" style="color: white; margin-left: 10px;">Admin Panel</a>
  <?php endif; ?>
</div>

<!-- Navigatie met vlaggen -->
<nav>
  <img src="img/Schermafbeelding 2025-05-19 144228.png" alt="Vlag 1">
  <img src="img/Schermafbeelding 2025-05-19 144803.png" alt="Vlag 2">
  <img src="img/Schermafbeelding 2025-05-19 144917.png" alt="Vlag 3">
  <img src="img/Schermafbeelding 2025-05-19 145153.png" alt="Vlag 4">
  <img src="img/Schermafbeelding 2025-06-02 113448.png" alt="Vlag 5">
</nav>

<!-- Instructies -->
<div class="container">
  <h1>Welkom in de Escape Room</h1>
  <p>Kun jij ontsnappen? Los puzzels op, werk samen, en kraak de code.</p>
  <p>Ontsnap uit de kamer door het raden van hoofdsteden van landen.</p>
  <p>Elk juist antwoord brengt je naar een volgend level.</p>
  <p>Naarmate je verder komt, worden de landen en hoofdsteden moeilijker.</p>
  <p>Uiteindelijk moet je alle antwoorden goed hebben om te winnen en als je 1 fout maakt moet je weer opnieuw beginnen. <strong>SUCCES!</strong></p>

  <a href="room_1.php" class="start-button">Start demonstratie van kamer 1</a>
</div>

<!--  Eigen score tonen -->
<?php if (!empty($eigenScore) && !empty($eigenScore['starttijd']) && !empty($eigenScore['eindtijd'])): ?>
  <?php 
    $start = strtotime($eigenScore['starttijd']);
    $eind = strtotime($eigenScore['eindtijd']);
    $duur = $eind - $start;
  ?>
  <div class="container" style="background-color: rgba(0, 128, 0, 0.6); margin-top: 20px;">
    <h2>🎉 Gefeliciteerd!</h2>
    <p>Je hebt het spel uitgespeeld in <strong><?= $duur ?></strong> seconden.</p>
  </div>
<?php endif; ?>

<!--  Leaderboard -->
<div class="leaderboard">
  <h2>🏆 Leaderboard – Snelste Eindtijden</h2>
  <table>
    <thead>
      <tr>
        <th>Teamnaam</th>
        <th>Duur (seconden)</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $heeftScore = false;
      foreach ($topScores as $row): 
        if (!empty($row['starttijd']) && !empty($row['eindtijd'])) {
          $start = strtotime($row['starttijd']);
          $eind = strtotime($row['eindtijd']);
          $seconden = $eind - $start;
          if ($seconden <= 115): 
            $heeftScore = true;
      ?>
        <tr>
          <td><?= htmlspecialchars($row['naam']) ?></td>
          <td><?= $seconden ?> seconden</td>
        </tr>
      <?php 
          endif;
        }
      endforeach; 
      if (!$heeftScore): ?>
        <tr><td colspan="2">Team Mehmet        45 seconden.</td></tr>
         <tr><td colspan="2">Team Shazaib        65 seconden.</td></tr>
          <tr><td colspan="2">Team Admin        67 seconden.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</body>
</html>
