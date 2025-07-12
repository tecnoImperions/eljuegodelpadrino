<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>BORLEONE INTRO</title>

  <!-- Tamaño y escala en móviles -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

  <!-- Color oscuro para la barra del navegador en móviles -->
  <meta name="theme-color" content="#000000" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

  <!-- Fuente elegante -->
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&display=swap" rel="stylesheet" />

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    html,
    body {
      height: 100vh;
      background: radial-gradient(circle at center, #000000, #0c0c0c);
      font-family: 'Cinzel', serif;
      color: #d4af37;
      overflow: hidden;
      user-select: none;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      touch-action: manipulation;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }
    .glow-bg {
      position: fixed;
      width: 600px;
      height: 600px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(212, 175, 55, 0.08), transparent 70%);
      animation: pulse 3s ease-in-out infinite alternate;
      z-index: 0;
      pointer-events: none;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }
    @keyframes pulse {
      from {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0.12;
      }
      to {
        transform: translate(-50%, -50%) scale(1.2);
        opacity: 0.28;
      }
    }
    .scene {
      width: 260px;
      height: 260px;
      perspective: 1200px;
      position: relative;
      z-index: 2;
    }
    .coin {
      width: 100%;
      height: 100%;
      position: relative;
      transform-style: preserve-3d;
      animation: spin 5s linear infinite;
      border-radius: 50%;
      /* doble borde dorado 3D falso */
      border: 10px double #b8860b;
      box-shadow:
        0 0 40px #ffd700,
        inset 0 0 15px #d4af37;
      background: radial-gradient(circle at 30% 30%, #222222, #111111);
    }
    /* borde interior */
    .coin::before {
      content: '';
      position: absolute;
      inset: 12px;
      border-radius: 50%;
      border: 8px double #d4af37;
      pointer-events: none;
      filter: drop-shadow(0 0 20px #ffd700);
      box-shadow: 0 0 30px #ffd700;
    }
    .face {
      position: absolute;
      width: 100%;
      height: 100%;
      border-radius: 50%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      backface-visibility: hidden;
      text-align: center;
      user-select: none;
      font-weight: 900;
      text-shadow:
        0 0 12px #b8860b,
        0 0 25px #ffd700,
        0 0 40px #fffacd;
      filter: drop-shadow(0 0 15px #ffd700);
      color: #ffd700;
      letter-spacing: 0.2em;
      font-size: 5rem;
      font-family: 'Cinzel', serif;
      padding: 0 20px;
    }
    .face.front {
      transform: rotateY(0deg) translateZ(20px);
    }
    .face.front .top-text {
      font-size: 5.6rem;
      margin-bottom: 0.1em;
      text-transform: uppercase;
      animation: glowText 3s ease-in-out infinite alternate;
    }
    .face.front .bottom-text {
      font-size: 2.7rem;
      margin-top: 0.1em;
      color: #c9b037;
      letter-spacing: 0.5em;
      text-shadow: 0 0 18px #d4af37;
      filter: drop-shadow(0 0 10px #d4af37);
    }
    .face.back {
      transform: rotateY(180deg) translateZ(20px);
      font-size: 4.5rem;
      color: #b8860b;
      text-shadow:
        0 0 15px #b8860b,
        0 0 30px #ffd700;
      filter: drop-shadow(0 0 12px #b8860b);
      letter-spacing: 0.15em;
      display: flex;
      justify-content: center;
      align-items: center;
      font-weight: 700;
    }
    .shine {
      position: absolute;
      top: -15%;
      left: -15%;
      width: 130%;
      height: 130%;
      border-radius: 50%;
      background: linear-gradient(120deg, rgba(255,255,255,0.18), transparent 65%);
      animation: rotateShine 3s linear infinite;
      pointer-events: none;
      z-index: 5;
      filter: drop-shadow(0 0 10px #fffacd);
    }
    @keyframes spin {
      0% {
        transform: rotateY(0deg);
      }
      100% {
        transform: rotateY(360deg);
      }
    }
    @keyframes rotateShine {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }
    @keyframes glowText {
      0% {
        text-shadow:
          0 0 25px #ffd700,
          0 0 55px #ffd700,
          0 0 80px #fffacd;
        color: #ffd700;
      }
      100% {
        text-shadow:
          0 0 75px #fffacd,
          0 0 120px #fffacd,
          0 0 150px #fffacd;
        color: #fffacd;
      }
    }

    /* BOTON */
    #permission {
      position: fixed;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10;
    }
    button.start-button {
      background: linear-gradient(135deg, #d4af37, #b8860b);
      border: none;
      color: #000;
      font-family: 'Cinzel', serif;
      font-size: 1.5rem;
      font-weight: 900;
      padding: 1.2rem 3rem;
      border-radius: 16px;
      cursor: pointer;
      box-shadow:
        0 0 20px #ffd700,
        inset 0 0 10px #ffec73;
      transition: transform 0.2s ease;
      user-select: none;
      touch-action: manipulation;
    }
    button.start-button:hover {
      transform: scale(1.1);
    }

    /* Responsive */
    @media (max-width: 600px) {
      .scene {
        width: 180px;
        height: 180px;
      }
      .face {
        font-size: 3rem;
      }
      .face.front .top-text {
        font-size: 3.4rem;
      }
      .face.front .bottom-text {
        font-size: 1.8rem;
      }
      .face.back {
        font-size: 2.8rem;
      }
      button.start-button {
        font-size: 1.2rem;
        padding: 1rem 2rem;
      }
    }
  </style>
</head>
<body>
  <div class="glow-bg" aria-hidden="true"></div>

  <div class="scene" aria-label="Moneda girando Borleone">
    <div class="coin" id="coin" role="img" aria-live="polite" aria-atomic="true" tabindex="0">
      <div class="face front" aria-label="Cara de la moneda">
        <div class="top-text">BORLEONE</div>
        <div class="bottom-text">PREMIUM</div>
        <div class="shine"></div>
      </div>
      <div class="face back" aria-label="Reverso de la moneda con número uno">
        #1
        <div class="shine"></div>
      </div>
    </div>
  </div>

  <div id="permission" aria-label="Permiso para reproducir audio">
    <button class="start-button" onclick="startIntro()" aria-label="Botón para entrar y reproducir audio">ENTRAR</button>
  </div>

  <audio id="coinSound" preload="auto">
    <source src="https://cdn.pixabay.com/download/audio/2022/03/09/audio_c84bc84318.mp3?filename=coin-collision-sound-342335.mp3" type="audio/mpeg" />
  </audio>

  <script>
    const permissionBtn = document.getElementById('permission');
    const coin = document.getElementById('coin');
    const sound = document.getElementById('coinSound');

    function startIntro() {
      permissionBtn.style.display = 'none';

      sound.volume = 1;
      sound.play().catch((e) => {
        console.warn('Audio bloqueado o no permitido:', e);
      });

      // La moneda ya está girando, no necesitamos mostrar nada extra

      setTimeout(() => {
        // Fade out la moneda
        coin.style.transition = 'opacity 0.7s ease';
        coin.style.opacity = '0';

        setTimeout(() => {
          window.location.href = 'index.php'; // Cambia esta URL según tu necesidad
        }, 700);
      }, 3000);
    }

    window.addEventListener('load', () => {
      permissionBtn.style.display = 'block';
      coin.style.opacity = '1';
    });
  </script>
</body>
</html>
