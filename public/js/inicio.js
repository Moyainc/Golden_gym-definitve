feather.replace();

// Preloader
window.addEventListener("load", () => {
  setTimeout(() => {
    const preloader = document.getElementById("preloader");
    preloader.style.opacity = "0";
    setTimeout(() => (preloader.style.display = "none"), 500); // espera a que termine la transición
  }, 700); // tiempo total visible antes de desvanecerse
});
