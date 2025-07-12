<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>🎧 Reproductor - El Padrino</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600&display=swap" rel="stylesheet" />

  <style>
    body {
      background-color: #121212;
      color: #f1c40f;
      font-family: 'Oswald', sans-serif;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 1rem;
    }

    .player-container {
      background: #1e1e1e;
      border-radius: 20px;
      max-width: 400px;
      width: 100%;
      box-shadow: 0 8px 30px rgba(241, 196, 15, 0.3);
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .cover-art {
      border-radius: 15px;
      width: 100%;
      aspect-ratio: 1 / 1;
      object-fit: cover;
      box-shadow: 0 6px 20px rgba(241, 196, 15, 0.6);
    }

    h2 {
      font-weight: 700;
      text-align: center;
      margin: 0;
    }

    .controls {
      display: flex;
      justify-content: center;
      gap: 2rem;
      align-items: center;
      font-size: 1.8rem;
      color: #f1c40f;
      user-select: none;
    }

    .controls button {
      background: none;
      border: none;
      color: inherit;
      cursor: pointer;
      transition: color 0.3s ease;
    }

    .controls button:hover {
      color: #e67e22;
    }

    audio {
      display: none; /* Ocultamos el audio nativo */
    }

    .progress-container {
      width: 100%;
      background: #333;
      height: 6px;
      border-radius: 10px;
      cursor: pointer;
      margin: 0.5rem 0 1rem;
      box-shadow: inset 0 0 5px rgba(0,0,0,0.6);
    }

    .progress {
      background: #f1c40f;
      height: 100%;
      width: 0%;
      border-radius: 10px;
      transition: width 0.1s linear;
    }

    .time {
      display: flex;
      justify-content: space-between;
      font-size: 0.8rem;
      color: #bbb;
      user-select: none;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .playlist {
      max-height: 220px;
      overflow-y: auto;
      border-top: 1px solid #444;
      padding-top: 0.5rem;
    }

    .playlist-item {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.5rem 0;
      cursor: pointer;
      border-radius: 10px;
      transition: background-color 0.2s ease;
    }

    .playlist-item:hover {
      background-color: rgba(241, 196, 15, 0.1);
    }

    .playlist-item.active {
      background-color: rgba(241, 196, 15, 0.3);
    }

    .playlist-cover {
      width: 50px;
      height: 50px;
      border-radius: 8px;
      object-fit: cover;
      box-shadow: 0 3px 8px rgba(241, 196, 15, 0.6);
      flex-shrink: 0;
    }

    .playlist-title {
      flex-grow: 1;
      font-weight: 600;
      color: #f1c40f;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    @media (max-width: 480px) {
      .player-container {
        max-width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="player-container" role="region" aria-label="Reproductor de música">
    <img src="assets/video/fondo.png" alt="Portada de la canción" class="cover-art" id="cover-art" />
    <h2 id="track-title">Intro - El Padrino</h2>

    <div class="controls" role="group" aria-label="Controles de reproducción">
      <button id="prev-btn" aria-label="Canción anterior"><i class="bi bi-skip-backward-fill"></i></button>
      <button id="play-pause-btn" aria-label="Reproducir / Pausar"><i class="bi bi-play-fill" id="play-icon"></i><i class="bi bi-pause-fill d-none" id="pause-icon"></i></button>
      <button id="next-btn" aria-label="Siguiente canción"><i class="bi bi-skip-forward-fill"></i></button>
    </div>

    <div class="progress-container" id="progress-container" aria-label="Barra de progreso de la canción" tabindex="0">
      <div class="progress" id="progress"></div>
    </div>

    <div class="time" aria-live="polite" aria-atomic="true">
      <span id="current-time">0:00</span>
      <span id="duration">0:00</span>
    </div>

    <div class="playlist" id="playlist" aria-label="Lista de canciones">
      <!-- Lista generada por JS -->
    </div>

    <audio id="audio"></audio>
  </div>

  <script>
    // Lista de canciones - agrega más objetos aquí con ruta mp3 y portada
    const songs = [
      {
        title: "Intro - El Padrino",
        src: "assets/video/intro.mp3",
        cover: "assets/video/fondo.png"
      },
      {
        title: "Intro - El Padrino",
        src: "assets/video/intro.mp3",
        cover: "assets/video/fondo.png"
      }
      // Ejemplo para agregar más:
      // {
      //   title: "Otra canción",
      //   src: "assets/video/otra-cancion.mp3",
      //   cover: "assets/video/otra-portada.png"
      // }
    ];

    const audio = document.getElementById('audio');
    const playPauseBtn = document.getElementById('play-pause-btn');
    const playIcon = document.getElementById('play-icon');
    const pauseIcon = document.getElementById('pause-icon');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const coverArt = document.getElementById('cover-art');
    const trackTitle = document.getElementById('track-title');
    const progress = document.getElementById('progress');
    const progressContainer = document.getElementById('progress-container');
    const currentTimeEl = document.getElementById('current-time');
    const durationEl = document.getElementById('duration');
    const playlistEl = document.getElementById('playlist');

    let currentIndex = 0;
    let isPlaying = false;

    // Función para cargar canción
    function loadSong(index) {
      const song = songs[index];
      audio.src = song.src;
      coverArt.src = song.cover;
      trackTitle.textContent = song.title;

      // Marca canción activa en lista
      updatePlaylistActive();
    }

    // Reproducir canción
    function playSong() {
      audio.play();
      isPlaying = true;
      playIcon.classList.add('d-none');
      pauseIcon.classList.remove('d-none');
    }

    // Pausar canción
    function pauseSong() {
      audio.pause();
      isPlaying = false;
      playIcon.classList.remove('d-none');
      pauseIcon.classList.add('d-none');
    }

    // Alternar play/pause
    playPauseBtn.addEventListener('click', () => {
      if (isPlaying) pauseSong();
      else playSong();
    });

    // Canción anterior
    prevBtn.addEventListener('click', () => {
      currentIndex = (currentIndex - 1 + songs.length) % songs.length;
      loadSong(currentIndex);
      playSong();
    });

    // Canción siguiente
    nextBtn.addEventListener('click', () => {
      currentIndex = (currentIndex + 1) % songs.length;
      loadSong(currentIndex);
      playSong();
    });

    // Actualizar barra de progreso
    audio.addEventListener('timeupdate', () => {
      if(audio.duration){
        const progressPercent = (audio.currentTime / audio.duration) * 100;
        progress.style.width = progressPercent + '%';

        currentTimeEl.textContent = formatTime(audio.currentTime);
        durationEl.textContent = formatTime(audio.duration);
      }
    });

    // Hacer barra de progreso clickeable
    progressContainer.addEventListener('click', (e) => {
      const width = progressContainer.clientWidth;
      const clickX = e.offsetX;
      const duration = audio.duration;

      if(duration){
        audio.currentTime = (clickX / width) * duration;
      }
    });

    // Cuando termina una canción, pasar a la siguiente automáticamente
    audio.addEventListener('ended', () => {
      nextBtn.click();
    });

    // Formatear tiempo (segundos a mm:ss)
    function formatTime(seconds) {
      const minutes = Math.floor(seconds / 60) || 0;
      const secs = Math.floor(seconds % 60) || 0;
      return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
    }

    // Crear lista de canciones en el DOM
    function renderPlaylist() {
      playlistEl.innerHTML = '';
      songs.forEach((song, i) => {
        const item = document.createElement('div');
        item.classList.add('playlist-item');
        if(i === currentIndex) item.classList.add('active');
        item.setAttribute('role', 'button');
        item.setAttribute('tabindex', 0);
        item.setAttribute('aria-label', `Reproducir ${song.title}`);

        item.addEventListener('click', () => {
          currentIndex = i;
          loadSong(currentIndex);
          playSong();
        });
        item.addEventListener('keydown', (e) => {
          if(e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            currentIndex = i;
            loadSong(currentIndex);
            playSong();
          }
        });

        const img = document.createElement('img');
        img.src = song.cover;
        img.alt = `Portada de ${song.title}`;
        img.classList.add('playlist-cover');

        const title = document.createElement('div');
        title.textContent = song.title;
        title.classList.add('playlist-title');

        item.appendChild(img);
        item.appendChild(title);
        playlistEl.appendChild(item);
      });
    }

    // Actualizar estilo canción activa
    function updatePlaylistActive() {
      const items = playlistEl.querySelectorAll('.playlist-item');
      items.forEach((item, i) => {
        item.classList.toggle('active', i === currentIndex);
      });
    }

    // Iniciar reproductor
    loadSong(currentIndex);
    renderPlaylist();
  </script>

</body>
</html>
