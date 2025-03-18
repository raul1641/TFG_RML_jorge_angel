CREATE DATABASE IF NOT EXISTS REGISTRO_LIBROS;
USE REGISTRO_LIBROS;

-- Tabla Usuario
DROP TABLE IF EXISTS Usuario;
CREATE TABLE Usuario (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  tipo_usuario ENUM('admin', 'usuario'),
  fecha_registro DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla Libro
DROP TABLE IF EXISTS Libro;
CREATE TABLE Libro (
  id_libro INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(255) NOT NULL,
  autor VARCHAR(100),
  editorial VARCHAR(100),
  anio_publicacion YEAR,
  isbn VARCHAR(13) UNIQUE,
  cantidad_disponible INT DEFAULT 1,
  imagen VARCHAR(255) DEFAULT 'default.jpg' -- Nueva columna para imágenes
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla Préstamo
DROP TABLE IF EXISTS Prestamo;
CREATE TABLE Prestamo (
  id_prestamo INT AUTO_INCREMENT,
  id_usuario INT,
  id_libro INT,
  fecha_prestamo DATETIME,
  fecha_devolucion DATETIME,
  estado ENUM('pendiente', 'devuelto') DEFAULT 'pendiente',
  PRIMARY KEY (id_prestamo),
  FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE,
  FOREIGN KEY (id_libro) REFERENCES Libro(id_libro) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabla Historial de Préstamos
DROP TABLE IF EXISTS Historial_Prestamos;
CREATE TABLE Historial_Prestamos (
  id_historial INT AUTO_INCREMENT,
  id_usuario INT,
  id_libro INT,
  fecha_prestamo DATETIME,
  fecha_devolucion DATETIME,
  PRIMARY KEY (id_historial),
  FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE,
  FOREIGN KEY (id_libro) REFERENCES Libro(id_libro) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
