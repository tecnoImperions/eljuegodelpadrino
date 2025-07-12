<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="theme-color" content="#000000">
  <title>El Padrino - Inicio</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600&display=swap" rel="stylesheet"/>

  <style>
    /* Fondo con imagen y overlay negro con transparencia */
    body {
      margin: 0;
      padding: 0;
      background: url('https://i.pinimg.com/736x/3b/40/fc/3b40fc1b818790a496b3ca9aecaf08f0.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: 'Oswald', sans-serif;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #f1c40f; /* texto amarillo */
      background-color: #0f0f0f; /* respaldo color negro */
    }

    /* Capa oscura para mejorar contraste */
    .overlay {
      background-color: rgba(0, 0, 0, 0.85);
      width: 100%;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
    }

    .mafia-header {
      text-align: center;
      background-color: rgba(18, 18, 18, 0.95);
      padding: 2rem 2.5rem;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(241, 196, 15, 0.7);
      color: #f1c40f;
      width: 100%;
      max-width: 600px;
    }

    h1 {
      font-size: clamp(2rem, 5vw, 3.5rem);
      font-weight: 700;
      margin-bottom: 1rem;
      letter-spacing: 1.5px;
    }

    p {
      font-size: clamp(1rem, 3vw, 1.3rem);
      margin-bottom: 2rem;
      font-weight: 500;
      color: #ddd;
    }

    /* Botones principales con estilo amarillo y negro, tipo login */
    .btn-mafia {
      background-color: #f1c40f;
      color: #0f0f0f;
      font-weight: 700;
      padding: 15px 2.5rem;
      border: none;
      border-radius: 30px;
      font-size: 1.2rem;
      box-shadow: 0 5px 15px rgba(241, 196, 15, 0.5);
      transition: background-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
      width: 100%;
      max-width: 280px;
    }

    .btn-mafia:hover {
      background-color: #e67e22;
      box-shadow: 0 8px 25px rgba(230, 126, 34, 0.7);
    }

    .btn-outline-light {
      border: 2px solid #f1c40f;
      color: #f1c40f;
      font-weight: 700;
      padding: 15px 2.5rem;
      background: transparent;
      border-radius: 30px;
      font-size: 1.2rem;
      box-shadow: inset 0 0 0 0 #f1c40f;
      transition: all 0.3s ease-in-out;
      width: 100%;
      max-width: 280px;
    }

    .btn-outline-light:hover {
      background-color: #f1c40f;
      color: #0f0f0f;
      box-shadow: 0 5px 15px rgba(241, 196, 15, 0.6);
    }

    .btn-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 1rem;
    }

    @media (max-width: 576px) {
      .btn-container {
        flex-direction: column;
        align-items: center;
      }
      .btn-mafia, .btn-outline-light {
        width: 100%;
        max-width: 280px;
      }
    }
  </style>
</head>
<body>
  <div class="overlay">
    <div class="mafia-header">
      <h1>🎩 Bienvenido al Padrino</h1>
      <p>Participa en sorteos y gana como un verdadero mafioso.</p>
      <div class="btn-container">
        <a href="registro.php" class="btn btn-mafia">Registrarse</a>
        <a href="login.php" class="btn btn-outline-light">Iniciar Sesión</a>
      </div>
    </div>
  </div>
</body>
</html>
