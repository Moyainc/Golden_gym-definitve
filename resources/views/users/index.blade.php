<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Golden Gym - Transforma tu Cuerpo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Bebas+Neue&display=swap" rel="stylesheet">
  <link href="{{ asset('css/inicio_public.css') }}" rel="stylesheet" />

</head>

<body>

  <!-- Navbar Golden Gym -->
  <nav class="navbar">
    <a href="#" class="navbar-brand">GOLDEN GYM</a>
    <ul class="navbar-menu" id="navbarMenu">
      <li><a href="#entrenadores">Entrenadores</a></li>
      <li><a href="#localizacion">Localización</a></li>
      <li><a href="#productos">Productos</a></li>
      <li><a href="#login" class="navbar-login">Iniciar Sesión</a></li>
    </ul>
    <div class="hamburger" id="hamburger">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </nav>


  <div id="preloader">
    <div class="spinner"></div>
  </div>

  <section class="hero">
    <video class="hero-video" autoplay muted loop playsinline>
      <source src="{{ asset('img/photos/video-index.mp4') }}" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <a href="#entrenadores" class="cta-button">Comienza Hoy</a>
    </div>
  </section>

  <!-- Trainers Section -->
  <section id="entrenadores" class="section">
    <h2 class="section-title">Nuestros Entrenadores</h2>
    <div class="trainers-grid">
      @forelse ($entrenadores as $trainer)
      <div class="trainer-card fade-in">
        <img src="{{ asset('storage/' . $trainer->imagen) }}"
          alt="{{ $trainer->nombre }}"
          class="trainer-image">
        <h3 class="trainer-name">{{ strtoupper($trainer->nombre) }}</h3>
        <p class="trainer-specialty">{{ $trainer->descripcion }}</p>
      </div>
      @empty
      <p class="text-muted">No hay entrenadores disponibles por el momento.</p>
      @endforelse
    </div>
  </section>


  <!-- Location Section -->
  <section id="localizacion" class="location-section">
    <div class="location-container">
      <h2 class="section-title">Nuestra Ubicación</h2>
      <div class="location-content">
        <div class="location-text fade-in">
          <div class="location-animated-text" id="animated-text"></div>
          <div class="location-details">
            <p><strong>📍 Dirección:</strong> Av. Principal 123, Ciudad</p>
            <p><strong>📞 Teléfono:</strong> +1 (555) 123-4567</p>
            <p><strong>🕐 Horario:</strong> Lunes a Viernes: 6:00 AM - 11:00 PM<br>Sábado y Domingo: 8:00 AM - 8:00 PM</p>
            <p><strong>💪 Instalaciones:</strong> Más de 2,000 m² de espacio equipado con la última tecnología en entrenamiento</p>
          </div>
        </div>
        <div class="map-container fade-in">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.9476519598093!2d-73.99185368459395!3d40.74117097932881!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c259a9b3117469%3A0xd134e199a405a163!2sEmpire%20State%20Building!5e0!3m2!1sen!2sus!4v1234567890123!5m2!1sen!2sus" allowfullscreen="" loading="lazy"></iframe>
        </div>
      </div>
    </div>
  </section>

  <!-- Products Section -->
  <section id="productos" class="section">
    <h2 class="section-title">Nuestros Productos</h2>
    <div class="products-grid">
      <div class="product-card fade-in">
        <img src="/placeholder.svg?height=220&width=220" alt="Proteína Premium" class="product-image">
        <h3 class="product-name">PROTEÍNA PREMIUM</h3>
        <p class="product-price">$49.99</p>
        <p class="product-description">Proteína de suero de alta calidad para maximizar tus ganancias musculares</p>
      </div>
      <div class="product-card fade-in">
        <img src="/placeholder.svg?height=220&width=220" alt="Guantes Pro" class="product-image">
        <h3 class="product-name">GUANTES PRO</h3>
        <p class="product-price">$29.99</p>
        <p class="product-description">Guantes profesionales con agarre superior y máxima comodidad</p>
      </div>
      <div class="product-card fade-in">
        <img src="/placeholder.svg?height=220&width=220" alt="Shaker Golden" class="product-image">
        <h3 class="product-name">SHAKER GOLDEN</h3>
        <p class="product-price">$19.99</p>
        <p class="product-description">Shaker exclusivo de Golden Gym con sistema de mezcla perfecto</p>
      </div>
      <div class="product-card fade-in">
        <img src="/placeholder.svg?height=220&width=220" alt="Membresía Anual" class="product-image">
        <h3 class="product-name">MEMBRESÍA ANUAL</h3>
        <p class="product-price">$599.99</p>
        <p class="product-description">Acceso ilimitado por 12 meses + 1 mes gratis de entrenamiento personal</p>
      </div>
    </div>
  </section>

  <!-- Motivational Quote Section -->
  <section class="quote-section">
    <p class="quote-text fade-in">
      Tu cuerpo puede resistir casi cualquier cosa. Es tu mente la que debes convencer. Ven a Golden Gym y descubre tu verdadero potencial.
    </p>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-content">

      <div class="footer-social">
        <a href="{{ $links?->facebook ?? '#' }}" target="_blank" class="social-link">
          <i data-feather="facebook"></i>
        </a>
        <a href="{{ $links?->instagram ?? '#' }}" target="_blank" class="social-link">
          <i data-feather="instagram"></i>
        </a>
        <a href="{{ $links?->twitter ?? '#' }}" target="_blank" class="social-link">
          <i data-feather="twitter"></i>
        </a>
        <a href="{{ $links?->youtube ?? '#' }}" target="_blank" class="social-link">
          <i data-feather="youtube"></i>
        </a>
      </div>


      <p>&copy; 2025 Golden Gym. Todos los derechos reservados. | Diseñado para inspirar grandeza</p>

    </div>
  </footer>


  <!-- JavaScript -->
  <script src="{{ asset('js/inicio_public.js') }}"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      feather.replace();
    });
  </script>

</body>

</html>
