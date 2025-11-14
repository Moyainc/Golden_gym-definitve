document.addEventListener("DOMContentLoaded", () => {
    setTimeout(() => {
        const preloader = document.getElementById("preloader");
        preloader.style.opacity = "0";
        setTimeout(() => (preloader.style.display = "none"), 500);
    }, 700);



    const navbar = document.querySelector(".navbar");
    const hamburger = document.getElementById("hamburger");
    const navbarMenu = document.getElementById("navbarMenu");

    window.addEventListener("scroll", () => {
        if (window.scrollY > 100) {
            navbar.classList.add("scrolled");
        } else {
            navbar.classList.remove("scrolled");
        }
    });

    hamburger.addEventListener("click", () => {
        hamburger.classList.toggle("active");
        navbarMenu.classList.toggle("active");
    });

    // Close mobile menu when clicking a link
    document.querySelectorAll(".navbar-menu a").forEach((link) => {
        link.addEventListener("click", () => {
            hamburger.classList.remove("active");
            navbarMenu.classList.remove("active");
        });
    });

    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: "0px 0px -80px 0px",
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("visible");
            }
        });
    }, observerOptions);

    document.querySelectorAll(".fade-in").forEach((el) => {
        observer.observe(el);
    });

    // Animated text typewriter effect
    const animatedTextElement = document.getElementById("animated-text");
    const texts = [
        "💪 GOLDEN GYM - DONDE NACEN LOS CAMPEONES",
        "🔥 TRANSFORMA TU VIDA, TRANSFORMA TU CUERPO",
        "⭐ EQUIPAMIENTO DE ÚLTIMA GENERACIÓN",
    ];
    let textIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let typingSpeed = 100;

    function typeWriter() {
        const currentText = texts[textIndex];

        if (!isDeleting) {
            animatedTextElement.textContent = currentText.substring(
                0,
                charIndex + 1
            );
            charIndex++;

            if (charIndex === currentText.length) {
                isDeleting = true;
                typingSpeed = 2000;
            } else {
                typingSpeed = 100;
            }
        } else {
            animatedTextElement.textContent = currentText.substring(
                0,
                charIndex - 1
            );
            charIndex--;

            if (charIndex === 0) {
                isDeleting = false;
                textIndex = (textIndex + 1) % texts.length;
                typingSpeed = 500;
            } else {
                typingSpeed = 50;
            }
        }

        setTimeout(typeWriter, typingSpeed);
    }

    typeWriter();

    // Smooth scroll for all anchor links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                const offsetTop = target.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: "smooth",
                });
            }
        });
    });

    // Video fallback
    const video = document.querySelector(".hero-video");
    video.addEventListener("error", () => {
        video.style.display = "none";
        document.querySelector(".hero").style.background =
            "linear-gradient(135deg, #000 0%, #272727 100%)";
    });
});
