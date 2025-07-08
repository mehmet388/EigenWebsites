<?php
require_once('./dbcon.php');

if (!isset($_POST['answers']) || !is_array($_POST['answers'])) {
  header('Location: verliesscherm.php');
  exit;
}

$userAnswers = $_POST['answers'];
$ids = implode(',', array_map('intval', array_keys($userAnswers)));

try {
  $stmt = $db_connection->query("SELECT id, answer FROM questions WHERE id IN ($ids)");
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Databasefout: " . $e->getMessage());
}

// Check of elk antwoord juist is
foreach ($rows as $row) {
  $id = $row['id'];
  $correct = strtolower(trim($row['answer']));
  $given = strtolower(trim($userAnswers[$id]));
  if ($correct !== $given) {
    header('Location: verliesscherm.php');
    exit;
  }
}

// Alles goed => door naar kamer 2 of win
header('Location: winst.php'); // of room2.php
exit;
