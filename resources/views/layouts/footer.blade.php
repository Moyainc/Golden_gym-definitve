<footer class="footer bg-dark text-white border-top border-secondary p-2">
  <div class="container-fluid">
    <div class="row align-items-center">

      <!-- Logo + Nombre del gym -->
      <div class="col-md-4 d-flex align-items-center mb-md-0">
        <!-- Si tienes logo, reemplaza src por la ruta de tu logo -->
        <img src="{{ asset('img/icons/golden_gym.png') }}" alt="Golden GYM Logo" class="me-2" style="height:30px;">
      </div>

      <!-- Links principales -->
      <div class="col-md-4 text-center mb-md-0">
        <ul class="list-inline mb-0">
          <li class="list-inline-item mx-2">
            <a class="text-secondary text-decoration-none hover-yellow" href="#">Support</a>
          </li>
          <li class="list-inline-item mx-2">
            <a class="text-secondary text-decoration-none hover-yellow" href="#">Help Center</a>
          </li>
          <li class="list-inline-item mx-2">
            <a class="text-secondary text-decoration-none hover-yellow" href="#">Privacy</a>
          </li>
          <li class="list-inline-item mx-2">
            <a class="text-secondary text-decoration-none hover-yellow" href="#">Terms</a>
          </li>
        </ul>
      </div>

      <!-- Derechos reservados -->
      <div class="col-md-4 text-md-end text-center">
        <small class="text-secondary">
          &copy; 2025 <a href="#" class="text-white text-decoration-none">Golden GYM</a> - Derechos Reservados
        </small>
      </div>
    </div>
  </div>
</footer>

<style>
  /* Efecto hover en links: pasan de gris a amarillo */
  .hover-yellow:hover {
    color: #fcd52b !important;
  }

  /* Ajuste responsive para logos grandes en móviles */
  @media (max-width: 576px) {
    .footer img {
      height: 35px;
    }
  }
</style>
