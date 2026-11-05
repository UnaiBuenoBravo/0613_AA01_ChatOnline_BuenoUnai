# Tbl_Ajustes

Guarda las **preferencias de personalización** de cada usuario. Cada usuario tiene una fila.

| Campo | Tipo de dato | Longitud | Función |
|:---|:---|:---|:---|
| IdAjustes **PK** | INT (AUTO_INCREMENT) | — | Identificador de los ajustes |
| IdUser **FK**, **UNIQUE**, NOT NULL | INT | — | Propietario de la configuración → `Tbl_Usuario` |
| Tema NOT NULL | ENUM | — | Tema visual: `claro` u `oscuro` (por defecto `claro`) |
| ColorChat NOT NULL | ENUM | — | Color del chat: `azul`, `verde`, `rojo` o `morado` (por defecto `azul`) |
| Fuente NOT NULL | ENUM | — | Tipografía: `Arial`, `Verdana` o `Roboto` (por defecto `Arial`) |

## A destacar

- **Opciones cerradas:** al ser `ENUM`, la base de datos **solo acepta** los valores de la lista. Para añadir un color o una fuente nueva hay que modificar la tabla (`ALTER TABLE`). Una posible mejora seria hacer una tabla a parte y tener un formulario para añadir, modificar, borrar... opciones. 
- **Se crea sola:** el trigger `trg_usuario_ai` inserta la fila con los valores por defecto cuando se registra un usuario. Desde PHP solo hay que hacer `UPDATE`, nunca `INSERT`.
- **IdUser es UNIQUE:** garantiza una única configuración por usuario (relación 1 a 1).
- Si se elimina el usuario, sus ajustes se borran (cascada).
