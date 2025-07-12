<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal de Contactos Interactivo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body style="background: #1a1a1a; padding: 10px; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;">

<!-- Modal de Contactos -->
<div class="modal-contacto-wrapper">
    <div class="modal-contacto">

        <!-- BOTÓN VOLVER -->
        <button class="btn-volver" onclick="volverAtras()">
            <i class="bi bi-arrow-left"></i>
            <span class="volver-text">Volver</span>
            <div class="btn-glow-volver"></div>
        </button>

        <div class="modal-header-animated">
            <h5 class="modal-title">
                <span class="icon-pulse">📱</span>
                <span class="title-text">Conéctate con Nosotros</span>
            </h5>
            <div class="title-underline"></div>
        </div>

        <div class="contactos-grid">
            <a href="https://wa.me/59163488086?text=Hola,%20quiero%20más%20información" 
               target="_blank" 
               class="contact-btn whatsapp" 
               data-platform="WhatsApp">
                <div class="btn-content">
                    <i class="bi bi-whatsapp icon-animated"></i>
                    <span class="btn-text">WhatsApp</span>
                    <div class="btn-glow"></div>
                </div>
                <div class="ripple-effect"></div>
            </a>

            <a href="https://t.me/+1VWOkOrUuPc3ZTM5" 
               target="_blank" 
               class="contact-btn telegram"
               data-platform="Telegram">
                <div class="btn-content">
                    <i class="bi bi-telegram icon-animated"></i>
                    <span class="btn-text">Telegram</span>
                    <div class="btn-glow"></div>
                </div>
                <div class="ripple-effect"></div>
            </a>

            <a href="https://www.tiktok.com/@elpadrino1013?_t=ZM-8w8Feidd3FW&_r=1" 
               target="_blank" 
               class="contact-btn tiktok"
               data-platform="TikTok">
                <div class="btn-content">
                    <i class="bi bi-tiktok icon-animated"></i>
                    <span class="btn-text">TikTok</span>
                    <div class="btn-glow"></div>
                </div>
                <div class="ripple-effect"></div>
            </a>

            <a href="https://www.youtube.com/@Padrino-c3c7u" 
               target="_blank" 
               class="contact-btn youtube"
               data-platform="YouTube">
                <div class="btn-content">
                    <i class="bi bi-youtube icon-animated"></i>
                    <span class="btn-text">YouTube</span>
                    <div class="btn-glow"></div>
                </div>
                <div class="ripple-effect"></div>
            </a>
        </div>

        <div class="correo-section">
            <div class="email-header">
                <span class="email-icon">📧</span>
                <p class="email-text">También puedes escribirnos:</p>
            </div>
            
            <div class="email-display">
                <span class="email-address" id="emailDisplay">borleone101@gmail.com</span>
            </div>

            <div class="correo-btns">
                <button class="copy-btn" onclick="copiarCorreo()" id="copyBtn">
                    <i class="bi bi-clipboard"></i>
                    <span class="btn-text">Copiar correo</span>
                    <div class="btn-ripple"></div>
                </button>
                
                <a href="mailto:borleone101@gmail.com?subject=Consulta&body=Hola, quiero más información sobre..." 
                   class="mail-btn">
                    <i class="bi bi-envelope"></i>
                    <span class="btn-text">Enviar mensaje</span>
                    <div class="btn-ripple"></div>
                </a>
            </div>

            <div class="notification-toast" id="copySuccess">
                <i class="bi bi-check-circle"></i>
                <span>¡Correo copiado al portapapeles!</span>
            </div>
        </div>

        <!-- Partículas de fondo -->
        <div class="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
    </div>
</div>

<style>
* {
    box-sizing: border-box;
}

.modal-contacto-wrapper {
    position: relative;
    width: 100%;
    max-width: 650px;
    margin: 0 auto;
    padding: 0 10px;
}

.modal-contacto {
    font-family: 'Oswald', sans-serif;
    color: #fff;
    background: linear-gradient(135deg, rgba(20, 20, 20, 0.95), rgba(40, 40, 40, 0.9));
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: clamp(1.5rem, 4vw, 2.5rem);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    width: 100%;
    min-height: auto;
}

.modal-contacto::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent, rgba(241, 196, 15, 0.05), transparent);
    animation: shimmer 3s ease-in-out infinite;
    pointer-events: none;
}

