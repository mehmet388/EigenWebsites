<?php
session_start();
require 'dbcon.php';

// Check admin
if (!isset($_SESSION['user']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

$error = '';
$editMode = false;
$editQuestion = null;

// Handle Delete Question
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db_connection->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}

// Handle Edit request (question)
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db_connection->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt->execute([$id]);
    $editQuestion = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($editQuestion) {
        $editMode = true;
    } else {
        $error = "Question not found.";
    }
}

// Handle form submission (add or update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $hint = trim($_POST['hint'] ?? '');
    $roomId = (int)($_POST['roomId'] ?? 0);

    if ($question === '' || $answer === '' || $roomId <= 0) {
        $error = "Please fill in question, answer and a valid room ID.";
    } else {
        if (isset($_POST['id']) && $_POST['id'] !== '') {
            $id = (int)$_POST['id'];
            $update = $db_connection->prepare("UPDATE questions SET question = ?, answer = ?, hint = ?, roomId = ? WHERE id = ?");
            if ($update->execute([$question, $answer, $hint, $roomId, $id])) {
                header("Location: admin.php");
                exit;
            } else {
                $error = "Error updating the question.";
            }
        } else {
            $insert = $db_connection->prepare("INSERT INTO questions (question, answer, hint, roomId) VALUES (?, ?, ?, ?)");
            if ($insert->execute([$question, $answer, $hint, $roomId])) {
                header("Location: admin.php");
                exit;
            } else {
                $error = "Error adding the question.";
            }
        }
    }
}

// Fetch questions only
$questions = $db_connection->query("SELECT * FROM questions ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8" />
<title>Admin - Beheer</title>
<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1000px;
    margin: 30px auto;
    background-color: #f8f9fa;
    padding: 20px;
}

h1, h2 {
    text-align: center;
}

.error {
    background-color: #f8d7da;
    color: #842029;
    padding: 12px;
    border: 1px solid #f5c2c7;
    margin-bottom: 20px;
    border-radius: 5px;
}

form {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

form label {
    font-weight: bold;
    display: block;
    margin-top: 10px;
}

form input[type="text"],
form input[type="number"] {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border-radius: 4px;
    border: 1px solid #ccc;
}

form button {
    margin-top: 15px;
    background-color: #0c6efc;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

form button:hover {
    background-color: #094fc0;
}

table {
    width: 100%;
    background: white;
    border-collapse: collapse;
    margin-bottom: 40px;
    box-shadow: 0 0 8px rgba(0,0,0,0.1);
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #0c6efc;
    color: white;
}

.actions a {
    padding: 6px 12px;
    margin-right: 5px;
    text-decoration: none;
    color: white;
    border-radius: 5px;
    font-size: 0.9rem;
}

.actions .delete {
    background-color: #d9534f;
}

.actions .delete:hover {
    background-color: #b02a37;
}

.actions .edit {
    background-color: #198754;
}

.actions .edit:hover {
    background-color: #146c43;
}
</style>
</head>
<body>

<h1>Admin Panel</h1>

<?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<a href="index.php">← Terug naar Home</a>

<!-- Vraag toevoegen/bewerken -->
<form method="post" action="admin.php">
    <?php if ($editMode): ?>
        <input type="hidden" name="id" value="<?= (int)$editQuestion['id'] ?>">
    <?php endif; ?>

    <label for="question">Vraag:</label>
    <input type="text" id="question" name="question" required value="<?= $editMode ? htmlspecialchars($editQuestion['question']) : '' ?>">

    <label for="answer">Antwoord:</label>
    <input type="text" id="answer" name="answer" required value="<?= $editMode ? htmlspecialchars($editQuestion['answer']) : '' ?>">

    <label for="hint">Hint (optioneel):</label>
    <input type="text" id="hint" name="hint" value="<?= $editMode ? htmlspecialchars($editQuestion['hint']) : '' ?>">

    <label for="roomId">Kamer ID:</label>
    <input type="number" id="roomId" name="roomId" required min="1" value="<?= $editMode ? (int)$editQuestion['roomId'] : '' ?>">

    <button type="submit"><?= $editMode ? 'Update Vraag' : 'Voeg Vraag Toe' ?></button>
    <?php if ($editMode): ?>
        <a href="admin.php">Annuleer</a>
    <?php endif; ?>
</form>

<!-- Vraag Tabel -->
<h2>Vragenlijst</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Vraag</th>
            <th>Antwoord</th>
            <th>Hint</th>
            <th>Kamer ID</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($questions)): ?>
            <tr><td colspan="6">Geen vragen gevonden.</td></tr>
        <?php else: ?>
            <?php foreach ($questions as $q): ?>
            <tr>
                <td><?= $q['id'] ?></td>
                <td><?= htmlspecialchars($q['question']) ?></td>
                <td><?= htmlspecialchars($q['answer']) ?></td>
                <td><?= htmlspecialchars($q['hint']) ?></td>
                <td><?= $q['roomId'] ?></td>
                <td class="actions">
                    <a class="edit" href="admin.php?edit=<?= $q['id'] ?>">Bewerk</a>
                    <a class="delete" href="admin.php?delete=<?= $q['id'] ?>" onclick="return confirm('Weet je zeker dat je deze vraag wilt verwijderen?');">Verwijder</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
