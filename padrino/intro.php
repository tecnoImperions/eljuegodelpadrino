<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>BORLEONE</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#000000">
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      background-color: #0f0f0f; /* Fondo oscuro sólido */
      color: #f1c40f; /* Color dorado para texto */
      font-family: 'Cinzel', serif;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      height: 100vh;
    }

    /* Mantenemos el gradiente y la textura, pero con más opacidad oscura */
    .particles {
      position: absolute;
      width: 100%;
      height: 100%;
      background: url('https://www.transparenttextures.com/patterns/stardust.png');
      opacity: 0.05; /* un poco más tenue para no sobrecargar */
      z-index: 0;
      pointer-events: none;
    }

    .flash {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: radial-gradient(circle, rgba(241, 196, 15, 0.3) 0%, transparent 70%);
      opacity: 0;
      animation: flash 0.4s ease-out 1.5s;
      pointer-events: none;
      z-index: 2;
    }

    @keyframes flash {
      0% { opacity: 0; }
      50% { opacity: 1; }
      100% { opacity: 0; }
    }

    .intro-container, .permission-screen {
      display: none;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      opacity: 0;
      z-index: 1;
    }

    .permission-screen {
      display: flex;
      opacity: 1;
      animation: fadeIn 1s ease-out forwards;
    }

    .intro-container.show {
      display: flex;
      animation: introAppear 0.8s ease-out forwards;
    }

    .logo {
      font-size: clamp(4rem, 15vw, 10rem);
      font-weight: 700;
      color: #f1c40f;
      margin-bottom: 1rem;
      text-shadow: 0 0 50px rgba(241, 196, 15, 0.8);
      animation: glow 2s ease-in-out infinite alternate;
    }

    .tagline {
      color: #f1c40f;
      font-size: 1.2rem;
      letter-spacing: 0.3em;
      opacity: 0;
      animation: fadeIn 1s ease-out 0.8s forwards;
    }

    .start-button {
      background-color: transparent;
      color: #f1c40f;
      border: 2px solid #f1c40f;
      padding: 1rem 2.5rem;
      font-size: 1.2rem;
      font-weight: bold;
      border-radius: 30px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 0 10px rgba(241, 196, 15, 0.3);
    }

    .start-button:hover {
      background-color: #f1c40f;
      color: #0f0f0f;
      box-shadow: 0 0 25px rgba(241, 196, 15, 0.8);
      transform: scale(1.05);
    }

    .fade-out {
      animation: fadeOut 0.5s ease-in forwards;
    }

    @keyframes glow {
      from { text-shadow: 0 0 50px rgba(241, 196, 15, 0.8); }
      to   { text-shadow: 0 0 80px rgba(241, 196, 15, 1); }
    }

    @keyframes fadeIn {
      to { opacity: 1; }
    }

    @keyframes fadeOut {
      to { opacity: 0; transform: scale(0.95); }
    }

    @keyframes introAppear {
      to { opacity: 1; transform: scale(1); }
    }

    @media (max-width: 768px) {
      .tagline { font-size: 1rem; letter-spacing: 0.2em; }
      .start-button { font-size: 1rem; padding: 0.8rem 2rem; }
    }
  </style>
</head>
<body>
  <div class="particles"></div>
  <div class="flash"></div>

  <div class="permission-screen" id="permission">
    <button class="start-button" onclick="startIntro()">ENTRAR</button>
  </div>

  <div class="intro-container" id="intro">
    <div class="logo">BORLEONE</div>
    <div class="tagline">PREMIUM EXPERIENCE</div>
  </div>

  <audio id="coinSound" preload="auto">
    <source src="assets/mp3/coin.mp3" type="audio/mpeg">
  </audio>

  <script>
    const intro = document.getElementById('intro');
    const permission = document.getElementById('permission');
    const sound = document.getElementById('coinSound');

    function startIntro() {
      permission.style.display = 'none';
      intro.classList.add('show');

      // Reproduce el sonido
      sound.volume = 1;
      sound.play().catch(err => {
        console.warn("Audio bloqueado:", err);
      });

      // Transición a la página principal
      setTimeout(() => {
        intro.classList.add('fade-out');
        setTimeout(() => {
          window.location.href = 'index.php';
        }, 500);
      }, 3000);
    }

    window.addEventListener('load', () => {
      permission.style.display = 'flex';
    });

    document.addEventListener('selectstart', e => e.preventDefault());
  </script>
</body>
</html>
