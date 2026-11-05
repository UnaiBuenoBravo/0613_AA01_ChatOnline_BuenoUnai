# Tbl_Chat

Representa cada **conversación**: puede ser un chat **privado** (entre dos personas) o un **grupo**. No guarda quién participa (eso está en `Tbl_ChatParticipante`) ni los mensajes (`Tbl_Mensaje`).

| Campo | Tipo de dato | Longitud | Función |
|:---|:---|:---|:---|
| IdChat **PK** | INT (AUTO_INCREMENT) | — | Identificador del chat |
| Tipo NOT NULL | ENUM | — | `privado` o `grupo` (por defecto `privado`) |
| Nombre (opcional) | VARCHAR | 100 | Nombre del grupo (**solo grupos**) |
| FotoGrupo (opcional) | VARCHAR | 255 | Ruta de la imagen del grupo (**solo grupos**) |
| ClavePrivado **UNIQUE** (opcional) | VARCHAR | 25 | Clave del chat privado con formato `idMenor-idMayor` (**solo privados**) |
| IdCreador **FK** (opcional) | INT | — | Usuario que creó el chat → `Tbl_Usuario` |
| FechaCreacion NOT NULL | DATETIME | — | Fecha de creación (automática) |

## Peculiaridades

- **Regla de coherencia (CHECK):**
  - Un chat **privado** *debe* tener `ClavePrivado` y *no* puede tener `Nombre`.
  - Un chat **grupo** *debe* tener `Nombre` y *no* puede tener `ClavePrivado`.
- **ClavePrivado evita duplicados:** por ejemplo, el chat entre los usuarios 3 y 8 siempre es `3-8`. Como es `UNIQUE`, nunca habrá dos chats privados entre las mismas personas. **La genera PHP** (la base de datos no la calcula).
- **IdCreador usa `ON DELETE SET NULL`:** si el creador borra su cuenta, el chat **no se elimina**; solo queda sin creador.
- Los chats ya **no dependen de la amistad** para existir; la amistad solo se comprueba al enviar mensajes en privados.
- La restricción `CHECK` requiere **MySQL 8.0.16 o superior** (en versiones anteriores se ignora sin avisar).
