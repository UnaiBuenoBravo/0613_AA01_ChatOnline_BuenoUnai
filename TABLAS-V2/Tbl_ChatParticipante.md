# Tbl_ChatParticipante

Indica **quién pertenece a cada chat** y guarda el **estado personal** de cada persona en él (qué ha leído, qué ha borrado, qué rol tiene).

| Campo | Tipo de dato | Longitud | Función |
|:---|:---|:---|:---|
| IdChat **PK, FK** | INT | — | Chat al que pertenece → `Tbl_Chat` |
| IdUser **PK, FK** | INT | — | Usuario participante → `Tbl_Usuario` |
| Rol NOT NULL | ENUM | — | `admin` o `miembro` (por defecto `miembro`) |
| FechaUnion NOT NULL | DATETIME | — | Cuándo entró al chat (automática) |
| UltimoMensajeLeido NOT NULL | (BIG)INT UNSIGNED | — | Id del último mensaje que ha leído (0 = ninguno) |
| UltimoMensajeBorrado NOT NULL | (BIG)INT UNSIGNED | — | Id del último mensaje que "vació" para sí mismo (0 = ninguno) |

## Peculiaridades

- **Clave primaria compuesta (IdChat + IdUser):** un usuario solo puede aparecer una vez en cada chat.
- **Esta tabla es imprescindible para enviar mensajes:** el trigger `trg_mensaje_bi` rechaza el mensaje si el usuario no tiene fila aquí para ese chat.
- **Mensajes no leídos:** son los mensajes del chat con `IdMensaje > UltimoMensajeLeido`.
- **Vaciar chat solo para mí:** el usuario no borra los mensajes reales (los demás los siguen viendo); se guarda hasta qué mensaje borró, y la aplicación solo muestra los de `IdMensaje > UltimoMensajeBorrado`.
- **Rol** tiene sentido sobre todo en grupos. En un chat privado ambos suelen ser `miembro`.
- Si se elimina el chat o el usuario, la fila desaparece (cascada).
