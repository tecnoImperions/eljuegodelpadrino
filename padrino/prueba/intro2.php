<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>BORLEONE - Intro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
  
  <!-- Meta para barras y botones móviles oscuros -->
  <meta name="theme-color" content="#000000" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
  <meta name="msapplication-navbutton-color" content="#000000" />

  <style>
    /* RESET */
    * {
      margin: 0; padding: 0; box-sizing: border-box;
    }
    html, body {
      height: 100%;
      background: #000;
      color: #d4af37;
      font-family: 'Cinzel', serif;
      overflow: hidden;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      padding-bottom: env(safe-area-inset-bottom);
      background-clip: padding-box;
      position: relative;
    }
    /* Zona segura inferior fondo negro para móviles */
    @supports (padding: env(safe-area-inset-bottom)) {
      body::after {
        content: '';
        position: fixed;
        bottom: 0; left: 0; right: 0;
        height: env(safe-area-inset-bottom);
        background: #000;
        z-index: 9999;
      }
    }

    /* Contenedor general centrado */
    #container {
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
      perspective: 1200px;
    }

    /* Reflectores animados en el fondo */
    #spotlights {
      position: absolute;
      top: 50%; left: 50%;
      width: 200%;
      height: 200%;
      transform: translate(-50%, -50%);
      pointer-events: none;
      z-index: 0;
      animation: rotateSpotlights 20s linear infinite;
    }
    #spotlights::before, #spotlights::after {
      content: "";
      position: absolute;
      width: 40vw;
      height: 40vw;
      background: radial-gradient(circle, rgba(212,175,55,0.25) 0%, transparent 70%);
      filter: blur(60px);
      top: 30%; left: 10%;
      border-radius: 50%;
      animation: pulse 6s ease-in-out infinite;
      mix-blend-mode: screen;
    }
    #spotlights::after {
      top: 60%; left: 70%;
      animation-delay: 3s;
    }
    @keyframes rotateSpotlights {
      from { transform: translate(-50%, -50%) rotate(0deg);}
      to { transform: translate(-50%, -50%) rotate(360deg);}
    }
    @keyframes pulse {
      0%, 100% {opacity: 0.3;}
      50% {opacity: 0.7;}
    }

    /* Moneda */
    .coin {
      width: 240px;
      height: 240px;
      border-radius: 50%;
      position: relative;
      cursor: default;
      transform-style: preserve-3d;
      animation: spin 6s linear infinite;
      box-shadow:
        0 0 20px 2px rgba(212,175,55,0.6),
        inset 0 0 10px 4px rgba(255, 215, 0, 0.6);
      /* Doble borde dorado (3D fake) */
      border: 8px double #d4af37;
      background: linear-gradient(145deg, #1a1a1a 20%, #000000 80%);
    }
    /* Borde extra para efecto 3D falso */
    .coin::before {
      content: "";
      position: absolute;
      top: 8px; left: 8px; right: 8px; bottom: 8px;
      border-radius: 50%;
      border: 5px double #b8860b;
      pointer-events: none;
      filter: drop-shadow(0 0 6px rgba(212,175,55,0.4));
      z-index: 1;
    }

    /* Cara y cruz */
    .face {
      position: absolute;
      width: 100%;
      height: 100%;
      backface-visibility: hidden;
      border-radius: 50%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      user-select: none;
      text-shadow:
        0 0 10px #d4af37,
        0 0 20px #ffd700,
        0 0 30px #ffd700,
        0 0 40px #ffd700;
      font-weight: 900;
    }
    /* Cara */
    .face.front {
      background: radial-gradient(circle at center, #1f1f1f, #000000 80%);
      color: #d4af37;
      font-size: 3.8rem;
      letter-spacing: 0.1em;
      border: 6px solid transparent;
      text-transform: uppercase;
      z-index: 2;
      padding: 0 15px;
      font-family: 'Cinzel', serif;
      filter: drop-shadow(0 0 6px #ffd700);
      box-shadow:
        inset 0 0 40px rgba(255, 215, 0, 0.6),
        0 0 10px rgba(255, 215, 0, 0.5);
      transform: translateZ(12px);
    }
    /* Cruz */
    .face.back {
      background: radial-gradient(circle at center, #1a1a1a, #000000 90%);
      color: #b8860b;
      font-size: 3rem;
      font-weight: 700;
      text-shadow:
        0 0 8px #b8860b,
        0 0 15px #b8860b;
      border: 6px solid transparent;
      transform: rotateY(180deg) translateZ(12px);
      font-family: 'Cinzel', serif;
      padding: 0 20px;
      filter: drop-shadow(0 0 5px #b8860b);
    }

    /* Texto dentro de la moneda */
    .front .top-text {
      font-size: 3.6rem;
      font-weight: 900;
      letter-spacing: 0.15em;
      margin-bottom: 0.1em;
      text-transform: uppercase;
      color: #d4af37;
      text-shadow:
        0 0 15px #ffd700,
        0 0 25px #ffd700,
        0 0 40px #ffd700;
      animation: glowText 3s ease-in-out infinite alternate;
    }
    .front .bottom-text {
      font-size: 1.5rem;
      letter-spacing: 0.5em;
      margin-top: 0.1em;
      color: #c9b037;
      text-shadow: 0 0 6px #d4af37;
    }
    @keyframes glowText {
      from {
        text-shadow:
          0 0 15px #ffd700,
          0 0 25px #ffd700,
          0 0 40px #ffd700;
        color: #d4af37;
      }
      to {
        text-shadow:
          0 0 30px #fffacd,
          0 0 50px #fffacd,
          0 0 70px #fffacd;
        color: #fffacd;
      }
    }

    /* Animación giro */
    @keyframes spin {
      from { transform: rotateY(0deg); }
      to { transform: rotateY(360deg); }
    }

    /* Botón ENTRAR */
    #permission {
      position: fixed;
      bottom: 3rem;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10;
      display: flex;
      justify-content: center;
      align-items: center;
      width: 90%;
      max-width: 320px;
    }
    .start-button {
      background: linear-gradient(135deg, #d4af37, #b8860b);
      border: none;
      color: #000;
      font-family: 'Cinzel', serif;
      font-size: 1.4rem;
      font-weight: 900;
      padding: 1.2rem 2.5rem;
      border-radius: 14px;
      cursor: pointer;
      box-shadow:
        0 0 12px #ffd700,
        inset 0 0 6px #fffacd;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      user-select: none;
      text-shadow: 0 0 6px #fffacd;
    }
    .start-button:hover,
    .start-button:focus {
      outline: none;
      transform: scale(1.1);
      box-shadow:
        0 0 25px #fffacd,
        inset 0 0 12px #fffacd;
    }

    /* Evitar selección */
    body, .start-button {
      -webkit-user-select: none;
      -moz-user-select: none;
      user-select: none;
    }
  </style>
</head>
<body>
  <div id="container" aria-label="Intro animación Borleone">

    <div id="spotlights"></div>

    <div class="coin" aria-hidden="true" role="img" aria-label="Moneda giratoria Borleone">
      <div class="face front">
        <div class="top-text">BORLEONE</div>
        <div class="bottom-text">PREMIUM</div>
      </div>
      <div class="face back">
        #1
      </div>
    </div>
  </div>

  <div id="permission" role="dialog" aria-modal="true" aria-label="Botón para entrar a la web">
    <button class="start-button" onclick="startIntro()">ENTRAR</button>
  </div>

  <audio id="coinSound" preload="auto" src="https://cdn.pixabay.com/download/audio/2022/03/09/audio_c84bc84318.mp3?filename=coin-collision-sound-342335.mp3"></audio>

  <script>
    const coin = document.querySelector('.coin');
    const permission = document.getElementById('permission');
    const sound = document.getElementById('coinSound');

    function startIntro() {
      // Desaparece botón
      permission.style.display = 'none';

      // Reproducir sonido
      sound.volume = 1;
      sound.play().catch(e => console.warn("Audio bloqueado:", e));

      // Animación de giro más rápida para entrar
      coin.style.animation = "spin 1.5s linear 3";

      // Después de la animación redirigir
      setTimeout(() => {
        window.location.href = 'index.php';
      }, 4500);
    }
  </script>
</body>
</html>
