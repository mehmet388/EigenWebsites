<?php
//  Verbind met database
require_once('./dbcon.php');

try {
  //  Haal alle vragen op voor roomId = 1
  $stmt = $db_connection->query("SELECT * FROM questions WHERE roomId = 1");
  $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  //  Stop script als er een fout is bij de database
  die("Databasefout: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Escape Room 1</title>
  <style>
    /*  Algemene opmaak en achtergrond */
    body {
      margin: 0;
      padding: 0;
      background-image: url('img/wereldkaart1_orig.png');
      background-size: cover;
      background-position: center 0%;
      background-repeat: no-repeat;
      font-family: Arial, sans-serif;
      overflow: hidden;
    }

    /*  Timer stijl linksboven */
    #timer {
      position: absolute;
      top: 20px;
      left: 20px;
      font-size: 24px;
      font-weight: bold;
      color: red;
      text-shadow: 1px 1px 4px black;
      z-index: 100;
    }

    /*  Instructietekst bovenaan */
    h2 {
      position: absolute;
      top: 10%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 400px;
      background-color: rgba(0, 0, 0, 0.7);
      border: 2px solid gold;
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      color: white;
      box-shadow: 0 0 20px rgba(0,0,0,0.8);
      z-index: 90;
    }

    /*  Verborgen boxen die klikbaar zijn */
    .hidden-box {
      position: absolute;
      opacity: 0;
      width: 80px;
      height: 80px;
      cursor: pointer;
      z-index: 50;
    }

    /*  Box zichtbaar na klik */
    .hidden-box.revealed {
      opacity: 10;
      background-color: #fff;
      border: 2px solid #b7d539;
      border-radius: 5px;
      text-align: center;
      line-height: 80px;
      font-weight: bold;
      color: #000;
    }

    /*  Donkere overlay bij vraag */
    .overlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background: rgba(0, 0, 0, 0.6);
      z-index: 100;
    }

    /*  Modal (vraag + inputveld) */
    .modal {
      display: none;
      position: fixed;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      background: #fff;
      color: #000;
      padding: 20px;
      border-radius: 10px;
      z-index: 101;
      width: 300px;
      box-shadow: 0 0 20px black;
      text-align: center;
    }

    .modal input {
      width: 90%;
      padding: 8px;
      margin-top: 10px;
      margin-bottom: 10px;
    }

    .modal button {
      padding: 10px 20px;
      background-color: #b7d539;
      border: none;
      border-radius: 5px;
      color: white;
      font-weight: bold;
      cursor: pointer;
    }

    .modal button:hover {
      background-color: #9cb230;
    }

    /*  Winnende scherm na alle goede antwoorden */
    #winScreen {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background: rgba(0, 0, 0, 0.85);
      color: #fff;
      font-size: 2em;
      text-align: center;
      padding-top: 20vh;
      z-index: 2000;
    }

    #winScreen button {
      margin-top: 30px;
      padding: 12px 30px;
      font-size: 1em;
      background-color: #b7d539;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      color: #000;
      font-weight: bold;
      transition: background-color 0.3s;
    }

    #winScreen button:hover {
      background-color: #9cb230;
    }
  </style>
</head>

<body>

<!-- Timer zichtbaar linksboven -->
<div id="timer">⏱️ Tijd over: 60s</div>

<!-- Instructietekst boven de kaart -->
<div class="center-box">
  <h2>Raad de hoofdsteden van de landen</h2>

  <!--  Dynamisch gegenereerde boxen uit database -->
  <div class="container">
    <?php foreach ($questions as $index => $question) : ?>
      <div class="hidden-box box<?php echo $index + 1; ?>"
           style="top: <?php echo 20 + $index * 10; ?>%; left: <?php echo 30 + $index * 15; ?>%;"
           onclick="revealBox(this, <?php echo $index; ?>)"
           data-question="<?php echo htmlspecialchars($question['question']); ?>"
           data-answer="<?php echo htmlspecialchars($question['answer']); ?>">
        Box <?php echo $index + 1; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!--  Vraag overlay + modaal -->
