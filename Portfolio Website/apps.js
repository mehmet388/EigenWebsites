document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".nav-button");
    const sections = document.querySelectorAll(".content-section");
  
     // maakt alles clickable
    buttons.forEach((button) => {
      button.addEventListener("click", function () {
        const sectionId = this.getAttribute("data-section");
  
        // Verberg alle secties
        sections.forEach((section) => section.classList.remove("active"));
  
        // Toon de juiste sectie
        document.getElementById(sectionId).classList.add("active");
      });
    });
  });
  