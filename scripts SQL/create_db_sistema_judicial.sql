-- ============================================
-- Base de Datos: Sistema Judicial
-- ============================================

DROP DATABASE IF EXISTS sistema_judicial;
CREATE DATABASE sistema_judicial
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE sistema_judicial;

-- ============================================
-- TABLA: ABOGADO
-- ============================================

CREATE TABLE Abogado (
    a_codigo INT AUTO_INCREMENT PRIMARY KEY,
    a_nombre VARCHAR(50) NOT NULL,
    a_apellido VARCHAR(50) NOT NULL,
    a_matricula INT NOT NULL UNIQUE,
    a_calle VARCHAR(100) NOT NULL,
    a_numero INT NOT NULL,
    a_piso INT NULL,
    a_depto VARCHAR(10) NULL
) ENGINE=InnoDB;


-- ============================================
-- TABLA: CLIENTE
-- ============================================

CREATE TABLE Cliente (
    c_codigo INT AUTO_INCREMENT PRIMARY KEY,
    c_nombre VARCHAR(50) NOT NULL,
    c_apellido VARCHAR(50) NOT NULL,
    c_calle VARCHAR(100) NOT NULL,
    c_numero INT NOT NULL,
    c_piso INT NULL,
    c_depto VARCHAR(10) NULL
) ENGINE=InnoDB;


-- ============================================
-- TABLA: JUZGADO
-- ============================================

CREATE TABLE Juzgado (
    j_codigo INT AUTO_INCREMENT PRIMARY KEY,
    j_nombre VARCHAR(100) NOT NULL,
    j_apellido_juez VARCHAR(50) NOT NULL,
    j_nombre_juez VARCHAR(50) NOT NULL,
    j_calle VARCHAR(100) NOT NULL,
    j_numero INT NOT NULL,
    j_piso INT NULL,
    j_depto VARCHAR(10) NULL,
    fuero VARCHAR(50) NOT NULL
) ENGINE=InnoDB;


-- ============================================
-- TABLA: EXPEDIENTE
-- ============================================

CREATE TABLE Expediente (
    e_codigo INT AUTO_INCREMENT PRIMARY KEY,
    c_codigo INT NOT NULL,
    j_codigo INT NOT NULL,
    e_caratula VARCHAR(255) NOT NULL,
    ultima_modificacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_expediente_cliente
        FOREIGN KEY (c_codigo)
        REFERENCES Cliente(c_codigo),

    CONSTRAINT fk_expediente_juzgado
        FOREIGN KEY (j_codigo)
        REFERENCES Juzgado(j_codigo)
) ENGINE=InnoDB;


-- ============================================
-- TABLA: AUDIENCIA
-- ============================================

CREATE TABLE Audiencia (
    au_codigo INT AUTO_INCREMENT PRIMARY KEY,
    au_fecha_hora DATETIME NOT NULL,
    au_estado VARCHAR(20) NOT NULL,
    au_tipo VARCHAR(20) NOT NULL,
    e_codigo INT NOT NULL,

    CONSTRAINT chk_estado
        CHECK (au_estado IN ('Pendiente','Realizada','Suspendida')),

    CONSTRAINT chk_tipo
        CHECK (au_tipo IN ('Mediacion','Testimonial','Sentencia')),

    CONSTRAINT fk_audiencia_expediente
        FOREIGN KEY (e_codigo)
        REFERENCES Expediente(e_codigo)
) ENGINE=InnoDB;


-- ============================================
-- TABLA: ABOGADO_EXPEDIENTE
-- ============================================

CREATE TABLE Abogado_Expediente (
    a_codigo INT NOT NULL,
    e_codigo INT NOT NULL,
    fecha_asignacion DATETIME NOT NULL,

    PRIMARY KEY (a_codigo, e_codigo),

    CONSTRAINT fk_abexp_abogado
        FOREIGN KEY (a_codigo)
        REFERENCES Abogado(a_codigo),

    CONSTRAINT fk_abexp_expediente
        FOREIGN KEY (e_codigo)
        REFERENCES Expediente(e_codigo)
) ENGINE=InnoDB;