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
