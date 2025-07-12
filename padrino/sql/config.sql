-- 🔄 Eliminar y crear base de datos
DROP DATABASE IF EXISTS padrino;
CREATE DATABASE padrino CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE padrino;

-- 🧑‍💼 Tabla de usuarios
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(100) NOT NULL UNIQUE,
  contrasena VARCHAR(255) NOT NULL,
  celular VARCHAR(20),
  rol ENUM('usuario', 'admin', 'trabajador') DEFAULT 'usuario',
  plan ENUM('gratuito', 'plus', 'pro') DEFAULT 'gratuito',
  estado ENUM('activo', 'bloqueado') DEFAULT 'activo',
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  foto_perfil VARCHAR(255) DEFAULT NULL,
  info_adicional TEXT DEFAULT NULL,
  reset_token VARCHAR(255) NULL,
  reset_expira DATETIME NULL
) ENGINE=InnoDB;

-- 🎯 Tabla de sorteos
CREATE TABLE sorteos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(100) NOT NULL,
  descripcion TEXT,
  plan ENUM('gratuito', 'plus', 'pro') DEFAULT 'gratuito',
  precio_entrada DECIMAL(10,2) DEFAULT 5.00,
  max_participantes INT DEFAULT 25,
  fecha_inicio DATETIME,
  fecha_cierre DATETIME,
  estado ENUM('activo', 'cerrado', 'finalizado', 'inactivo') DEFAULT 'activo',
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 🎟️ Tabla de participaciones
CREATE TABLE participaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_sorteo INT NOT NULL,
  cantidad_boletos INT DEFAULT 1,
  estado ENUM('pendiente', 'comprobado', 'rechazado', 'ganador', 'perdedor') DEFAULT 'pendiente',
  comprobante_imagen VARCHAR(255),
  lugar ENUM('ninguno', 'primer', 'segundo', 'tercer') DEFAULT 'ninguno',
  fecha_participacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (id_sorteo) REFERENCES sorteos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 🏆 Tabla de ganadores
CREATE TABLE ganadores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_participacion INT NOT NULL,
  qr_pago_premio VARCHAR(255),
  estado_pago ENUM('pendiente', 'pagado') DEFAULT 'pendiente',
  fecha_ganado DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_participacion) REFERENCES participaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 👨‍🔧 Tabla de trabajadores
CREATE TABLE trabajadores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_sorteo INT NOT NULL,
  estado ENUM('activo', 'bloqueado') DEFAULT 'activo',
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (id_sorteo) REFERENCES sorteos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 🕘 Historial de sorteos
CREATE TABLE historial_sorteos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_sorteo INT NOT NULL,
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  ganadores JSON,
  FOREIGN KEY (id_sorteo) REFERENCES sorteos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 💸 Tabla de transacciones
CREATE TABLE IF NOT EXISTS transacciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  tipo ENUM('depositar', 'retirar', 'cobrar', 'pagar') NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);



-- 🆓 Sorteos Gratuitos
INSERT INTO sorteos (titulo, descripcion, plan, precio_entrada, max_participantes, fecha_inicio, fecha_cierre, estado)
VALUES
('Sorteo Gratuito 1', 'Participa gratis por premios increíbles', 'gratuito', 5.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 5 DAY), 'activo'),
('Sorteo Gratuito 2', 'Nuevo sorteo gratuito disponible', 'gratuito', 5.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 6 DAY), 'activo'),
('Sorteo Gratuito 3', 'Disfruta premios sin pagar nada', 'gratuito', 5.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'activo'),
('Sorteo Gratuito 4', 'Sorteo exclusivo para usuarios gratuitos', 'gratuito', 5.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 8 DAY), 'activo'),
('Sorteo Gratuito 5', 'Premios sorpresa sin costo', 'gratuito', 5.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 9 DAY), 'activo'),
('Sorteo Gratuito 6', 'Último sorteo gratuito del mes', 'gratuito', 5.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 10 DAY), 'activo');

-- 🟡 Sorteos Plus
INSERT INTO sorteos (titulo, descripcion, plan, precio_entrada, max_participantes, fecha_inicio, fecha_cierre, estado)
VALUES
('Sorteo Plus 1', 'Sorteo exclusivo para usuarios Plus', 'plus', 25.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 5 DAY), 'activo'),
('Sorteo Plus 2', 'Gana premios participando con Plus', 'plus', 25.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 6 DAY), 'activo'),
('Sorteo Plus 3', 'Más sorteos para usuarios Plus', 'plus', 25.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'activo'),
('Sorteo Plus 4', 'Premios exclusivos para Plus', 'plus', 25.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 8 DAY), 'activo'),
('Sorteo Plus 5', 'Boletos accesibles para grandes premios', 'plus', 25.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 9 DAY), 'activo'),
('Sorteo Plus 6', 'Evento especial para clientes Plus', 'plus', 25.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 10 DAY), 'activo');

-- 🔴 Sorteos Pro
INSERT INTO sorteos (titulo, descripcion, plan, precio_entrada, max_participantes, fecha_inicio, fecha_cierre, estado)
VALUES
('Sorteo Pro 1', 'Premios de alto valor para usuarios Pro', 'pro', 100.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 5 DAY), 'activo'),
('Sorteo Pro 2', 'Sorteo de lujo solo para Pro', 'pro', 100.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 6 DAY), 'activo'),
('Sorteo Pro 3', 'Alta recompensa para usuarios Pro', 'pro', 100.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'activo'),
('Sorteo Pro 4', 'Solo los Pro entran a este gran sorteo', 'pro', 100.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 8 DAY), 'activo'),
('Sorteo Pro 5', 'Evento premium para usuarios Pro', 'pro', 100.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 9 DAY), 'activo'),
('Sorteo Pro 6', 'Sorteo especial de fin de mes Pro', 'pro', 100.00, 25, NOW(), DATE_ADD(NOW(), INTERVAL 10 DAY), 'activo');






CREATE TABLE historial_ganadores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_historial INT,
  id_usuario INT,
  lugar ENUM('primer', 'segundo', 'tercer'),
  FOREIGN KEY (id_historial) REFERENCES historial_sorteos(id) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);


-- Índices recomendados
CREATE INDEX idx_participaciones_sorteo_estado ON participaciones(id_sorteo, estado);
CREATE INDEX idx_ganadores_participacion ON ganadores(id_participacion);
CREATE INDEX idx_participaciones_usuario_sorteo ON participaciones(id_usuario, id_sorteo);