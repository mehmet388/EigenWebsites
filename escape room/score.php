<?php
require 'dbcon.php';

$stmt = $pdo->query("
    SELECT teams.naam, scores.eindtijd 
    FROM scores 
    JOIN teams ON scores.team_id = teams.id 
    ORDER BY scores.eindtijd ASC
");
$scores = $stmt->fetchAll();
?>

<h2>Scoreoverzicht</h2>

<table border="1">
    <tr>
        <th>Team</th>
        <th>Eindtijd</th>
    </tr>
    <?php foreach ($scores as $score): ?>
    <tr>
        <td><?= htmlspecialchars($score['naam']) ?></td>
        <td><?= htmlspecialchars($score['eindtijd']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
