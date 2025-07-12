<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>BORLEONE INTRO</title>

  <!-- Tamaño y escala en móviles -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <!-- Color oscuro para la barra del navegador en móviles -->
  <meta name="theme-color" content="#000000" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- Fuente elegante -->
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&display=swap" rel="stylesheet">

  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    html, body {
      background: radial-gradient(circle at center, #000000, #0c0c0c);
      height: 100vh;
      font-family: 'Cinzel', serif;
      color: #fff;
      overflow: hidden;
      touch-action: manipulation;
    }

    body {
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }

    .scene {
      width: 250px;
      height: 250px;
      perspective: 1200px;
    }

    .coin {
      width: 100%;
      height: 100%;
      position: relative;
      transform-style: preserve-3d;
      animation: spin 4s linear infinite;
    }

    .face {
      position: absolute;
      width: 100%;
      height: 100%;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      font-weight: bold;
      backface-visibility: hidden;
      color: #f8e38c;
      text-shadow: 0 0 10px #d4af37, 0 0 20px #000;
      background: radial-gradient(circle at 30% 30%, #1e1e1e, #121212);
      border: 6px double #d4af37;
      box-shadow:
        0 0 15px rgba(255, 215, 0, 0.3),
        inset 0 0 10px rgba(255, 255, 255, 0.1),
        inset 0 0 30px #222;
    }

    .face.front { transform: rotateY(0deg); }
    .face.back { transform: rotateY(180deg); }

    @keyframes spin {
      0%   { transform: rotateY(0deg); }
      100% { transform: rotateY(360deg); }
    }

    .shine {
      position: absolute;
      width: 100%;
      height: 100%;
      border-radius: 50%;
      background: linear-gradient(120deg, rgba(255,255,255,0.12), transparent 70%);
      animation: rotateShine 3s linear infinite;
      z-index: 2;
      pointer-events: none;
    }

    @keyframes rotateShine {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .glow-bg {
      position: absolute;
      width: 600px;
      height: 600px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(212, 175, 55, 0.08), transparent 70%);
      animation: pulse 3s ease-in-out infinite alternate;
      z-index: 0;
    }

    @keyframes pulse {
      from { transform: scale(1); opacity: 0.1; }
      to   { transform: scale(1.2); opacity: 0.3; }
    }

    @media (max-width: 600px) {
      .scene {
        width: 160px;
        height: 160px;
      }
      .face {
        font-size: 1.3rem;
      }
    }
  </style>
</head>
<body>
  <div class="glow-bg"></div>
  <div class="scene">
    <div class="coin">
      <div class="face front">BORLEONE<div class="shine"></div></div>
      <div class="face back">#1<div class="shine"></div></div>
    </div>
  </div>
</body>
</html>
