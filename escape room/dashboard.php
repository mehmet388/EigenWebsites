<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login_register.php');
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"><title>Dashboard</title></head>
<body>
  <h1>Welkom, <?= htmlspecialchars($user['username']) ?> (<?= $user['role'] ?>)</h1>

  <ul>
    <?php if ($user['role'] === 'admin'): ?>
      <li><a href="crud_teams.php">Beheer Teams</a></li>
      <li><a href="crud_questions.php">Beheer Vragen</a></li>
    <?php endif; ?>
    <li><a href="scoreboard.php">Bekijk Scorebord</a></li>
    <li><a href="logout.php">Uitloggen</a></li>
  </ul>
</body>
</html>
