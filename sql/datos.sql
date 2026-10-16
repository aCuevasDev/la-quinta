-- La Quinta · datos de ejemplo (4 categorías y 12 actividades)
-- Usar solo datos ficticios; nunca datos reales de personas.

-- Fuerza la lectura del fichero como UTF-8, aunque el cliente mysql use latin1 (RNF-10).
SET NAMES utf8mb4;

USE la_quinta;

-- Categorías
INSERT INTO categorias (id, nombre, descripcion) VALUES
  (1, 'Ocio y relación',   'Quedadas y actividades para conocer gente y pasar un buen rato.'),
  (2, 'Nuevas tecnologías', 'Aprende a usar el móvil, el ordenador e internet con calma.'),
  (3, 'Salud y deporte',   'Ejercicio suave y hábitos saludables adaptados a ti.'),
  (4, 'Cultura y memoria', 'Talleres para ejercitar la memoria y disfrutar de la cultura.')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion);

-- Actividades (3 por categoría)
INSERT INTO actividades (id, categoria_id, nombre, descripcion) VALUES
  (1,  1, 'Café y conversación',   'Encuentro distendido para charlar y conocer a otras personas.'),
  (2,  1, 'Taller de juegos de mesa', 'Partidas sencillas de cartas y juegos de mesa en grupo.'),
  (3,  1, 'Salida al parque',      'Paseo tranquilo por el parque y merienda compartida.'),
  (4,  2, 'Móvil desde cero',      'Aprende a hacer llamadas, escribir mensajes y hacer fotos.'),
  (5,  2, 'Internet sin miedo',    'Buscar información y usar el correo con seguridad.'),
  (6,  2, 'Videollamadas en familia', 'Cómo hablar por video con hijos y nietos.'), 
  (7,  3, 'Gimnasia suave',        'Ejercicios de movilidad y equilibrio adaptados a cada persona.'),
  (8,  3, 'Tai chi para principiantes', 'Movimientos lentos para relajarse y mejorar el equilibrio.'),
  (9,  3, 'Alimentación saludable', 'Consejos sencillos para comer mejor en el día a día.'),
  (10, 4, 'Memoria en acción',     'Juegos y ejercicios para mantener la mente despierta.'),
  (11, 4, 'Club de lectura',       'Leemos y comentamos un relato corto en grupo.'),
  (12, 4, 'Historias de nuestro barrio', 'Recordamos y compartimos la historia del barrio.')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), descripcion = VALUES(descripcion);
