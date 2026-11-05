# Tbl_Amigos

Gestiona las **solicitudes de amistad** y las **amistades** entre dos usuarios. Una misma fila representa la relación completa: se crea como `pendiente` y luego pasa a `aceptada` o `rechazada`. Además del registro de los tiempos de las solicitudes y respuestas.

| Campo | Tipo de dato | Longitud | Función |
|:---|:---|:---|:---|
| IdAmigo **PK** | INT (AUTO_INCREMENT) | — | Identificador de la relación |
| IdUser1 **FK**, NOT NULL | INT | — | Primer usuario de la pareja (siempre el de **menor** Id) → `Tbl_Usuario` |
| IdUser2 **FK**, NOT NULL | INT | — | Segundo usuario de la pareja (siempre el de **mayor** Id) → `Tbl_Usuario` |
| IdSolicitante **FK**, NOT NULL | INT | — | Quién envió la solicitud (debe ser IdUser1 o IdUser2) → `Tbl_Usuario` |
| Estado NOT NULL | ENUM | — | `pendiente`, `aceptada` o `rechazada` (por defecto `pendiente`) |
| FechaSolicitud NOT NULL | DATETIME | — | Cuándo se envió la solicitud (automática) |
| FechaRespuesta (opcional) | DATETIME | — | Cuándo se aceptó o rechazó; vacía mientras esté pendiente |

**Restricción:** `UNIQUE (IdUser1, IdUser2)` → solo puede existir **una** fila por pareja.

## Peculiaridades

- **Pareja ordenada:** siempre `IdUser1 < IdUser2`. Si se inserta al revés, el trigger `trg_amigos_bi` los **intercambia solo**. Así, la pareja 5-3 y la 3-5 son la misma fila y no hay duplicados.
- **IdSolicitante** es el que permite saber *quién* pidió la amistad.
- **No se puede ser amigo de uno mismo** (lo impide el trigger).
- **No se puede solicitar amistad si hay un bloqueo** entre ambos, en cualquier dirección.
- **Reenvío tras rechazo:** como la pareja es única, volver a pedir amistad se hace con un `UPDATE` sobre la fila existente (de `rechazada` a `pendiente`), no con un nuevo `INSERT`. El trigger `trg_amigos_bu` vuelve a validar las reglas.
- Si cualquiera de los dos usuarios se elimina, la fila desaparece (cascada).
