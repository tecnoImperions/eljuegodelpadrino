<?php 
session_start();
require_once '../config.php';

if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    echo "Acceso no autorizado";
    exit;
}

$user = $_SESSION['usuario'];
$plan_usuario = $user['plan'];
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap');

  .modal-planes {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #eee;
    background: transparent;
  }

  .modal-planes .plans-container {
    max-width: 960px;
    margin: 0 auto;
    padding: 1rem 0;
  }

  .modal-planes .plans-header {
    text-align: center;
    font-size: 1.6rem;
    color: #f1c40f;
    font-weight: 700;
    margin-bottom: 1.5rem;
    user-select: none;
    letter-spacing: 1px;
  }

  .modal-planes .row {
    display: flex;
    gap: 1.8rem;
    flex-wrap: wrap;
    justify-content: center;
  }

  .modal-planes .plan-card {
    background: #1f1f1f;
    border-radius: 14px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.6);
    flex: 1 1 280px;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    cursor: default;
    display: flex;
    flex-direction: column;
    padding: 1.8rem 2rem;
    border: 4px solid transparent;
  }

  .modal-planes .plan-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 30px #f1c40f88;
  }

  .modal-planes .plan-header {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 1.8rem;
    text-align: center;
    margin-bottom: 1.2rem;
    letter-spacing: 1.5px;
    color: inherit;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.6rem;
  }

  .modal-planes .actual-label {
    background: #f1c40f;
    color: #fff;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 1rem;
    letter-spacing: 1px;
    user-select: none;
    box-shadow: 0 0 10px #f1c40f;
  }

  .modal-planes .plan-gratuito {
    border-color: #f1c40f;
    color: #f1c40f;
  }
  .modal-planes .plan-plus {
    border-color: #27ae60;
    color: #27ae60;
  }
  .modal-planes .plan-pro {
    border-color: #e74c3c;
    color: #e74c3c;
  }

  .modal-planes ul.benefits-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .modal-planes ul.benefits-list li {
    font-size: 1.1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    user-select: none;
    color: #eee;
  }

  .modal-planes ul.benefits-list li::before {
    content: "✓";
    color: currentColor;
    font-weight: 700;
    font-size: 1.3rem;
    line-height: 1;
  }

  .modal-planes .upgrade-text {
    margin-top: 3rem;
    font-style: italic;
    text-align: center;
    color: #bbb;
    font-size: 1.05rem;
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
    user-select: none;
  }

  @media (max-width: 640px) {
    .modal-planes .row {
      flex-direction: column;
      gap: 1.5rem;
    }
    .modal-planes .plan-card {
      flex: 1 1 100%;
      padding: 1.5rem;
    }
    .modal-planes .plan-header {
      font-size: 1.5rem;
    }
  }
</style>

<!-- 👇 Aquí se envuelve todo con .modal-planes -->
<div class="modal-planes">
  <div class="plans-container" role="main" aria-label="Planes de suscripción">
    <div class="plans-header">Elige tu plan</div>

    <div class="row">
      <!-- Plan Gratuito -->
      <div class="plan-card plan-gratuito <?= ($plan_usuario === 'gratuito' ? 'plan-actual' : '') ?>" tabindex="0" aria-label="Plan Gratuito">
        <div class="plan-header">
          Plan Gratuito
          <?php if ($plan_usuario === 'gratuito'): ?>
            <span class="actual-label" aria-label="Plan actual">ACTUAL</span>
          <?php endif; ?>
        </div>
        <ul class="benefits-list">
          <li>Acceso a sorteos básicos</li>
          <li>Máximo 1 participación diaria</li>
          <li>Soporte limitado</li>
        </ul>
      </div>

      <!-- Plan Plus -->
      <div class="plan-card plan-plus <?= ($plan_usuario === 'plus' ? 'plan-actual' : '') ?>" tabindex="0" aria-label="Plan Plus">
        <div class="plan-header">
          Plan Plus
          <?php if ($plan_usuario === 'plus'): ?>
            <span class="actual-label" aria-label="Plan actual">ACTUAL</span>
          <?php endif; ?>
        </div>
        <ul class="benefits-list">
          <li>Acceso a sorteos Plus exclusivos</li>
          <li>Hasta 5 participaciones diarias</li>
          <li>Soporte prioritario</li>
        </ul>
      </div>

      <!-- Plan Pro -->
      <div class="plan-card plan-pro <?= ($plan_usuario === 'pro' ? 'plan-actual' : '') ?>" tabindex="0" aria-label="Plan Pro">
        <div class="plan-header">
          Plan Pro
          <?php if ($plan_usuario === 'pro'): ?>
            <span class="actual-label" aria-label="Plan actual">ACTUAL</span>
          <?php endif; ?>
        </div>
        <ul class="benefits-list">
          <li>Acceso a todos los sorteos, incluye Pro</li>
          <li>Participaciones ilimitadas</li>
          <li>Soporte 24/7</li>
        </ul>
      </div>
    </div>

    <div class="upgrade-text" tabindex="0">
      <p>¿Quieres mejorar tu plan para más beneficios? Contáctanos o visita la tienda del juego.</p>
    </div>
  </div>
</div>