<section class="overlay" id="overlay" onclick="closeModal()"></section>
<section class="modal" id="modal">
  <p id="question"></p>
  <input type="text" id="answer" placeholder="Typ je antwoord" />
  <button onclick="checkAnswer()">Verzenden</button>
  <p id="feedback"></p>
</section>

<!--  Win scherm -->
<div id="winScreen">
  🎉 Je hebt Room 1 verslagen! 🎉<br />
  Je gaat nu door naar Room 2.<br />
  <button id="continueBtn">Doorgaan</button>
</div>

<!--  JavaScript spelmechaniek -->
<script>
  let correctAnswers = 0;                 //  Telt juiste antwoorden
  let answeredBoxes = new Set();          //  Voorkomt dubbele antwoorden
  let timeLeft = 60;                      //  Starttijd in seconden
  const totalQuestions = <?php echo count($questions); ?>;  //  Totaal aantal vragen

  //  Timer die elke seconde aftelt
  function updateTimer() {
    if (timeLeft <= 0) {
      window.location.href = "verliesscherm.php"; // Tijd op = verloren
      return;
    }
    document.getElementById("timer").innerText = "⏱️ Tijd over: " + timeLeft + "s";
    timeLeft--;
  }
  setInterval(updateTimer, 1000);

  //  Toon vraagmodaal bij klikken op box
  function revealBox(element, index) {
    if (!element.classList.contains('revealed')) {
      element.classList.add('revealed');
    }
    openModal(index);
  }

  //  Modal openen met vraag
  function openModal(index) {
    const box = document.querySelector(`.box${index + 1}`);
    const questionText = box.dataset.question;

    document.getElementById('question').textContent = questionText;
    document.getElementById('answer').value = '';
    document.getElementById('feedback').textContent = '';

    document.getElementById('overlay').style.display = 'block';
    document.getElementById('modal').style.display = 'block';

    document.getElementById('modal').dataset.correctAnswer = box.dataset.answer;
    document.getElementById('modal').dataset.boxIndex = index;
  }

  //  Sluit de modal
  function closeModal() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('modal').style.display = 'none';
  }

  //  Controleer antwoord
  function checkAnswer() {
    const userAnswer = document.getElementById('answer').value.trim().toLowerCase();
    const correctAnswer = document.getElementById('modal').dataset.correctAnswer.toLowerCase();
    const boxIndex = document.getElementById('modal').dataset.boxIndex;
    const feedback = document.getElementById('feedback');

    if (userAnswer === correctAnswer) {
      //  Juist antwoord
      if (!answeredBoxes.has(boxIndex)) {
        correctAnswers++;
        answeredBoxes.add(boxIndex);
      }

      feedback.textContent = ' Correct!';
      feedback.style.color = 'green';

      setTimeout(() => {
        closeModal();

        //  Check of spel voltooid is
        if (correctAnswers === totalQuestions) {
          showWinScreen();
        }
      }, 1000);
    } else {
      //  Fout antwoord → verlies
      closeModal();
      showLossScreen();
      setTimeout(() => {
        window.location.href = 'index.php';
      }, 3000);
    }
  }

  //  Toon win scherm
  function showWinScreen() {
    document.getElementById('winScreen').style.display = 'block';
  }

  //  Toon verlies overlay
  function showLossScreen() {
    let lossOverlay = document.createElement('div');
    lossOverlay.id = 'lossScreen';
    lossOverlay.style.position = 'fixed';
    lossOverlay.style.width = '100vw';
    lossOverlay.style.height = '100vh';
    lossOverlay.style.background = 'rgba(0,0,0,0.85)';
    lossOverlay.style.color = '#fff';
    lossOverlay.style.fontSize = '2em';
    lossOverlay.style.display = 'flex';
    lossOverlay.style.justifyContent = 'center';
    lossOverlay.style.alignItems = 'center';
    lossOverlay.style.zIndex = 2000;
    lossOverlay.textContent = '❌ Helaas, fout antwoord! Je gaat terug naar het begin.';
    document.body.appendChild(lossOverlay);
  }

  //  Ga naar Room 2 bij klik op knop
  document.getElementById('continueBtn').addEventListener('click', () => {
    window.location.href = 'room_2.php';
  });
</script>
</body>
</html>
