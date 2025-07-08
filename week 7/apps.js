// Navigatie tussen secties
const navButtons = document.querySelectorAll('.nav-button');
const sections = document.querySelectorAll('.content-section');

function toggleSection(target) {
  sections.forEach(section => section.classList.remove('active'));
  document.getElementById(target).classList.add('active');
}

navButtons.forEach(button => {
  button.addEventListener('click', () => {
    const targetSection = button.getAttribute('data-section');
    toggleSection(targetSection);
  });
});

// Wachtwoord tonen/verbergen
const passwordField = document.getElementById('password');
const showPasswordCheckbox = document.getElementById('show-password');

showPasswordCheckbox.addEventListener('change', () => {
  passwordField.type = showPasswordCheckbox.checked ? 'text' : 'password';
});

// Inloggen validatie
const loginForm = document.getElementById('login-form');
const loginError = document.getElementById('login-error');

const users = [
  { username: 'admin', password: '1234' },
  { username: 'user1', password: 'abcd' },
  { username: 'test', password: 'test123' },
];

function validateLogin(username, password) {
  return users.find(user => user.username === username && user.password === password);
}

loginForm.addEventListener('submit', (e) => {
  e.preventDefault();
  const username = document.getElementById('username').value;
  const password = document.getElementById('password').value;
  const redirect = document.getElementById('redirect').value;

  if (validateLogin(username, password)) {
    alert(`Welkom ${username}!`);
    toggleSection(redirect);
    loginError.textContent = '';
  } else {
    loginError.textContent = 'Ongeldige gebruikersnaam of wachtwoord.';
  }
});

// Modal functionaliteit
const modal = document.getElementById("modal");
const closeBtn = document.querySelector(".close-btn");
const newAccountBtnBetaling = document.getElementById("new-account-btn-betaling");
const newAccountBtnOmschrijving = document.getElementById("new-account-btn-omschrijving");
const newAccountForm = document.getElementById("new-account-form");

function openModal() {
  modal.style.display = "block";
}

newAccountBtnBetaling.addEventListener("click", openModal);
newAccountBtnOmschrijving.addEventListener("click", openModal);

closeBtn.addEventListener("click", () => {
  modal.style.display = "none";
});

window.addEventListener("click", (e) => {
  if (e.target === modal) {
    modal.style.display = "none";
  }
});

newAccountForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const accountName = document.getElementById("account-name").value;
  const initialAmount = document.getElementById("initial-amount").value;

  const newRekeningItem = document.createElement("div");
  newRekeningItem.classList.add("rekening-item");
  newRekeningItem.innerHTML = `
    <span>${accountName}</span>
    <span>€${parseFloat(initialAmount).toFixed(2)}</span>
  `;

  const rekeningContainer = document.querySelector("#betaalrekening .rekening");
  rekeningContainer.appendChild(newRekeningItem);

  modal.style.display = "none";
  newAccountForm.reset();
});



// Crypto-functionaliteit
const saldoEl = document.getElementById("saldo");
const huidigePrijsEl = document.getElementById("huidige-prijs");
const inBezitEl = document.getElementById("in-bezit");
const bedragInput = document.getElementById("bedrag");
const investeringStatusEl = document.getElementById("investering-status");

let saldo = 760.0; // Startsaldo (kan gekoppeld worden aan een dashboard-waarde)
let huidigePrijs = 120.0;
let inBezit = 2;

function updateCryptoInfo() {
  saldoEl.textContent = `€${saldo.toFixed(2)}`;
  huidigePrijsEl.textContent = `€${huidigePrijs.toFixed(2)}`;
  inBezitEl.textContent = inBezit;
}

document.getElementById("koop-btn").addEventListener("click", () => {
  const bedrag = parseFloat(bedragInput.value);
  if (isNaN(bedrag) || bedrag <= 0) {
    investeringStatusEl.textContent = "Voer een geldig bedrag in.";
    return;
  }

  if (saldo >= bedrag) {
    const aantal = Math.floor(bedrag / huidigePrijs);
    saldo -= bedrag;
    inBezit += aantal;
    investeringStatusEl.textContent = `Je hebt €${bedrag.toFixed(2)} geïnvesteerd.`;
    investeringStatusEl.style.color = "green";
  } else {
    investeringStatusEl.textContent = "Onvoldoende saldo.";
    investeringStatusEl.style.color = "red";
  }

  updateCryptoInfo();
});

document.getElementById("verkoop-btn").addEventListener("click", () => {
  const bedrag = parseFloat(bedragInput.value);
  if (isNaN(bedrag) || bedrag <= 0) {
    investeringStatusEl.textContent = "Voer een geldig bedrag in.";
    return;
  }

  const aantal = Math.floor(bedrag / huidigePrijs);
  if (inBezit >= aantal) {
    saldo += bedrag;
    inBezit -= aantal;
    investeringStatusEl.textContent = `Je hebt €${bedrag.toFixed(2)} terugverdiend.`;
    investeringStatusEl.style.color = "green";
  } else {
    investeringStatusEl.textContent = "Onvoldoende eenheden in bezit.";
    investeringStatusEl.style.color = "red";
  }

  updateCryptoInfo();
});