/* ESTILOS DEL BOTÓN VOLVER */
.btn-volver {
    position: absolute;
    top: clamp(1rem, 3vw, 1.5rem);
    left: clamp(1rem, 3vw, 1.5rem);
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.9), rgba(41, 128, 185, 0.9));
    border: 2px solid rgba(52, 152, 219, 0.3);
    border-radius: 12px;
    padding: clamp(0.6rem, 2vw, 0.8rem) clamp(0.8rem, 2vw, 1rem);
    color: white;
    font-family: 'Oswald', sans-serif;
    font-weight: 600;
    font-size: clamp(0.8rem, 2vw, 0.9rem);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: clamp(0.3rem, 1vw, 0.5rem);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: 10;
    backdrop-filter: blur(10px);
    overflow: hidden;
    transform: translateX(-100px);
    opacity: 0;
    animation: slideInLeft 0.6s ease-out 0.2s forwards;
}

@keyframes slideInLeft {
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.btn-volver:hover {
    background: linear-gradient(135deg, rgba(41, 128, 185, 1), rgba(31, 97, 141, 1));
    border-color: rgba(52, 152, 219, 0.8);
    transform: translateX(-3px) translateY(-2px);
    box-shadow: 0 8px 16px rgba(52, 152, 219, 0.3);
}

.btn-volver:hover .btn-glow-volver {
    width: 100px;
    height: 100px;
}

.btn-volver:hover i {
    transform: translateX(-2px);
}

.btn-volver:active {
    transform: translateX(-1px) translateY(-1px) scale(0.95);
}

.btn-glow-volver {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: all 0.4s ease;
    pointer-events: none;
}

.volver-text {
    transition: transform 0.3s ease;
}

.btn-volver:hover .volver-text {
    transform: translateX(-1px);
}

@keyframes shimmer {
    0%, 100% { transform: translateX(-100%); }
    50% { transform: translateX(100%); }
}

.modal-header-animated {
    text-align: center;
    margin-bottom: clamp(1.5rem, 4vw, 2.5rem);
}

.modal-title {
    font-size: clamp(1.3rem, 4vw, 1.8rem);
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: clamp(0.5rem, 2vw, 0.75rem);
    flex-wrap: wrap;
}

.icon-pulse {
    animation: pulse 2s ease-in-out infinite;
    font-size: clamp(1.5rem, 4vw, 2rem);
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.title-text {
    background: linear-gradient(45deg, #f1c40f, #f39c12, #e67e22);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: textGlow 2s ease-in-out infinite alternate;
}

@keyframes textGlow {
    from { filter: brightness(1); }
    to { filter: brightness(1.2); }
}

.title-underline {
    height: 3px;
    background: linear-gradient(90deg, transparent, #f1c40f, transparent);
    margin: 1rem auto;
    width: clamp(80px, 20vw, 120px);
    border-radius: 2px;
    animation: expand 2s ease-in-out infinite alternate;
}

@keyframes expand {
    from { width: clamp(60px, 15vw, 80px); }
    to { width: clamp(100px, 25vw, 140px); }
}

.contactos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(180px, 100%), 1fr));
    gap: clamp(1rem, 3vw, 1.5rem);
    margin-bottom: clamp(1.5rem, 4vw, 2.5rem);
}

.contact-btn {
    position: relative;
    padding: clamp(1rem, 3vw, 1.2rem) clamp(1rem, 3vw, 1.5rem);
    border-radius: 15px;
    font-size: clamp(0.95rem, 2.5vw, 1.1rem);
    font-weight: 600;
    text-decoration: none;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 2px solid transparent;
    cursor: pointer;
    transform-style: preserve-3d;
    min-height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: clamp(0.5rem, 2vw, 0.75rem);
    width: 100%;
}

.icon-animated {
    font-size: clamp(1.2rem, 3vw, 1.4rem);
    transition: transform 0.3s ease;
    flex-shrink: 0;
}

.btn-text {
    transition: transform 0.3s ease;
    white-space: nowrap;
}

.btn-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: all 0.6s ease;
}

.contact-btn:hover .btn-glow {
    width: 300px;
    height: 300px;
}

.contact-btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
}

.contact-btn:hover .icon-animated {
    transform: rotate(360deg) scale(1.1);
}

.contact-btn:hover .btn-text {
    transform: translateY(-1px);
}

.contact-btn:active {
    transform: translateY(-2px) scale(0.98);
}

