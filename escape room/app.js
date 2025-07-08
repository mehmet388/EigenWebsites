let currentIndex = 0;
let correctCount = 0;
let totalQuestions = document.querySelectorAll('.box').length;
let answered = new Array(totalQuestions).fill(false);

function openModal(index) {
  currentIndex = index;
  const box = document.querySelector(`.box${index + 1}`);

  if (answered[index]) {
    alert("Deze vraag is al beantwoord.");
    return;
  }

  document.getElementById('question').innerText = box.dataset.question;
  document.getElementById('answer').value = "";
  document.getElementById('feedback').innerText = "";
  document.getElementById('modal').style.display = 'block';
  document.getElementById('overlay').style.display = 'block';
}

function closeModal() {
  document.getElementById('modal').style.display = 'none';
  document.getElementById('overlay').style.display = 'none';
}

function checkAnswer() {
  const userAnswer = document.getElementById('answer').value.trim().toLowerCase();
  const box = document.querySelector(`.box${currentIndex + 1}`);
  const correctAnswer = box.dataset.answer.trim().toLowerCase();

  if (answered[currentIndex]) {
    document.getElementById('feedback').innerText = "Deze vraag is al beantwoord.";
    return;
  }

  if (userAnswer === correctAnswer) {
    answered[currentIndex] = true;
    correctCount++;
    closeModal();

    if (correctCount === totalQuestions) {
      // Win: alle vragen goed
      setTimeout(() => {
        window.location.href = "win.html";
      }, 500);
    }
  } else {
    // Fout: direct verliezen
    closeModal();
    setTimeout(() => {
      window.location.href = "lose.html";
    }, 500);
  }
}

// Timer (60 seconden)
let timeLeft = 60;
let timerElement = document.getElementById("timer");

let timerInterval = setInterval(() => {
  timeLeft--;

  if (timerElement) {
    timerElement.innerText = `⏱️ Tijd over: ${timeLeft}s`;
  }

  if (timeLeft <= 0) {
    clearInterval(timerInterval);
    if (correctCount < totalQuestions) {
      window.location.href = "lose.html"; // Tijd voorbij => verloren
    }
  }
}, 1000);

 setTimeout(() => {
    window.location.href = 'verlies.php';
  }, 60000); // 60 seconden