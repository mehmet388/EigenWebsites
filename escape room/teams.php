<?php
session_start();
require 'dbcon.php';

// Alleen admins mogen dit doen
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$error = '';
$editMode = false;
$editTeam = null;

// Verwijderen
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db_connection->prepare("DELETE FROM teams WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: teams.php");
    exit;
}

// Bewerken
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db_connection->prepare("SELECT * FROM teams WHERE id = ?");
    $stmt->execute([$id]);
    $editTeam = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($editTeam) {
        $editMode = true;
    } else {
        $error = "Team niet gevonden.";
    }
}

// Toevoegen of bijwerken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam = trim($_POST['naam'] ?? '');

    if ($naam === '') {
        $error = "Teamnaam is verplicht.";
    } else {
        if (isset($_POST['id']) && $_POST['id'] !== '') {
            $id = (int)$_POST['id'];
            $stmt = $db_connection->prepare("UPDATE teams SET naam = ? WHERE id = ?");
            $stmt->execute([$naam, $id]);
            header("Location: teams.php");
            exit;
        } else {
            $stmt = $db_connection->prepare("INSERT INTO teams (naam, created_at) VALUES (?, CURTIME())");
            $stmt->execute([$naam]);
            header("Location: teams.php");
            exit;
        }
    }
}

// Teamlijst ophalen met evt. scores
$stmt = $db_connection->query("
    SELECT t.*, s.eindtijd
    FROM teams t
    LEFT JOIN scores s ON t.id = s.team_id
    ORDER BY t.id
");
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Teambeheer</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 {
            text-align: center;
        }
        .error {
            background: #f8d7da;
            color: #842029;
            padding: 10px;
            border-radius: 5px;
        }
        form, table {
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #0c6efc;
            color: white;
        }
        .actions a {
            padding: 6px 10px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 5px;
            color: white;
        }
        .edit { background: #198754; }
        .delete { background: #d9534f; }
    </style>
</head>
<body>

<h1>Teams beheren</h1>

<?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<a href="index.php">← Terug naar start</a>

<form method="post" action="teams.php">
    <?php if ($editMode): ?>
        <input type="hidden" name="id" value="<?= (int)$editTeam['id'] ?>">
    <?php endif; ?>

    <label for="naam">Teamnaam:</label>
    <input type="text" name="naam" id="naam" required value="<?= $editMode ? htmlspecialchars($editTeam['naam']) : '' ?>">

    <button type="submit"><?= $editMode ? 'Update team' : 'Voeg team toe' ?></button>
    <?php if ($editMode): ?>
        <a href="teams.php" style="margin-left:10px;">Annuleer</a>
    <?php endif; ?>
</form>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Teamnaam</th>
            <th>Aangemaakt</th>
            <th>Eindtijd</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($teams)): ?>
            <tr><td colspan="5">Geen teams gevonden.</td></tr>
        <?php else: ?>
            <?php foreach ($teams as $team): ?>
                <tr>
                    <td><?= (int)$team['id'] ?></td>
                    <td><?= htmlspecialchars($team['naam']) ?></td>
                    <td><?= htmlspecialchars($team['created_at']) ?></td>
                    <td><?= htmlspecialchars($team['eindtijd'] ?? '-') ?></td>
                    <td class="actions">
                        <a class="edit" href="teams.php?edit=<?= (int)$team['id'] ?>">Bewerk</a>
                        <a class="delete" href="teams.php?delete=<?= (int)$team['id'] ?>" onclick="return confirm('Weet je zeker dat je dit team wilt verwijderen?');">Verwijder</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
