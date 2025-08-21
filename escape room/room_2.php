<?php
require_once('./dbcon.php');

//  Haal alle vragen op voor roomId 2 uit de database
try {
  $stmt = $db_connection->query("SELECT * FROM questions WHERE roomId = 2");
  $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  //  Foutmelding tonen als er iets misgaat met de database
  die("Databasefout: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Escape Room 2</title>
  <style>
    /*  Algemene body styling, achtergrond afbeelding en font */
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

    /*  Timer styling: rood, zichtbaar linksboven */
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

    /*  Titel bovenin midden met achtergrond en stijl */
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

    /*  Stijl voor de verborgen boxen */
    .hidden-box {
      position: absolute;
      opacity: 0;
      width: 80px;
      height: 80px;
      cursor: pointer;
      z-index: 50;
    }

    /*  Stijl voor onthulde boxen: zichtbaar en met kader */
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

    /*  Overlay achter modal: half-transparant zwart */
    .overlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background: rgba(0, 0, 0, 0.6);
      z-index: 100;
    }

    /*  Modal venster styling */
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

    /*  Input veld in modal */
    .modal input {
      width: 90%;
      padding: 8px;
      margin-top: 10px;
      margin-bottom: 10px;
    }

    /*  Verzenden knop in modal */
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

    /*  Win scherm styling */
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
  <!--  Timer zichtbaar bovenin links -->
  <div id="timer">⏱️ Tijd over: 55s</div>

  <div class="center-box">
    <h2>Raad de hoofdsteden van de landen<br>room 2 succes!!!</h2>
    <div class="container">

    <!--  Genereer boxen met willekeurige posities -->
    <?php foreach ($questions as $index => $question) : ?>
      <?php
        //  Willekeurige top en left positie tussen 10% en 80%
        $top = rand(10, 80);
        $left = rand(10, 80);
      ?>
      <div class="hidden-box box<?= $index + 1; ?>"
           style="top: <?= $top; ?>%; left: <?= $left; ?>%;"
           onclick="revealBox(this, <?= $index; ?>)"
           data-question="<?= htmlspecialchars($question['question']); ?>"
           data-answer="<?= htmlspecialchars($question['answer']); ?>">
        Box <?= $index + 1; ?>
      </div>
    <?php endforeach; ?>

    <!--  Overlay en modal voor vragen -->
    <section class="overlay" id="overlay" onclick="closeModal()"></section>
    <section class="modal" id="modal">
      <p id="question"></p>
      <input type="text" id="answer" placeholder="Typ je antwoord" />
      <button onclick="checkAnswer()">Verzenden</button>
      <p id="feedback"></p>
    </section>

    <!--  Win scherm -->
    <div id="winScreen">
      🎉 Gefeliciteerd je hebt het spel gewonnen je bent een kanjer 🎉<br />
      Je gaat nu terug naar homepagina.<br />
      <button id="continueBtn">Homepagina</button>
    </div>

<script>
  //  Teller van correcte antwoorden
  let correctAnswers = 0;
  //  Houd bij welke boxen al goed beantwoord zijn
  let answeredBoxes = new Set();
  //  Starttijd van de timer in seconden
  let timeLeft = 55;
  //  Totaal aantal vragen
  const totalQuestions = <?= count($questions); ?>;

  //  Update timer elke seconde en ga naar verliespagina als tijd op is
  function updateTimer() {
    if (timeLeft <= 0) {
      window.location.href = "verliesscherm.php";
      return;
    }
    document.getElementById("timer").innerText = "⏱️ Tijd over: " + timeLeft + "s";
    timeLeft--;
  }
  setInterval(updateTimer, 1000);

  //  Toon box visueel als onthuld en open modal met vraag
  function revealBox(element, index) {
    if (!element.classList.contains('revealed')) {
      element.classList.add('revealed');
    }
    openModal(index);
  }

  //  Open modal met de vraag van de gekozen box
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

  //  Sluit de modal en overlay
  function closeModal() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('modal').style.display = 'none';
  }

  //  Controleer antwoord op correctheid en update score
  function checkAnswer() {
    const userAnswer = document.getElementById('answer').value.trim().toLowerCase();
    const correctAnswer = document.getElementById('modal').dataset.correctAnswer.toLowerCase();
    const boxIndex = document.getElementById('modal').dataset.boxIndex;
    const feedback = document.getElementById('feedback');

    if (userAnswer === correctAnswer) {
      if (!answeredBoxes.has(boxIndex)) {
        correctAnswers++;           //  Verhoog score als box nog niet beantwoord was
        answeredBoxes.add(boxIndex); //  Markeer box als beantwoord
      }

      feedback.textContent = ' Correct!';
      feedback.style.color = 'green';

      //  Sluit modal en toon win scherm als alle vragen goed zijn
      setTimeout(() => {
        closeModal();

        if (correctAnswers === totalQuestions) {
          showWinScreen();
        }
      }, 1000);
    } else {
      //  Bij fout antwoord: sluit modal, toon verlies bericht en ga terug naar start
      closeModal();
      showLossScreen();
      setTimeout(() => {
        window.location.href = 'index.php';
      }, 3000);
    }
  }

  //  Toon het win scherm
  function showWinScreen() {
    document.getElementById('winScreen').style.display = 'block';
  }

  //  Maak en toon een overlay met verliesbericht
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

  //  Eventlistener voor knop in win scherm: terug naar homepagina
  document.getElementById('continueBtn').addEventListener('click', () => {
    window.location.href = 'index.php';
  });
</script>
</body>
</html>
