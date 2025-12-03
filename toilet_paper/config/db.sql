DROP DATABASE IF EXISTS papel_app;
CREATE DATABASE papel_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE papel_app;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    puntos INT DEFAULT 100,
    rollos_actuales INT DEFAULT 0,
    rollos_total_usados INT DEFAULT 0,
    es_admin TINYINT DEFAULT 0,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE eventos_diarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    puntos INT NOT NULL
);

CREATE TABLE eventos_completados (
    usuario_id INT,
    evento_id INT,
    fecha DATE,
    PRIMARY KEY (usuario_id, evento_id, fecha)
);

CREATE TABLE transacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    tipo VARCHAR(20),
    cantidad INT DEFAULT 0,
    puntos INT DEFAULT 0,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Admin fácil de recordar
INSERT INTO usuarios (nombre, email, password, es_admin, puntos) VALUES 
('Admin', 'admin@admin.com', '1234', 1, 99999);

-- 4 eventos iniciales
INSERT INTO eventos_diarios (titulo, descripcion, puntos) VALUES
('Baño ecológico', 'Usaste bidé o servilleta de tela', 20),
('Reto 24h sin papel', '¡Un día entero sin usar rollos', 40),
('Comparte la app', 'Comparte con un amigo', 25),
('Foto creativa', 'Sube una foto divertida con un rollo', 30);