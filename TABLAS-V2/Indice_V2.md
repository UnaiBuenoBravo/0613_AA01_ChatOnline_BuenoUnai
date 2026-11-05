# Base de datos ChatOnline: Índice

Motor: MySQL (InnoDB) · Codificación: utf8mb4

| Archivo | Contenido |
|:---|:---|
| [Tbl_Usuario.md](Tbl_Usuario.md) | Usuarios registrados |
| [Tbl_Amigos.md](Tbl_Amigos.md) | Solicitudes y amistades |
| [Tbl_Bloqueo.md](Tbl_Bloqueo.md) | Bloqueos entre usuarios |
| [Tbl_Apodo.md](Tbl_Apodo.md) | Apodos privados |
| [Tbl_Ajustes.md](Tbl_Ajustes.md) | Preferencias de cada usuario |
| [Tbl_Chat.md](Tbl_Chat.md) | Chats privados y grupos |
| [Tbl_ChatParticipante.md](Tbl_ChatParticipante.md) | Miembros de cada chat y su estado |
| [Tbl_Mensaje.md](Tbl_Mensaje.md) | Mensajes |
| [Triggers.md](Triggers.md) | Manual de las reglas automáticas |

## Relaciones principales

- `Tbl_Usuario` 1 — 1 `Tbl_Ajustes`
- `Tbl_Usuario` 1 — N `Tbl_Amigos`, `Tbl_Bloqueo`, `Tbl_Apodo`, `Tbl_Mensaje`
- `Tbl_Chat` N — N `Tbl_Usuario`, a través de `Tbl_ChatParticipante`
- `Tbl_Chat` 1 — N `Tbl_Mensaje`