.whatsapp { 
    background: linear-gradient(135deg, #25D366, #128C7E);
    color: white;
}

.whatsapp:hover {
    background: linear-gradient(135deg, #128C7E, #25D366);
    border-color: #25D366;
}

.telegram { 
    background: linear-gradient(135deg, #0088cc, #005999);
    color: white;
}

.telegram:hover {
    background: linear-gradient(135deg, #005999, #0088cc);
    border-color: #0088cc;
}

.tiktok { 
    background: linear-gradient(135deg, #000000, #333333);
    color: white;
}

.tiktok:hover {
    background: linear-gradient(135deg, #333333, #000000);
    border-color: #ff0050;
}

.youtube { 
    background: linear-gradient(135deg, #FF0000, #CC0000);
    color: white;
}

.youtube:hover {
    background: linear-gradient(135deg, #CC0000, #FF0000);
    border-color: #FF0000;
}

.ripple-effect {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    pointer-events: none;
}

.contact-btn:active .ripple-effect {
    animation: ripple 0.6s ease-out;
}

@keyframes ripple {
    to {
        width: 300px;
        height: 300px;
        opacity: 0;
    }
}

.correo-section {
    text-align: center;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    padding: clamp(1.5rem, 4vw, 2rem);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.email-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: clamp(0.3rem, 1vw, 0.5rem);
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.email-icon {
    font-size: clamp(1.2rem, 3vw, 1.5rem);
    animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

.email-text {
    margin: 0;
    font-size: clamp(0.9rem, 2.5vw, 1rem);
    color: rgba(255, 255, 255, 0.9);
}

.email-display {
    background: rgba(0, 0, 0, 0.3);
    border-radius: 10px;
    padding: clamp(0.6rem, 2vw, 0.75rem) clamp(0.8rem, 2vw, 1rem);
    margin: 1rem 0;
    border: 1px solid rgba(241, 196, 15, 0.3);
}

.email-address {
    font-family: 'Courier New', monospace;
    font-size: clamp(0.8rem, 2.5vw, 1rem);
    color: #f1c40f;
    letter-spacing: 1px;
    word-break: break-all;
}

.correo-btns {
    display: flex;
    justify-content: center;
    gap: clamp(0.8rem, 2vw, 1rem);
    flex-wrap: wrap;
    margin-top: clamp(1rem, 3vw, 1.5rem);
}

.copy-btn,
.mail-btn {
    position: relative;
    padding: clamp(0.7rem, 2vw, 0.8rem) clamp(1rem, 3vw, 1.5rem);
    border-radius: 12px;
    font-size: clamp(0.85rem, 2vw, 0.95rem);
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    align-items: center;
    gap: clamp(0.3rem, 1vw, 0.5rem);
    text-decoration: none;
    overflow: hidden;
    min-width: clamp(120px, 25vw, 140px);
    justify-content: center;
}

.copy-btn {
    background: linear-gradient(135deg, #f1c40f, #f39c12);
    color: #000;
}

.copy-btn:hover {
    background: linear-gradient(135deg, #f39c12, #e67e22);
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(241, 196, 15, 0.4);
}

.mail-btn {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: #fff;
}

.mail-btn:hover {
    background: linear-gradient(135deg, #2980b9, #1f618d);
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(52, 152, 219, 0.4);
}

.btn-ripple {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    pointer-events: none;
}

.copy-btn:active .btn-ripple,
.mail-btn:active .btn-ripple {
    animation: ripple 0.6s ease-out;
}

.notification-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white;
    padding: clamp(0.8rem, 2vw, 1rem) clamp(1rem, 3vw, 1.5rem);
    border-radius: 12px;
    box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
    display: none;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    z-index: 1000;
    animation: slideIn 0.4s ease-out;
    font-size: clamp(0.8rem, 2vw, 0.9rem);
    max-width: calc(100vw - 40px);
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.particles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    overflow: hidden;
}

.particle {
    position: absolute;
    width: clamp(3px, 1vw, 4px);
    height: clamp(3px, 1vw, 4px);
    background: rgba(241, 196, 15, 0.6);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

.particle:nth-child(1) {
    left: 10%;
    top: 20%;
    animation-delay: 0s;
}

.particle:nth-child(2) {
    left: 80%;
    top: 30%;
    animation-delay: 1s;
}

.particle:nth-child(3) {
    left: 20%;
    top: 80%;
    animation-delay: 2s;
}

.particle:nth-child(4) {
    left: 90%;
    top: 70%;
    animation-delay: 3s;
}

.particle:nth-child(5) {
    left: 50%;
    top: 10%;
    animation-delay: 4s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
        opacity: 0.7;
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
        opacity: 1;
    }
}

/* Mejoras específicas para dispositivos móviles */
@media (max-width: 768px) {
    body {
        padding: 5px;
    }
    
    .contactos-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-btn:hover {
        transform: translateY(-2px);
    }
    
    .correo-btns {
        flex-direction: column;
        align-items: center;
    }
    
    .copy-btn,
    .mail-btn {
        width: 100%;
        max-width: 280px;
    }
    
    .notification-toast {
        position: fixed;
        top: auto;
        bottom: 20px;
        left: 20px;
        right: 20px;
        text-align: center;
        transform: translateX(0);
    }
    
    /* Ajuste del botón volver en móviles */
    .btn-volver {
        top: 0.8rem;
        left: 0.8rem;
        padding: 0.5rem 0.7rem;
    }
    
    .volver-text {
        display: none;
    }
    
    @keyframes slideIn {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
}

/* Ajustes para pantallas muy pequeñas */
@media (max-width: 380px) {
    .modal-title {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .email-header {
        flex-direction: column;
        gap: 0.3rem;
    }
    
    .email-address {
        font-size: 0.75rem;
    }
}

/* Ajustes para pantallas grandes */
@media (min-width: 1200px) {
    .contactos-grid {
        grid-template-columns: repeat(2, 1fr);
        max-width: 500px;
        margin: 0 auto 2.5rem auto;
    }
    
    .contact-btn:hover {
        transform: translateY(-8px) rotateX(10deg);
    }
}

/* Mejoras para tablets */
@media (min-width: 769px) and (max-width: 1024px) {
    .contactos-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Soporte para modo landscape en móviles */
@media (max-height: 500px) and (orientation: landscape) {
    .modal-contacto {
        padding: 1rem;
    }
    
    .modal-header-animated {
        margin-bottom: 1rem;
    }
    
    .correo-section {
        padding: 1rem;
    }
    
    .btn-volver {
        top: 0.5rem;
        left: 0.5rem;
    }
}
</style>

<script>
    function copiarCorreo() {
    const correo = 'borleone101@gmail.com';
    const copyBtn = document.getElementById('copyBtn');
    const notification = document.getElementById('copySuccess');
    
    // Animación del botón
    copyBtn.style.transform = 'scale(0.95)';
    
    navigator.clipboard.writeText(correo).then(() => {
        // Mostrar notificación
        notification.style.display = 'flex';
        
        // Cambiar el contenido del botón temporalmente
        const originalContent = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="bi bi-check"></i><span class="btn-text">¡Copiado!</span>';
        copyBtn.style.background = 'linear-gradient(135deg, #2ecc71, #27ae60)';
        
        setTimeout(() => {
            copyBtn.style.transform = 'scale(1)';
        }, 150);
        
        // Ocultar notificación y restaurar botón
        setTimeout(() => {
            notification.style.animation = 'slideIn 0.4s ease-out reverse';
            setTimeout(() => {
                notification.style.display = 'none';
                notification.style.animation = 'slideIn 0.4s ease-out';
            }, 400);
            
            copyBtn.innerHTML = originalContent;
            copyBtn.style.background = 'linear-gradient(135deg, #f1c40f, #f39c12)';
        }, 2500);
    }).catch(err => {
        console.error('Error al copiar:', err);
        // Feedback de error
        copyBtn.innerHTML = '<i class="bi bi-x"></i><span class="btn-text">Error</span>';
        copyBtn.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
        
        setTimeout(() => {
            copyBtn.innerHTML = '<i class="bi bi-clipboard"></i><span class="btn-text">Copiar correo</span>';
            copyBtn.style.background = 'linear-gradient(135deg, #f1c40f, #f39c12)';
            copyBtn.style.transform = 'scale(1)';
        }, 2000);
    });
}

// FUNCIÓN DEL BOTÓN VOLVER
function volverAtras() {
    const btnVolver = document.querySelector('.btn-volver');
    
    // Crear efecto visual al hacer clic
    btnVolver.style.transform = 'translateX(-1px) translateY(-1px) scale(0.95)';
    
    // Crear efecto de partículas
    createClickEffect(btnVolver.offsetLeft + btnVolver.offsetWidth/2, 
                     btnVolver.offsetTop + btnVolver.offsetHeight/2);
    
    // Restaurar el botón después de un momento
    setTimeout(() => {
        btnVolver.style.transform = 'translateX(0) translateY(0) scale(1)';
    }, 150);
    
    // Volver a la página anterior del navegador
    window.history.back();
}

// Función para crear efecto de partículas al hacer clic
function createClickEffect(x, y) {
    const colors = ['#f1c40f', '#e67e22', '#e74c3c', '#9b59b6', '#3498db'];
    
    for (let i = 0; i < 6; i++) {
        const particle = document.createElement('div');
        particle.style.position = 'fixed';
        particle.style.left = x + 'px';
        particle.style.top = y + 'px';
        particle.style.width = '4px';
        particle.style.height = '4px';
        particle.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        particle.style.borderRadius = '50%';
        particle.style.pointerEvents = 'none';
        particle.style.zIndex = '10000';
        
        const angle = (Math.PI * 2 * i) / 6;
        const velocity = 15 + Math.random() * 10;
        const vx = Math.cos(angle) * velocity;
        const vy = Math.sin(angle) * velocity;
        
        document.body.appendChild(particle);
        
        let opacity = 1;
        let posX = x;
        let posY = y;
        
        const animate = () => {
            posX += vx;
            posY += vy;
            opacity -= 0.03;
            
            particle.style.left = posX + 'px';
            particle.style.top = posY + 'px';
            particle.style.opacity = opacity;
            
            if (opacity > 0) {
                requestAnimationFrame(animate);
            } else {
                document.body.removeChild(particle);
            }
        };
        
        requestAnimationFrame(animate);
    }
}

// Efectos adicionales al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Agregar efectos de hover mejorados a los botones de contacto
    const contactButtons = document.querySelectorAll('.contact-btn');
    
    contactButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
        
        button.addEventListener('click', function(e) {
            // Crear efecto ripple
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const ripple = document.createElement('div');
            ripple.style.position = 'absolute';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.style.width = '0';
            ripple.style.height = '0';
            ripple.style.background = 'rgba(255, 255, 255, 0.5)';
            ripple.style.borderRadius = '50%';
            ripple.style.transform = 'translate(-50%, -50%)';
            ripple.style.animation = 'ripple 0.6s ease-out';
            ripple.style.pointerEvents = 'none';
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Animación de entrada para los elementos
    const elementsToAnimate = document.querySelectorAll('.contact-btn, .correo-section');
    elementsToAnimate.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            element.style.transition = 'all 0.6s ease-out';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 100 + 300);
    });
});

// Función para manejar el redimensionamiento de ventana
window.addEventListener('resize', function() {
    // Recalcular posiciones si es necesario
    const modal = document.querySelector('.modal-contacto');
    if (modal) {
        modal.style.transition = 'all 0.3s ease';
    }
});

// Prevenir errores de clipboard en navegadores no compatibles
if (!navigator.clipboard) {
    console.warn('Clipboard API no disponible');
    
    // Función alternativa para copiar texto
    function fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            return successful;
        } catch (err) {
            document.body.removeChild(textArea);
            return false;
        }
    }
    
    // Reemplazar la función copiarCorreo si no hay soporte para clipboard
    window.copiarCorreo = function() {
        const correo = 'borleone101@gmail.com';
        const copyBtn = document.getElementById('copyBtn');
        const notification = document.getElementById('copySuccess');
        
        copyBtn.style.transform = 'scale(0.95)';
        
        const success = fallbackCopyTextToClipboard(correo);
        
        if (success) {
            notification.style.display = 'flex';
            const originalContent = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="bi bi-check"></i><span class="btn-text">¡Copiado!</span>';
            copyBtn.style.background = 'linear-gradient(135deg, #2ecc71, #27ae60)';
            
            setTimeout(() => {
                copyBtn.style.transform = 'scale(1)';
            }, 150);
            
            setTimeout(() => {
                notification.style.display = 'none';
                copyBtn.innerHTML = originalContent;
                copyBtn.style.background = 'linear-gradient(135deg, #f1c40f, #f39c12)';
            }, 2500);
        } else {
            copyBtn.innerHTML = '<i class="bi bi-x"></i><span class="btn-text">Error</span>';
            copyBtn.style.background = 'linear-gradient(135deg, #e74c3c, #c0392b)';
            
            setTimeout(() => {
                copyBtn.innerHTML = '<i class="bi bi-clipboard"></i><span class="btn-text">Copiar correo</span>';
                copyBtn.style.background = 'linear-gradient(135deg, #f1c40f, #f39c12)';
                copyBtn.style.transform = 'scale(1)';
            }, 2000);
        }
    };
}
</script>
</body>
</html>
