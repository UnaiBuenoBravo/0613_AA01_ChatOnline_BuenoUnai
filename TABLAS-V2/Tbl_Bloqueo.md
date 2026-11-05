# Tbl_Bloqueo

Registra **quién ha bloqueado a quién**. El bloqueo es dirigido: que A bloquee a B no implica que B bloquee a A.

| Campo | Tipo de dato | Longitud | Función |
|:---|:---|:---|:---|
| IdBloqueo **PK** | INT (AUTO_INCREMENT) | — | Identificador del bloqueo |
| IdBloqueador **FK**, NOT NULL | INT | — | Usuario que bloquea → `Tbl_Usuario` |
| IdBloqueado **FK**, NOT NULL | INT | — | Usuario bloqueado → `Tbl_Usuario` |
| FechaBloqueo NOT NULL | DATETIME | — | Cuándo se hizo el bloqueo (automática) |

**Restricción:** `UNIQUE (IdBloqueador, IdBloqueado)` → no se puede bloquear dos veces a la misma persona.

## Peculiaridades

- **Nadie puede bloquearse a sí mismo** (trigger `trg_bloqueo_bi`).
- **Efectos del bloqueo**, aplicados en *cualquier* dirección (A→B o B→A):
  - No se pueden enviar solicitudes de amistad (`Tbl_Amigos`).
  - No se pueden enviar mensajes en el chat privado (`Tbl_Mensaje`).
- Para **desbloquear** basta con borrar la fila.
- Si se elimina cualquiera de los dos usuarios, el bloqueo se borra (cascada).
