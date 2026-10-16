-- La Quinta · esquema de la base de datos
-- Importar antes que datos.sql. Se puede volver a importar sin problema.

CREATE DATABASE IF NOT EXISTS la_quinta
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE la_quinta;

-- Personas registradas. La identidad es nombre + apellidos (D4, DA-02).
-- La collation utf8mb4_unicode_ci no distingue mayúsculas ni tildes al comparar (CL-09);
-- los espacios sobrantes se normalizan en PHP antes de guardar y de buscar (RF-02, RF-04).
CREATE TABLE IF NOT EXISTS usuarios (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  nombre        VARCHAR(50)  NOT NULL,
  apellidos     VARCHAR(80)  NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  creado_en     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuarios_nombre_apellidos (nombre, apellidos)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4 categorías (RF-09)
CREATE TABLE IF NOT EXISTS categorias (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(80)  NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  UNIQUE KEY uq_categorias_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3 actividades por categoría (RF-10)
CREATE TABLE IF NOT EXISTS actividades (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT          NOT NULL,
  nombre       VARCHAR(120) NOT NULL,
  descripcion  VARCHAR(255) NOT NULL,
  CONSTRAINT fk_actividades_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reservas: una actividad en un día concreto (RF-11 a RF-16).
-- UNIQUE evita que el mismo usuario reserve lo mismo el mismo día (RF-14).
CREATE TABLE IF NOT EXISTS reservas (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id   INT      NOT NULL,
  actividad_id INT      NOT NULL,
  fecha        DATE     NOT NULL,
  creado_en    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_reservas_usuario_actividad_fecha (usuario_id, actividad_id, fecha),
  CONSTRAINT fk_reservas_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id),
  CONSTRAINT fk_reservas_actividad
    FOREIGN KEY (actividad_id) REFERENCES actividades (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
