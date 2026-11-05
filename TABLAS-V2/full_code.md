CREATE DATABASE IF NOT EXISTS ChatOnline
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ChatOnline;

CREATE TABLE IF NOT EXISTS Tbl_Usuario (
    IdUser INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Nombre VARCHAR(100) NOT NULL,
    Apellido VARCHAR(100) NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    Email VARCHAR(150) NOT NULL UNIQUE,
    FotoPerfil VARCHAR(255),
    Conectado BOOLEAN NOT NULL DEFAULT FALSE,
    UltimaConexion DATETIME NULL,
    FechaRegistro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Tbl_Amigos (
    IdAmigo INT AUTO_INCREMENT PRIMARY KEY,
    IdUser1 INT NOT NULL,
    IdUser2 INT NOT NULL,
    IdSolicitante INT NOT NULL,
    Estado ENUM('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente',
    FechaSolicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FechaRespuesta DATETIME NULL,

    CONSTRAINT fk_amigos_user1 FOREIGN KEY (IdUser1)
        REFERENCES Tbl_Usuario(IdUser) ,
    CONSTRAINT fk_amigos_user2 FOREIGN KEY (IdUser2)
        REFERENCES Tbl_Usuario(IdUser) ,
    CONSTRAINT fk_amigos_solicitante FOREIGN KEY (IdSolicitante)
        REFERENCES Tbl_Usuario(IdUser) ,
    CONSTRAINT uq_amigos_pareja UNIQUE (IdUser1, IdUser2)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Tbl_Bloqueo (
    IdBloqueo INT AUTO_INCREMENT PRIMARY KEY,
    IdBloqueador INT NOT NULL,
    IdBloqueado INT NOT NULL,
    FechaBloqueo DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_bloqueo_bloqueador FOREIGN KEY (IdBloqueador)
        REFERENCES Tbl_Usuario(IdUser) ,
    CONSTRAINT fk_bloqueo_bloqueado FOREIGN KEY (IdBloqueado)
        REFERENCES Tbl_Usuario(IdUser) ,
    CONSTRAINT uq_bloqueo UNIQUE (IdBloqueador, IdBloqueado)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Tbl_Apodo (
    IdApodo INT AUTO_INCREMENT PRIMARY KEY,
    IdAutor INT NOT NULL,
    IdUser INT NOT NULL,
    Apodo VARCHAR(50) NOT NULL,

    CONSTRAINT fk_apodo_autor FOREIGN KEY (IdAutor)
        REFERENCES Tbl_Usuario(IdUser) ,
    CONSTRAINT fk_apodo_user FOREIGN KEY (IdUser)
        REFERENCES Tbl_Usuario(IdUser) ,
    CONSTRAINT uq_apodo UNIQUE (IdAutor, IdUser)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Tbl_Ajustes (
    IdAjustes INT AUTO_INCREMENT PRIMARY KEY,
    IdUser INT NOT NULL UNIQUE,
    Tema ENUM('claro','oscuro') NOT NULL DEFAULT 'claro',
    ColorChat ENUM('azul','verde','rojo','morado') NOT NULL DEFAULT 'azul',
    Fuente ENUM('Arial','Verdana','Roboto') NOT NULL DEFAULT 'Arial',

    CONSTRAINT fk_ajustes_user FOREIGN KEY (IdUser)
        REFERENCES Tbl_Usuario(IdUser) 
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Tbl_Chat (
    IdChat INT AUTO_INCREMENT PRIMARY KEY,
    Tipo ENUM('privado','grupo') NOT NULL DEFAULT 'privado',
    Nombre VARCHAR(100) NULL,
    FotoGrupo VARCHAR(255) NULL,
    ClavePrivado VARCHAR(25) NULL UNIQUE,
    IdCreador INT NULL,
    FechaCreacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_chat_creador FOREIGN KEY (IdCreador)
        REFERENCES Tbl_Usuario(IdUser) ON DELETE SET NULL,
    CONSTRAINT chk_chat_tipo CHECK (
        (Tipo = 'privado' AND ClavePrivado IS NOT NULL AND Nombre IS NULL)
        OR (Tipo = 'grupo' AND ClavePrivado IS NULL AND Nombre IS NOT NULL)
    )
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Tbl_ChatParticipante (
    IdChat INT NOT NULL,
    IdUser INT NOT NULL,
    Rol ENUM('admin','miembro') NOT NULL DEFAULT 'miembro',
    FechaUnion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UltimoMensajeLeido INT UNSIGNED NOT NULL DEFAULT 0,
    UltimoMensajeBorrado INT UNSIGNED NOT NULL DEFAULT 0,

    PRIMARY KEY (IdChat, IdUser),
    CONSTRAINT fk_part_chat FOREIGN KEY (IdChat)
        REFERENCES Tbl_Chat(IdChat) ,
    CONSTRAINT fk_part_user FOREIGN KEY (IdUser)
        REFERENCES Tbl_Usuario(IdUser) 
) ENGINE=InnoDB;

-- 8. MENSAJES
CREATE TABLE IF NOT EXISTS Tbl_Mensaje (
    IdMensaje INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    IdChat INT NOT NULL,
    IdUser INT NOT NULL,
    Mensaje TEXT NOT NULL,
    FechaEnvio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_mensaje_chat FOREIGN KEY (IdChat)
        REFERENCES Tbl_Chat(IdChat),
    CONSTRAINT fk_mensaje_user FOREIGN KEY (IdUser)
        REFERENCES Tbl_Usuario(IdUser),

    INDEX idx_mensaje_chat_fecha (IdChat, FechaEnvio)
) ENGINE=InnoDB;





USE ChatOnline;

-- Usuarios
INSERT INTO Tbl_Usuario (Username, Nombre, Apellido, PasswordHash, Email) VALUES
('ana',   'Ana',   'García',  '$2y$10$08NOIBVHJ1kYZAxxbuviHOI/QvgPVjWY369zkR24U1IHbtVGkmfIG', 'ana@example.com'),
('luis',  'Luis',  'Martín',  '$2y$10$OXZEjSyi/ahCnwKCTQpYdeC6VzB3ph0XSJP8XG7fGTYhgLEudtow.', 'luis@example.com'),
('marta', 'Marta', 'López',   '$2y$10$Ex.dPg2y8bSglmaCPacPc.kWluQu45r1T1fdXUX02ycb.cb7a9hW2', 'marta@example.com'),
('pedro', 'Pedro', 'Sánchez', '$2y$10$lue3zqyk5/pGL05ZNXusReZzwO.mgVjmyN28RhHSDoWw.Uvuai1Qq', 'pedro@example.com');

-- Ajustes
INSERT INTO Tbl_Ajustes (IdUser) VALUES (1), (2), (3), (4);

-- Amigos
INSERT INTO Tbl_Amigos (IdUser1, IdUser2, IdSolicitante, Estado, FechaRespuesta) VALUES
(1, 2, 1, 'aceptada',  NOW()),
(1, 3, 3, 'aceptada',  NOW()),
(1, 4, 4, 'pendiente', NULL);

-- Bloqueo y apodo
INSERT INTO Tbl_Bloqueo (IdBloqueador, IdBloqueado) VALUES (2, 4);
INSERT INTO Tbl_Apodo (IdAutor, IdUser, Apodo) VALUES (1, 2, 'Luisito');

-- Chats
INSERT INTO Tbl_Chat (Tipo, Nombre, ClavePrivado, IdCreador) VALUES
('privado', NULL,      '1_2', 1),
('grupo',   'Amigos',  NULL,  1);

INSERT INTO Tbl_ChatParticipante (IdChat, IdUser, Rol) VALUES
(1, 1, 'miembro'),
(1, 2, 'miembro'),
(2, 1, 'admin'),
(2, 2, 'miembro'),
(2, 3, 'miembro');

-- Mensajes
INSERT INTO Tbl_Mensaje (IdChat, IdUser, Mensaje) VALUES
(1, 1, 'Hola Luis, ¿qué tal?'),
(1, 2, '¡Hola Ana! Todo bien, ¿y tú?'),
(2, 1, 'Bienvenidos al grupo'),
(2, 3, '¡Gracias por añadirme!');