// Simuleer prijsverandering
setInterval(() => {
  const verandering = Math.random() * 10 - 5; // Willekeurige verandering tussen -5 en +5
  huidigePrijs = Math.max(1, huidigePrijs + verandering); // Zorg dat de prijs niet negatief wordt
  updateCryptoInfo();
}, 5000);

updateCryptoInfo();

document.addEventListener("DOMContentLoaded", () => {
  const balances = { bitcoin: 0, ethereum: 0, litecoin: 0 };
  const prices = { bitcoin: 90000, ethereum: 4800, litecoin: 250 };
  let totalInvested = 0;

  const updateUI = () => {
    document.getElementById("bitcoin-balance").textContent = (balances.bitcoin * prices.bitcoin).toFixed(2);
    document.getElementById("ethereum-balance").textContent = (balances.ethereum * prices.ethereum).toFixed(2);
    document.getElementById("litecoin-balance").textContent = (balances.litecoin * prices.litecoin).toFixed(2);
    document.getElementById("status-message").textContent = `Je hebt €${totalInvested.toFixed(2)} geïnvesteerd.`;
  };

  const handleTransaction = (type) => {
    const crypto = document.getElementById("crypto-select").value;
    const amount = parseFloat(document.getElementById("amount").value);
    if (isNaN(amount) || amount <= 0) return alert("Voer een geldig bedrag in.");

    const units = amount / prices[crypto];
    if (type === "buy") {
      balances[crypto] += units;
      totalInvested += amount;
    } else if (type === "sell" && balances[crypto] >= units) {
      balances[crypto] -= units;
      totalInvested -= amount;
    } else {
      return alert("Onvoldoende balans om te verkopen.");
    }
    updateUI();
  };

  document.getElementById("buy-button").addEventListener("click", () => handleTransaction("buy"));
  document.getElementById("sell-button").addEventListener("click", () => handleTransaction("sell"));
  updateUI();
});



// Voorbeelddataset met transacties
const transactions = [
  {
    type: "inkomend",
    date: "2024-11-01",
    amount: 150.0,
    fromAccount: "Spaarrekening",
    toAccount: "Betaalrekening",
    description: "Salaris",
  },
  {
    type: "uitgaand",
    date: "2024-11-03",
    amount: 50.0,
    fromAccount: "Betaalrekening",
    toAccount: "Supermarkt",
    description: "Boodschappen",
  },
  {
    type: "inkomend",
    date: "2024-11-05",
    amount: 200.0,
    fromAccount: "Werkgever",
    toAccount: "Betaalrekening",
    description: "Bonus",
  },
];

// Functie om transacties weer te geven
function renderTransactions(transactions) {
  const transactionList = document.getElementById("transaction-list");
  transactionList.innerHTML = ""; // Reset de lijst

  transactions.forEach((transaction) => {
    const listItem = document.createElement("li");
    listItem.innerHTML = `
      <div>
        <strong>Type:</strong> ${transaction.type}<br>
        <strong>Datum:</strong> ${transaction.date}<br>
        <strong>Bedrag:</strong> €${transaction.amount.toFixed(2)}<br>
        <strong>Van rekening:</strong> ${transaction.fromAccount}<br>
        <strong>Naar rekening:</strong> ${transaction.toAccount}<br>
        <strong>Omschrijving:</strong> ${transaction.description}
      </div>
    `;
    listItem.style.backgroundColor =
      transaction.type === "inkomend" ? "#d4f7d4" : "#f7d4d4";
    transactionList.appendChild(listItem);
  });
}

// Functie om filters toe te passen
function applyFilters() {
  const typeFilter = document.getElementById("type-filter").value;
  const dateFilter = document.getElementById("date-filter").value;

  const filteredTransactions = transactions.filter((transaction) => {
    const matchesType =
      typeFilter === "all" || transaction.type === typeFilter;
    const matchesDate = !dateFilter || transaction.date === dateFilter;
    return matchesType && matchesDate;
  });

  renderTransactions(filteredTransactions);
}

// Event listener voor het filteren
document
  .getElementById("type-filter")
  .addEventListener("change", applyFilters);
document
  .getElementById("date-filter")
  .addEventListener("input", applyFilters);

// Functie om een nieuwe rekening toe te voegen via de modal
document.getElementById("new-account-form").addEventListener("submit", (e) => {
  e.preventDefault();
  const accountName = document.getElementById("account-name").value;
  const initialAmount = parseFloat(document.getElementById("initial-amount").value);

  alert(`Rekening "${accountName}" aangemaakt met €${initialAmount.toFixed(2)}`);
  // Sluit de modal
  document.querySelector(".modal").style.display = "none";
});

// Event listener om de modal te openen en sluiten
document.getElementById("new-account-btn-betaling").addEventListener("click", () => {
  document.querySelector(".modal").style.display = "block";
});

document.querySelector(".close-btn").addEventListener("click", () => {
  document.querySelector(".modal").style.display = "none";
});

// Laad de initiële transacties bij het starten
renderTransactions(transactions);
