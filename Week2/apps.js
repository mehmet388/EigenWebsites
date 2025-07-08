// Selecteer alle navigatieknoppen
const navButtons = document.querySelectorAll('.nav-button');

// Selecteer alle secties
const sections = document.querySelectorAll('.content-section');

// Voeg event listeners toe aan de knoppen
navButtons.forEach(button => {
  button.addEventListener('click', () => {
    // Haal de waarde van data-section op
    const targetSection = button.getAttribute('data-section');

    // Verwijder 'active' van alle secties
    sections.forEach(section => {
      section.classList.remove('active');
    });

    // Voeg 'active' toe aan de geselecteerde sectie
    const activeSection = document.getElementById(targetSection);
    activeSection.classList.add('active');
  });
});

// Selecteer elementen
const loginForm = document.getElementById("login-form");
const loginError = document.getElementById("login-error");

// Vooraf ingestelde gebruikers
const users = [
  { username: "admin", password: "1234" },
  { username: "user1", password: "abcd" },
  { username: "test", password: "test123" },
];

// Inlogformulier validatie
loginForm.addEventListener("submit", (e) => {
  e.preventDefault();

  // Haal gebruikersnaam, wachtwoord en gekozen sectie op
  const username = document.getElementById("username").value;
  const password = document.getElementById("password").value;
  const redirect = document.getElementById("redirect").value;

  // Controleer of er een gebruiker overeenkomt
  const user = users.find(
    (user) => user.username === username && user.password === password
  );

  if (user) {
    // Succesvol ingelogd
    loginError.style.display = "none";
    alert(`Welkom ${user.username}!`);
    
    // Leid de gebruiker naar de gekozen sectie
    document.querySelectorAll(".content-section").forEach((section) => {
      section.classList.remove("active");
    });
    document.getElementById(redirect).classList.add("active");
  } else {
    // Foutmelding tonen
    loginError.textContent = "Ongeldige gebruikersnaam of wachtwoord.";
    loginError.style.display = "block";
  }
});
