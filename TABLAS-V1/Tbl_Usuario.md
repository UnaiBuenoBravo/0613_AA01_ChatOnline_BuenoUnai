# Tbl_Usuario

| Campo | Tipo de dato | Longitud | Función |
|:---------|:-------------|:---------|:--------|
| IdUser PK | INT | — | Identificador único |
| Username | VARCHAR | 30 caracteres | Nombre de usuario único |
| Nombre | VARCHAR | 50 caracteres | Nombre real |
| Apellido | VARCHAR | 100 caracteres | Apellido |
| PasswordHash | VARCHAR | 255 caracteres | Contraseña almacenada de forma segura |
| Email | VARCHAR | 254 caracteres | Correo electrónico único |
| FotoPerfil | VARCHAR | 255 caracteres | Ruta de la foto de perfil |
| Conectado | BOOLEAN | — | Estado de conexión |

A destacar sobre la Tbl_Usuario:
- IdUser será la clave primaria.
- Username no se podrá repetir.
- Email no se podrá repetir.
- Conectado podrá ser un booleano (TRUE o FALSE).