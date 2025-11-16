<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Golden Gym - Transforma tu Cuerpo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="shortcut icon" href="{{ asset('img/icons/golden_gym.png') }}" />
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Bebas+Neue&display=swap" rel="stylesheet">
  <link href="{{ asset('css/inicio_public.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />


</head>

<body>

  <!-- Navbar Golden Gym -->
  <nav class="navbar">
    <a href="#" class="navbar-brand">GOLDEN GYM</a>
    <ul class="navbar-menu" id="navbarMenu">
      <li><a href="#entrenadores">Entrenadores</a></li>
      <li><a href="#localizacion">Localización</a></li>
      <li>
        <a href="{{ route('usuario.login') }}" class="navbar-login">
          <i class="fas fa-user"></i>
        </a>
      </li>

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
            <p><strong>📍 Dirección:</strong> Cl. 24 # 25-143, Quibdó, Chocó</p>
            <p><strong>📞 Teléfono:</strong> +57 310 4115783</p>
            <p><strong>🕐 Horario:</strong> Lunes a Viernes: 5:00 AM - 8:00 PM<br>Sábado: 5:00 AM - 12:00 PM</p>
          </div>
        </div>
        <div class="map-container fade-in">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d992.5464173885115!2d-76.64183989422426!3d5.686287154267455!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e488f628796fb3b%3A0xdc72e49194fbdcdc!2zQ2wuIDI0ICMgMjUtMTQzLCBRdWliZMOzLCBDaG9jw7M!5e0!3m2!1ses!2sco!4v1763145672569!5m2!1ses!2sco" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
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

      <div class="footer-social flex gap-4 justify-center items-center">
        <a href="{{ $links?->facebook ?? '#' }}" class="social-link">
          <i class="fa-brands fa-facebook-f fa-lg"></i>
        </a>
        <a href="{{ $links?->instagram ?? '#' }}" target="_blank" class="social-link">
          <i class="fa-brands fa-instagram fa-lg"></i>
        </a>
        <a href="{{ $links?->x ?? '#' }}" target="_blank" class="social-link">
          <i class="fa-brands fa-x-twitter fa-lg"></i>
        </a>
        <a href="{{ $links?->youtube ?? '#' }}" target="_blank" class="social-link">
          <i class="fa-brands fa-youtube fa-lg"></i>
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
