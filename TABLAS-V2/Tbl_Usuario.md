# Tbl_Usuario

Guarda a **todas las personas registradas** en la aplicación. Es la tabla central: casi todas las demás se conectan con ella.

| Campo | Tipo de dato | Longitud | Función |
|:---|:---|:---|:---|
| IdUser **PK** | INT (AUTO_INCREMENT) | — | Identificador único del usuario |
| Username **UNIQUE**, NOT NULL | VARCHAR | 50 | Nombre de usuario para iniciar sesión y buscar a otros; no se puede repetir |
| Nombre NOT NULL | VARCHAR | 100 | Nombre real |
| Apellido NOT NULL | VARCHAR | 100 | Apellidos |
| PasswordHash NOT NULL | VARCHAR | 255 | Contraseña **cifrada** (hash), nunca en texto plano |
| Email **UNIQUE**, NOT NULL | VARCHAR | 150 | Correo electrónico; no se puede repetir |
| FotoPerfil (opcional) | VARCHAR | 255 | Ruta o nombre del archivo de la foto de perfil |
| Conectado NOT NULL | BOOLEAN | — | Indica si el usuario está en línea (por defecto `FALSE`) |
| UltimaConexion (opcional) | DATETIME | — | Fecha y hora de la última vez que estuvo conectado |
| FechaRegistro NOT NULL | DATETIME | — | Fecha de alta; se rellena sola con la fecha actual |

## Peculiaridades

- **Username y Email son únicos:** la base de datos rechaza un segundo registro con los mismos valores.
- **PasswordHash** debe guardar el resultado de `password_hash()` (PHP). 255 caracteres dejan margen para algoritmos futuros.
- **FotoPerfil** solo guarda la *ruta* del archivo, no la imagen en sí.
- **Ajustes automáticos:** al insertar un usuario, un trigger (`trg_usuario_ai`) le crea su fila de ajustes por defecto en `Tbl_Ajustes`.
- **Borrado en cascada:** si se elimina un usuario, desaparecen también sus amistades, bloqueos, apodos, ajustes, participaciones en chats y mensajes.
