<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Banco Borleone</title>

  <!-- Fuentes y Estilos -->
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #121212;
      color: #eaeaea;
      margin: 0;
      padding: 0;
    }

    .hero {
      background: url('img/mafia-city.jpg') no-repeat center center/cover;
      color: white;
      padding: 6rem 2rem;
      text-align: center;
      position: relative;
    }

    .hero::after {
      content: '';
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.75);
      z-index: 0;
    }

    .hero-content {
      position: relative;
      z-index: 1;
    }

    .hero h1 {
      font-family: 'Cinzel', serif;
      font-size: 3.2rem;
      font-weight: 700;
      color: #d4af37;
    }

    .hero p {
      font-size: 1.4rem;
      margin-top: 1rem;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }

    .btn-gold {
      background-color: #d4af37;
      color: #000;
      border: none;
      font-weight: 600;
      padding: 0.75rem 1.5rem;
      border-radius: 5px;
      transition: 0.3s ease-in-out;
    }

    .btn-gold:hover {
      background-color: #b38f2d;
      color: #fff;
    }

    .features {
      background: #1b1b1b;
      padding: 4rem 2rem;
    }

    .features .icon-box {
      text-align: center;
      background-color: #2a2a2a;
      border: 1px solid #333;
      padding: 2rem 1.5rem;
      margin-bottom: 2rem;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.3);
      transition: transform 0.3s ease;
    }

    .features .icon-box:hover {
      transform: translateY(-10px);
    }

    .features i {
      font-size: 2.5rem;
      color: #d4af37;
      margin-bottom: 1rem;
    }

    .features h5 {
      font-family: 'Cinzel', serif;
      font-size: 1.25rem;
      color: #f0f0f0;
    }

    .features p {
      font-size: 0.95rem;
      color: #cccccc;
    }

    footer {
      background-color: #000;
      padding: 2rem 0;
      color: #999;
      text-align: center;
    }

    footer .social-icons a {
      color: #d4af37;
      margin: 0 10px;
      font-size: 1.4rem;
      transition: 0.3s;
    }

    footer .social-icons a:hover {
      color: #fff;
      transform: scale(1.2);
    }

    @media (max-width: 767px) {
      .hero h1 {
        font-size: 2.2rem;
      }
      .hero p {
        font-size: 1.1rem;
      }
    }
  </style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm" style="border-bottom: 2px solid #d4af37;">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="assets/img/gold.png" alt="Logo Banco Borleone" style="height: 40px; margin-right: 10px;">
      <span style="font-family: 'Cinzel', serif; font-weight: bold; color: #d4af37;">Banco Borleone</span>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto gap-1">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="usuarios/modal_contactos.php"><i class="fas fa-envelope me-1"></i>Contactos</a></li>
        <li class="nav-item"><a class="nav-link" href="registro.php"><i class="fas fa-user-plus me-1"></i>Crear cuenta</a></li>
        <li class="nav-item"><a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Iniciar sesión</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="container hero-content">
    <h1 data-aos="fade-up">Banco Borleone</h1>
    <p data-aos="fade-up" data-aos-delay="100">Donde el dinero se mueve en silencio, y la confianza vale más que el oro.</p>
    <a href="login.php" class="btn btn-gold me-3 mt-3" data-aos="zoom-in" data-aos-delay="200">Iniciar sesión</a>
    <a href="registro.php" class="btn btn-gold me-3 mt-3" data-aos="zoom-in" data-aos-delay="200">Crear cuenta</a>
   
  </div>
</section>

<!-- FEATURES -->
<section class="features">
  <div class="container">
    <div class="row text-center">
      <div class="col-md-4" data-aos="fade-up">
        <div class="icon-box">
          <i class="fas fa-user-secret"></i>
          <h5>Privacidad absoluta</h5>
          <p>Tu información es tan confidencial como los secretos de la familia.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="150">
        <div class="icon-box">
          <i class="fas fa-vault"></i>
          <h5>Seguridad blindada</h5>
          <p>Protegemos tu dinero como si fuera parte del botín.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
        <div class="icon-box">
          <i class="fas fa-handshake"></i>
          <h5>Negocios leales</h5>
          <p>Trato justo y claro. Porque en esta familia, la palabra vale más que un contrato.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="container">
    <p>&copy; 2025 Banco Borleone. Todos los derechos reservados.</p>
    <p><a href="#">Términos y condiciones</a> | <a href="#">Política de privacidad</a></p>
    <div class="social-icons mt-3">
      <a href="https://wa.me/59163488086?text=Hola,%20quiero%20más%20información" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
      <a href="https://www.youtube.com/@Padrino-c3c7u" target="_blank" title="YouTube"><i class="fab fa-youtube"></i></a>
      <a href="https://t.me/+1VWOkOrUuPc3ZTM" target="_blank" title="Telegram"><i class="fab fa-telegram"></i></a>
      <a href="https://www.tiktok.com/@elpadrino1013?_t=ZM-8w8Feidd3FW&_r=1" target="_blank" title="TikTok"><i class="fab fa-tiktok"></i></a>
    </div>
  </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
  AOS.init({ duration: 1000 });
</script>

</body>
</html>
